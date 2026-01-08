<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class LandingController extends Controller
{
    public function index()
    {
        return view('landing.index');
    }

    public function demo(Request $request)
    {
        $request->validate([
            'demo_file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('demo_file');
            $extension = $file->getClientOriginalExtension();
            
            // Parse the file based on extension
            $salesData = $this->parseFile($file, $extension);
            
            if (empty($salesData) || count($salesData) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient data. Please provide at least 2 data points for forecasting.'
                ], 400);
            }

            // Prepare data for API
            $requestData = [
                'sales_data' => $salesData,
                'forecast_horizon' => 30,
            ];

            // Call Python API
            $apiUrl = Config::get('forecast.api_url');
            $response = Http::timeout(60)->post("{$apiUrl}/forecast", $requestData);

            if (!$response->successful()) {
                Log::error('Demo Forecast API Error: ' . $response->body());
                return response()->json([
                    'success' => false,
                    'message' => 'Forecasting service is temporarily unavailable. Please try again later.'
                ], 500);
            }

            $forecastData = $response->json();

            if (!isset($forecastData['forecast']) || !$forecastData['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate forecast. Please check your data format.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Forecast generated successfully!',
                'forecast' => $forecastData['forecast'],
                'historical_data' => $salesData
            ]);

        } catch (\Exception $e) {
            Log::error('Demo Forecast Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseFile($file, $extension)
    {
        $salesData = [];
        $filePath = $file->getRealPath();

        try {
            if ($extension === 'csv') {
                $handle = fopen($filePath, 'r');
                if (!$handle) {
                    throw new \Exception('Could not open CSV file');
                }
                
                // Read header to determine format
                $header = fgetcsv($handle);
                if (!$header) {
                    fclose($handle);
                    throw new \Exception('Empty CSV file');
                }
                
                while (($row = fgetcsv($handle)) !== false) {
                    if (count($row) < 2) {
                        continue;
                    }
                    
                    // Clean the row data
                    $row = array_map('trim', $row);
                    
                    // Try different formats
                    $date = null;
                    $value = null;
                    
                    // Format 1: Date, Value (simple)
                    if (count($row) >= 2) {
                        $date = $row[0];
                        $value = is_numeric($row[1]) ? (float) $row[1] : null;
                    }
                    
                    // Format 2: Date, SKU, Store, Price, Quantity (calculate total)
                    if (count($row) >= 5 && is_numeric($row[3]) && is_numeric($row[4])) {
                        $date = $row[0];
                        $price = (float) $row[3];
                        $quantity = (int) $row[4];
                        $value = $price * $quantity;
                    }
                    
                    // Validate date format (YYYY-MM-DD)
                    if ($date && $value !== null && $value > 0) {
                        // Try to parse and validate date
                        $parsedDate = date('Y-m-d', strtotime($date));
                        if ($parsedDate && $parsedDate !== '1970-01-01') {
                            $salesData[] = [
                                'date' => $parsedDate,
                                'value' => $value
                            ];
                        }
                    }
                }
                fclose($handle);
            } else {
                // For Excel files, try to read as CSV first (works for simple Excel files)
                // Note: For complex Excel files, install PhpSpreadsheet: composer require phpoffice/phpspreadsheet
                $handle = @fopen($filePath, 'r');
                if ($handle) {
                    $header = fgetcsv($handle);
                    
                    while (($row = fgetcsv($handle)) !== false) {
                        if (count($row) < 2) {
                            continue;
                        }
                        
                        $row = array_map('trim', $row);
                        $date = $row[0] ?? null;
                        $value = null;
                        
                        if (count($row) >= 2 && is_numeric($row[1])) {
                            $value = (float) $row[1];
                        } elseif (count($row) >= 5 && is_numeric($row[3]) && is_numeric($row[4])) {
                            $value = (float) $row[3] * (int) $row[4];
                        }
                        
                        if ($date && $value !== null && $value > 0) {
                            $parsedDate = date('Y-m-d', strtotime($date));
                            if ($parsedDate && $parsedDate !== '1970-01-01') {
                                $salesData[] = [
                                    'date' => $parsedDate,
                                    'value' => $value
                                ];
                            }
                        }
                    }
                    fclose($handle);
                } else {
                    throw new \Exception('Could not read Excel file. Please convert to CSV format or ensure the file is not corrupted.');
                }
            }
        } catch (\Exception $e) {
            Log::error('File parsing error: ' . $e->getMessage());
            throw new \Exception('Error parsing file: ' . $e->getMessage());
        }

        // Sort by date
        usort($salesData, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $salesData;
    }
}

