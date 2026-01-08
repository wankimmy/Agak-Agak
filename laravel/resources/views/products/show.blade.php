@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Product Details</h4>
                <div>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Name:</dt>
                    <dd class="col-sm-9">{{ $product->name }}</dd>

                    <dt class="col-sm-3">SKU:</dt>
                    <dd class="col-sm-9">{{ $product->sku }}</dd>

                    <dt class="col-sm-3">Description:</dt>
                    <dd class="col-sm-9">{{ $product->description ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Base Price:</dt>
                    <dd class="col-sm-9">${{ number_format($product->base_price, 2) }}</dd>

                    <dt class="col-sm-3">Category:</dt>
                    <dd class="col-sm-9">{{ $product->category ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9">
                        @if($product->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

