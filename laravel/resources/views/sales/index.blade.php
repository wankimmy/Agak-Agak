@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Sales Records</h2>
    <div>
        <a href="{{ route('sales.upload') }}" class="btn btn-info">Upload Excel</a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">Add New Sale</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="salesTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Store</th>
                    <th>Quantity Sold</th>
                    <th>Price</th>
                    <th>Revenue</th>
                    <th>Stock Available</th>
                    <th>Location</th>
                    <th>Channel</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->id }}</td>
                        <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                        <td>{{ $sale->product->name }}</td>
                        <td>{{ $sale->store->name }}</td>
                        <td>{{ $sale->quantity_sold }}</td>
                        <td>${{ number_format($sale->price, 2) }}</td>
                        <td>${{ number_format($sale->revenue, 2) }}</td>
                        <td>
                            @if($sale->stock_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-danger">Out of Stock</span>
                            @endif
                        </td>
                        <td>{{ $sale->location ?? 'N/A' }}</td>
                        <td>{{ $sale->channel ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline">
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
        $('#salesTable').DataTable({
            order: [[1, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush

