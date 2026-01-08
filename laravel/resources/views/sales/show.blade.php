@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Sale Details</h4>
                <div>
                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Sale Date:</dt>
                    <dd class="col-sm-9">{{ $sale->sale_date->format('Y-m-d') }}</dd>

                    <dt class="col-sm-3">Product:</dt>
                    <dd class="col-sm-9">{{ $sale->product->name }} ({{ $sale->product->sku }})</dd>

                    <dt class="col-sm-3">Store:</dt>
                    <dd class="col-sm-9">{{ $sale->store->name }}</dd>

                    <dt class="col-sm-3">Quantity:</dt>
                    <dd class="col-sm-9">{{ $sale->quantity }}</dd>

                    <dt class="col-sm-3">Price:</dt>
                    <dd class="col-sm-9">${{ number_format($sale->price, 2) }}</dd>

                    <dt class="col-sm-3">Total Amount:</dt>
                    <dd class="col-sm-9"><strong>${{ number_format($sale->total_amount, 2) }}</strong></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

