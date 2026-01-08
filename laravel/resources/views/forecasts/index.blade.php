@extends('layouts.app')

@section('title', 'Forecasts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Sales Forecasts</h2>
    <a href="{{ route('forecasts.create') }}" class="btn btn-primary">Generate Forecast</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="forecastsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Store</th>
                    <th>Forecast Value</th>
                    <th>Lower Bound</th>
                    <th>Upper Bound</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($forecasts as $forecast)
                    <tr>
                        <td>{{ $forecast->id }}</td>
                        <td>{{ $forecast->forecast_date->format('Y-m-d') }}</td>
                        <td>{{ $forecast->product ? $forecast->product->name : 'All Products' }}</td>
                        <td>{{ $forecast->store ? $forecast->store->name : 'All Stores' }}</td>
                        <td>${{ number_format($forecast->forecast_value, 2) }}</td>
                        <td>${{ number_format($forecast->lower_bound ?? $forecast->forecast_value, 2) }}</td>
                        <td>${{ number_format($forecast->upper_bound ?? $forecast->forecast_value, 2) }}</td>
                        <td>
                            <a href="{{ route('forecasts.show', $forecast->id) }}" class="btn btn-sm btn-info">View</a>
                            <form action="{{ route('forecasts.destroy', $forecast) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#forecastsTable').DataTable({
            order: [[1, 'asc']],
            pageLength: 25
        });
    });
</script>
@endpush

