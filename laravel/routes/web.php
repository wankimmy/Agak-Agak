<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingController;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/demo', [LandingController::class, 'demo'])->name('landing.demo');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('locations', LocationController::class);
    Route::resource('stores', StoreController::class);
    Route::resource('products', ProductController::class);
    Route::resource('sales', SaleController::class);
    Route::get('/sales/upload', [SaleController::class, 'upload'])->name('sales.upload');
    Route::get('/sales/template/download', [SaleController::class, 'downloadTemplate'])->name('sales.downloadTemplate');
    Route::post('/sales/upload', [SaleController::class, 'processUpload'])->name('sales.processUpload');

    Route::get('/forecasts', [ForecastController::class, 'index'])->name('forecasts.index');
    Route::get('/forecasts/create', [ForecastController::class, 'create'])->name('forecasts.create');
    Route::post('/forecasts/generate', [ForecastController::class, 'generate'])->name('forecasts.generate');
    Route::get('/forecasts/{id}', [ForecastController::class, 'show'])->name('forecasts.show');
    Route::delete('/forecasts/{forecast}', [ForecastController::class, 'destroy'])->name('forecasts.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

