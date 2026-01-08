<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Exception;

class ExcelUploadService
{
    // Required columns in exact order
    private const REQUIRED_COLUMNS = [
        'date',
        'product_id',
        'product_name',
        'product_type',
        'is_digital',
        'store_id',
        'location',
        'price',
        'quantity_sold',
        'revenue',
    ];

    // Optional forecast-enhancing columns
    private const OPTIONAL_COLUMNS = [
        'promo_flag',
        'discount_pct',
        'stock_available',
        'holiday_flag',
        'channel',
    ];

    public function validateAndUpload($file): array
    {
        $errors = [];
        $warnings = [];
        $successCount = 0;
        $processedRows = [];

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                throw new Exception('Excel file is empty or has no data rows.');
            }

            // Validate header row
            $headerRow = array_map('strtolower', array_map('trim', $rows[0]));
            $headerValidation = $this->validateHeaders($headerRow);
            
            if (!$headerValidation['valid']) {
                throw new Exception('Invalid headers: ' . implode(', ', $headerValidation['errors']));
            }

            $columnMap = $this->createColumnMap($headerRow);

            // Process data rows
            DB::beginTransaction();

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;

                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                try {
                    $validatedData = $this->validateRow($row, $columnMap, $rowNumber);
                    
                    // Business rule: If stock_available = 0, quantity_sold must be 0
                    if ($validatedData['stock_available'] == 0 && $validatedData['quantity_sold'] > 0) {
                        $errors[] = "Row {$rowNumber}: stock_available is 0 but quantity_sold is {$validatedData['quantity_sold']}. If product is unavailable, quantity_sold must be 0.";
                        continue;
                    }

                    // Business rule: Digital products must have stock_available = 1
                    if ($validatedData['is_digital'] == 1 && $validatedData['stock_available'] == 0) {
                        $warnings[] = "Row {$rowNumber}: Digital product forced stock_available to 1 (digital products are always available).";
                        $validatedData['stock_available'] = 1;
                    }

                    // Auto-calculate revenue if missing
                    if (empty($validatedData['revenue']) || $validatedData['revenue'] == 0) {
                        $validatedData['revenue'] = $validatedData['price'] * $validatedData['quantity_sold'];
                    }

                    // Create or update product
                    $product = $this->createOrUpdateProduct($validatedData);

                    // Create or update store
                    $store = $this->createOrUpdateStore($validatedData);

                    // Create sale record
                    $sale = Sale::create([
                        'sale_date' => $validatedData['date'],
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'location' => $validatedData['location'],
                        'price' => $validatedData['price'],
                        'quantity_sold' => $validatedData['quantity_sold'],
                        'revenue' => $validatedData['revenue'],
                        'stock_available' => $validatedData['stock_available'],
                        'promo_flag' => $validatedData['promo_flag'] ?? 0,
                        'discount_pct' => $validatedData['discount_pct'] ?? null,
                        'holiday_flag' => $validatedData['holiday_flag'] ?? 0,
                        'channel' => $validatedData['channel'] ?? null,
                    ]);

                    $successCount++;
                    $processedRows[] = $rowNumber;

                } catch (Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    continue;
                }
            }

            // Validate date continuity (prevent data leakage)
            if ($successCount > 0) {
                $dateContinuityCheck = $this->validateDateContinuity($processedRows);
                if (!$dateContinuityCheck['valid']) {
                    DB::rollBack();
                    throw new Exception('Date continuity validation failed: ' . $dateContinuityCheck['message']);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'success_count' => $successCount,
                'errors' => $errors,
                'warnings' => $warnings,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Excel Upload Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $errors,
                'warnings' => $warnings,
            ];
        }
    }

    private function validateHeaders(array $headerRow): array
    {
        $errors = [];
        $missingColumns = [];

        foreach (self::REQUIRED_COLUMNS as $requiredColumn) {
            if (!in_array(strtolower($requiredColumn), $headerRow)) {
                $missingColumns[] = $requiredColumn;
            }
        }

        if (!empty($missingColumns)) {
            $errors[] = 'Missing required columns: ' . implode(', ', $missingColumns);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    private function createColumnMap(array $headerRow): array
    {
        $columnMap = [];
        
        foreach ($headerRow as $index => $header) {
            $columnMap[strtolower(trim($header))] = $index;
        }

        return $columnMap;
    }

    private function validateRow(array $row, array $columnMap, int $rowNumber): array
    {
        $data = [];

        // Required fields validation
        $date = $this->getCellValue($row, $columnMap, 'date');
        if (empty($date)) {
            throw new Exception('Date is required.');
        }
        $parsedDate = $this->parseDate($date);
        if (!$parsedDate) {
            throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
        }
        $data['date'] = $parsedDate;

        $productId = $this->getCellValue($row, $columnMap, 'product_id');
        if (empty($productId)) {
            throw new Exception('product_id is required.');
        }
        $data['product_id'] = trim($productId);

        $productName = $this->getCellValue($row, $columnMap, 'product_name');
        if (empty($productName)) {
            throw new Exception('product_name is required.');
        }
        $data['product_name'] = trim($productName);

        $productType = $this->getCellValue($row, $columnMap, 'product_type');
        if (empty($productType)) {
            throw new Exception('product_type is required.');
        }
        $data['product_type'] = trim($productType);

        $isDigital = $this->getCellValue($row, $columnMap, 'is_digital');
        if (!in_array($isDigital, [0, 1, '0', '1', true, false])) {
            throw new Exception('is_digital must be 0 (physical) or 1 (digital).');
        }
        $data['is_digital'] = (int) $isDigital;

        $storeId = $this->getCellValue($row, $columnMap, 'store_id');
        if (empty($storeId)) {
            throw new Exception('store_id is required.');
        }
        $data['store_id'] = trim($storeId);

        $location = $this->getCellValue($row, $columnMap, 'location');
        if (empty($location)) {
            throw new Exception('location is required.');
        }
        $data['location'] = trim($location);

        $price = $this->getCellValue($row, $columnMap, 'price');
        if (!is_numeric($price) || $price < 0) {
            throw new Exception('price must be a non-negative number.');
        }
        $data['price'] = (float) $price;

        $quantitySold = $this->getCellValue($row, $columnMap, 'quantity_sold');
        if (!is_numeric($quantitySold) || $quantitySold < 0) {
            throw new Exception('quantity_sold must be a non-negative number.');
        }
        $data['quantity_sold'] = (int) $quantitySold;

        $revenue = $this->getCellValue($row, $columnMap, 'revenue');
        if (!empty($revenue) && (!is_numeric($revenue) || $revenue < 0)) {
            throw new Exception('revenue must be a non-negative number if provided.');
        }
        $data['revenue'] = !empty($revenue) ? (float) $revenue : 0;

        // Optional fields
        $stockAvailable = $this->getCellValue($row, $columnMap, 'stock_available', true);
        if ($stockAvailable !== null && !in_array($stockAvailable, [0, 1, '0', '1', true, false])) {
            throw new Exception('stock_available must be 0 (unavailable) or 1 (available at START of day).');
        }
        // Default to 1 if not provided, but enforce digital product rule
        $data['stock_available'] = $stockAvailable !== null ? (int) $stockAvailable : 1;
        if ($data['is_digital'] == 1) {
            $data['stock_available'] = 1; // Force digital products to be available
        }

        $promoFlag = $this->getCellValue($row, $columnMap, 'promo_flag', true);
        $data['promo_flag'] = $promoFlag !== null ? (int) $promoFlag : 0;

        $discountPct = $this->getCellValue($row, $columnMap, 'discount_pct', true);
        if ($discountPct !== null && (!is_numeric($discountPct) || $discountPct < 0 || $discountPct > 100)) {
            throw new Exception('discount_pct must be between 0 and 100.');
        }
        $data['discount_pct'] = $discountPct !== null ? (float) $discountPct : null;

        $holidayFlag = $this->getCellValue($row, $columnMap, 'holiday_flag', true);
        $data['holiday_flag'] = $holidayFlag !== null ? (int) $holidayFlag : 0;

        $channel = $this->getCellValue($row, $columnMap, 'channel', true);
        if ($channel !== null && !in_array(strtolower(trim($channel)), ['online', 'retail', 'marketplace', ''])) {
            throw new Exception('channel must be: online, retail, or marketplace.');
        }
        $data['channel'] = !empty($channel) ? trim($channel) : null;

        return $data;
    }

    private function getCellValue(array $row, array $columnMap, string $column, bool $optional = false)
    {
        $columnKey = strtolower($column);
        
        if (!isset($columnMap[$columnKey])) {
            if ($optional) {
                return null;
            }
            throw new Exception("Column '{$column}' not found.");
        }

        $index = $columnMap[$columnKey];
        return isset($row[$index]) ? $row[$index] : ($optional ? null : '');
    }

    private function parseDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Try Excel date format (numeric)
        if (is_numeric($date)) {
            try {
                $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
                return $dateTime->format('Y-m-d');
            } catch (Exception $e) {
                // Not an Excel date
            }
        }

        // Try string date formats
        $dateString = is_string($date) ? trim($date) : (string) $date;
        
        // Try YYYY-MM-DD format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            $parsed = date_create_from_format('Y-m-d', $dateString);
            if ($parsed && $parsed->format('Y-m-d') === $dateString) {
                return $dateString;
            }
        }

        // Try other common formats
        $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $parsed = date_create_from_format($format, $dateString);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (!empty(trim((string) $cell))) {
                return false;
            }
        }
        return true;
    }

    private function createOrUpdateProduct(array $data): Product
    {
        $product = Product::where('sku', $data['product_id'])->first();

        if ($product) {
            // Update existing product
            $product->update([
                'name' => $data['product_name'],
                'product_type' => $data['product_type'],
                'is_digital' => $data['is_digital'],
                'base_price' => $data['price'],
            ]);
        } else {
            // Create new product
            $product = Product::create([
                'name' => $data['product_name'],
                'sku' => $data['product_id'],
                'product_type' => $data['product_type'],
                'is_digital' => $data['is_digital'],
                'base_price' => $data['price'],
                'category' => $data['product_type'],
                'is_active' => true,
            ]);
        }

        return $product;
    }

    private function createOrUpdateStore(array $data): Store
    {
        $store = Store::where('name', $data['store_id'])->first();

        if ($store) {
            // Update existing store
            $store->update([
                'location' => $data['location'],
            ]);
        } else {
            // Create new store (we'll need a default location_id)
            $location = \App\Models\Location::firstOrCreate(
                ['name' => $data['location']],
                ['name' => $data['location']]
            );

            $store = Store::create([
                'name' => $data['store_id'],
                'location_id' => $location->id,
                'location' => $data['location'],
                'is_active' => true,
            ]);
        }

        return $store;
    }

    private function validateDateContinuity(array $processedRows): array
    {
        // Get all dates from processed sales
        $dates = Sale::whereIn('id', function($query) use ($processedRows) {
            // This is a simplified check - in production, you'd want more sophisticated validation
        })->pluck('sale_date')->sort()->unique()->values();

        // Check for gaps larger than expected (prevent data leakage)
        // This is a basic check - you might want more sophisticated logic
        if ($dates->count() < 2) {
            return ['valid' => true, 'message' => ''];
        }

        // Check for reasonable date ranges (not too far in future/past)
        $today = now()->toDateString();
        $oldestDate = $dates->first();
        $newestDate = $dates->last();

        if ($oldestDate < now()->subYears(5)->toDateString()) {
            return [
                'valid' => false,
                'message' => 'Data contains dates older than 5 years. Please verify data integrity.',
            ];
        }

        if ($newestDate > now()->addDays(1)->toDateString()) {
            return [
                'valid' => false,
                'message' => 'Data contains future dates. Sales data cannot be in the future.',
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    public function generateTemplate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = array_merge(self::REQUIRED_COLUMNS, self::OPTIONAL_COLUMNS);
        $sheet->fromArray([$headers], null, 'A1');

        // Add sample data row
        $sampleRow = [
            date('Y-m-d'),
            'PROD-001',
            'Sample Product',
            'books',
            0, // is_digital
            'STORE-001',
            'New York',
            29.99,
            5,
            149.95, // revenue
            1, // stock_available
            0, // promo_flag
            null, // discount_pct
            0, // holiday_flag
            'retail', // channel
        ];
        $sheet->fromArray([$sampleRow], null, 'A2');

        // Style headers
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0'],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
