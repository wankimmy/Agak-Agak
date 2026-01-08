@extends('layouts.app')

@section('title', 'Stores')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Stores</h2>
    <a href="{{ route('stores.create') }}" class="btn btn-primary">Add New Store</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="storesTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stores as $store)
                    <tr>
                        <td>{{ $store->id }}</td>
                        <td>{{ $store->name }}</td>
                        <td>{{ $store->location->name }}</td>
                        <td>{{ $store->phone ?? 'N/A' }}</td>
                        <td>{{ $store->email ?? 'N/A' }}</td>
                        <td>
                            @if($store->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('stores.show', $store) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('stores.edit', $store) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('stores.destroy', $store) }}" method="POST" class="d-inline">
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
        $('#storesTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush

