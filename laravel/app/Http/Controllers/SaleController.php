<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Store;
use App\Services\ExcelUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['product', 'store'])->latest('sale_date')->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $stores = Store::where('is_active', true)->get();
        return view('sales.create', compact('products', 'stores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['total_amount'] = $validated['price'] * $validated['quantity'];

        Sale::create($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Sale recorded successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['product', 'store']);
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $products = Product::where('is_active', true)->get();
        $stores = Store::where('is_active', true)->get();
        return view('sales.edit', compact('sale', 'products', 'stores'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['total_amount'] = $validated['price'] * $validated['quantity'];

        $sale->update($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    public function upload()
    {
        return view('sales.upload');
    }

    public function downloadTemplate()
    {
        $excelService = new ExcelUploadService();
        $spreadsheet = $excelService->generateTemplate();

        $writer = new Xlsx($spreadsheet);
        
        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="sales_upload_template.xlsx"',
            ]
        );
    }

    public function processUpload(Request $request, ExcelUploadService $excelService)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('excel_file');
        $result = $excelService->validateAndUpload($file);

        if ($result['success']) {
            $message = "Successfully imported {$result['success_count']} sales records.";
            
            if (!empty($result['warnings'])) {
                $message .= " " . count($result['warnings']) . " warnings.";
            }
            
            if (!empty($result['errors'])) {
                $message .= " " . count($result['errors']) . " errors occurred.";
            }

            return redirect()->route('sales.index')
                ->with('success', $message)
                ->with('warnings', $result['warnings'] ?? [])
                ->with('errors', $result['errors'] ?? []);
        } else {
            return redirect()->route('sales.upload')
                ->with('error', $result['message'] ?? 'Error importing Excel file.')
                ->with('errors', $result['errors'] ?? [])
                ->with('warnings', $result['warnings'] ?? []);
        }
    }
}

