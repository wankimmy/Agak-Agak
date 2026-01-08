@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Locations</h2>
    <a href="{{ route('locations.create') }}" class="btn btn-primary">Add New Location</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="locationsTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Country</th>
                    <th>Stores</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $location)
                    <tr>
                        <td>{{ $location->id }}</td>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->city ?? 'N/A' }}</td>
                        <td>{{ $location->state ?? 'N/A' }}</td>
                        <td>{{ $location->country ?? 'N/A' }}</td>
                        <td>{{ $location->stores_count }}</td>
                        <td>
                            <a href="{{ route('locations.show', $location) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('locations.edit', $location) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('locations.destroy', $location) }}" method="POST" class="d-inline">
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
        $('#locationsTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 25
        });
    });
</script>
@endpush

