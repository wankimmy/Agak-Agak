<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use App\Models\Location;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'total_stores' => Store::count(),
            'total_locations' => Location::count(),
            'total_sales' => Sale::count(),
            'total_revenue' => Sale::sum('total_amount'),
            'recent_sales' => Sale::with(['product', 'store'])
                ->orderBy('sale_date', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('dashboard', compact('stats'));
    }
}

