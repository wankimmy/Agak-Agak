@extends('layouts.app')

@section('title', 'Location Details')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Location Details</h4>
                <div>
                    <a href="{{ route('locations.edit', $location) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Name:</dt>
                    <dd class="col-sm-9">{{ $location->name }}</dd>

                    <dt class="col-sm-3">Address:</dt>
                    <dd class="col-sm-9">{{ $location->address ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">City:</dt>
                    <dd class="col-sm-9">{{ $location->city ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">State:</dt>
                    <dd class="col-sm-9">{{ $location->state ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Country:</dt>
                    <dd class="col-sm-9">{{ $location->country ?? 'N/A' }}</dd>

                    <dt class="col-sm-3">Zip Code:</dt>
                    <dd class="col-sm-9">{{ $location->zip_code ?? 'N/A' }}</dd>
                </dl>

                <hr>

                <h5>Stores in this Location</h5>
                @if($location->stores->count() > 0)
                    <ul>
                        @foreach($location->stores as $store)
                            <li><a href="{{ route('stores.show', $store) }}">{{ $store->name }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p>No stores in this location.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

