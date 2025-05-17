@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Items</h2>
    <a href="{{ route('items.create') }}" class="btn btn-success">Add Item</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th class="text-end">Price ($)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
        <tr>
            <td>{{ $item->code }}</td>
            <td>{{ $item->name }}</td>
            <td class="text-end">{{ number_format($item->price, 2) }}</td>
            <td>
                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete item?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">No items found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
