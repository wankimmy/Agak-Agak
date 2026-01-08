<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Forecast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = Config::get('forecast.api_url');
    }

    public function generateForecast($productId = null, $storeId = null, $forecastHorizon = 30)
    {
        try {
            // Get historical sales data with all fields for exogenous variables
            $query = Sale::with(['product'])
                ->select(
                    'sale_date',
                    'product_id',
                    'price',
                    'revenue',
                    'promo_flag',
                    'stock_available',
                    'holiday_flag',
                    'channel'
                )
                ->orderBy('sale_date');

            if ($productId) {
                $query->where('product_id', $productId);
            }

            if ($storeId) {
                $query->where('store_id', $storeId);
            }

            $salesData = $query->get();

            if ($salesData->count() < 2) {
                throw new \Exception('Insufficient data for forecasting. Need at least 2 data points.');
            }

            // Group by date and aggregate, preserving exogenous variables
            $groupedData = $salesData->groupBy(function ($sale) {
                return $sale->sale_date->format('Y-m-d');
            })->map(function ($daySales) {
                return [
                    'date' => $daySales->first()->sale_date->format('Y-m-d'),
                    'value' => $daySales->sum('revenue'),
                    'price' => $daySales->avg('price'),
                    'promo_flag' => $daySales->max('promo_flag') ? 1 : 0,
                    'stock_available' => $daySales->max('stock_available') ? 1 : 0,
                    'holiday_flag' => $daySales->max('holiday_flag') ? 1 : 0,
                    'product_type' => $daySales->first()->product->product_type ?? null,
                    'channel' => $daySales->first()->channel ?? null,
                ];
            })->values();

            // Prepare data for API with exogenous variables
            $requestData = [
                'sales_data' => $groupedData->toArray(),
                'forecast_horizon' => $forecastHorizon,
                'product_id' => $productId,
                'store_id' => $storeId,
            ];

            // Call Python API
            $response = Http::timeout(60)->post("{$this->apiUrl}/forecast", $requestData);

            if (!$response->successful()) {
                throw new \Exception('Forecast API error: ' . $response->body());
            }

            $forecastData = $response->json();

            if (!isset($forecastData['forecast']) || !$forecastData['success']) {
                throw new \Exception('Invalid forecast response');
            }

            // Store forecasts in database
            DB::beginTransaction();
            try {
                // Delete existing forecasts for this product/store combination and horizon
                $deleteQuery = Forecast::query();
                if ($productId) {
                    $deleteQuery->where('product_id', $productId);
                } else {
                    $deleteQuery->whereNull('product_id');
                }
                if ($storeId) {
                    $deleteQuery->where('store_id', $storeId);
                } else {
                    $deleteQuery->whereNull('store_id');
                }
                $deleteQuery->delete();

                foreach ($forecastData['forecast'] as $forecast) {
                    Forecast::create([
                        'product_id' => $productId,
                        'store_id' => $storeId,
                        'forecast_date' => $forecast['date'],
                        'forecast_value' => $forecast['forecast'],
                        'lower_bound' => $forecast['lower_bound'] ?? null,
                        'upper_bound' => $forecast['upper_bound'] ?? null,
                        'forecast_type' => 'daily',
                    ]);
                }

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Forecast generated successfully',
                    'forecast_count' => count($forecastData['forecast']),
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Forecast Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getForecasts($productId = null, $storeId = null)
    {
        $query = Forecast::query();

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->with(['product', 'store'])
            ->orderBy('forecast_date')
            ->get();
    }
}

