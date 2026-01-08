@extends('layouts.app')

@section('title', 'Generate Forecast')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4>Generate Sales Forecast</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Note:</strong> The forecast uses AI-powered TimeGPT with exogenous variables (price, promotions, stock availability, holidays) to generate accurate predictions.
                    <br><strong>Forecast Horizons:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>7 Days:</strong> Short-term planning, inventory management</li>
                        <li><strong>30 Days:</strong> Medium-term planning, monthly forecasts</li>
                        <li><strong>90 Days:</strong> Long-term planning, quarterly forecasts</li>
                    </ul>
                </div>

                <form action="{{ route('forecasts.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="product_id" class="form-label">Product (Optional)</label>
                        <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="store_id" class="form-label">Store (Optional)</label>
                        <select class="form-select @error('store_id') is-invalid @enderror" id="store_id" name="store_id">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="forecast_horizon" class="form-label">Forecast Horizon *</label>
                        <select class="form-select @error('forecast_horizon') is-invalid @enderror" id="forecast_horizon" name="forecast_horizon" required>
                            <option value="7" {{ old('forecast_horizon', 30) == 7 ? 'selected' : '' }}>7 Days (Short-term)</option>
                            <option value="30" {{ old('forecast_horizon', 30) == 30 ? 'selected' : '' }}>30 Days (Medium-term)</option>
                            <option value="90" {{ old('forecast_horizon', 30) == 90 ? 'selected' : '' }}>90 Days (Long-term)</option>
                        </select>
                        @error('forecast_horizon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Select the number of days to forecast ahead</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('forecasts.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Generate Forecast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

