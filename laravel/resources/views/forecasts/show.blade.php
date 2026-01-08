@extends('layouts.app')

@section('title', 'Forecast Details')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Forecast Details</h4>
                <a href="{{ route('forecasts.index') }}" class="btn btn-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Forecast Date:</dt>
                    <dd class="col-sm-9">{{ $forecast->forecast_date->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Product:</dt>
                    <dd class="col-sm-9">{{ $forecast->product ? $forecast->product->name : 'All Products' }}</dd>

                    <dt class="col-sm-3">Store:</dt>
                    <dd class="col-sm-9">{{ $forecast->store ? $forecast->store->name : 'All Stores' }}</dd>

                    <dt class="col-sm-3">Forecast Value:</dt>
                    <dd class="col-sm-9"><strong>${{ number_format($forecast->forecast_value, 2) }}</strong></dd>

                    <dt class="col-sm-3">Lower Bound:</dt>
                    <dd class="col-sm-9">${{ number_format($forecast->lower_bound ?? $forecast->forecast_value, 2) }}</dd>

                    <dt class="col-sm-3">Upper Bound:</dt>
                    <dd class="col-sm-9">${{ number_format($forecast->upper_bound ?? $forecast->forecast_value, 2) }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

