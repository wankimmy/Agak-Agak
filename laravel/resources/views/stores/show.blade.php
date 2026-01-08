@extends('layouts.app')

@section('title', 'Store Details')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Store Details</h4>
                <div>
                    <a href="{{ route('stores.edit', $store) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('stores.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Name:</dt>
                    <dd class="col-sm-9">{{ $store->name }}</dd>

                    <dt class="col-sm-3">Location:</dt>
                    <dd class="col-sm-9">{{ $store->location->name }}</dd>

                    <dt class="col-sm-3">Phone:</dt>
                    <dd class="col-sm-9">{{ $store->phone ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9">{{ $store->email ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Status:</dt>
                    <dd class="col-sm-9">
                        @if($store->is_active)
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

