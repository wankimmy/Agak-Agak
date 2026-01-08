<?php

namespace App\Http\Controllers;

use App\Services\ForecastService;
use App\Models\Product;
use App\Models\Store;
use App\Models\Forecast;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    protected $forecastService;

    public function __construct(ForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    public function index()
    {
        $forecasts = Forecast::with(['product', 'store'])
            ->orderBy('forecast_date', 'desc')
            ->get();

        $products = Product::where('is_active', true)->get();
        $stores = Store::where('is_active', true)->get();

        return view('forecasts.index', compact('forecasts', 'products', 'stores'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        $stores = Store::where('is_active', true)->get();
        return view('forecasts.create', compact('products', 'stores'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'store_id' => 'nullable|exists:stores,id',
            'forecast_horizon' => 'integer|min:1|max:365',
        ]);

        $result = $this->forecastService->generateForecast(
            $validated['product_id'] ?? null,
            $validated['store_id'] ?? null,
            $validated['forecast_horizon'] ?? 30
        );

        if ($result['success']) {
            return redirect()->route('forecasts.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message'])
                ->withInput();
        }
    }

    public function show($id)
    {
        $forecast = Forecast::with(['product', 'store'])->findOrFail($id);
        return view('forecasts.show', compact('forecast'));
    }

    public function destroy(Forecast $forecast)
    {
        $forecast->delete();

        return redirect()->route('forecasts.index')
            ->with('success', 'Forecast deleted successfully.');
    }
}

