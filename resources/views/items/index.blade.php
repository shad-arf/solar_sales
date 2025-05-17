@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">All Items</h2>
    <div>
        <a href="{{ route('items.create') }}" class="btn btn-success me-2">Add Item</a>
        <a href="{{ route('items.lowStock') }}" class="btn btn-warning me-2">Low Stock</a>
        <a href="{{ route('items.outOfStock') }}" class="btn btn-danger me-2">Out of Stock</a>
        <a href="{{ route('items.trashed') }}" class="btn btn-secondary">Deleted Items</a>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th class="text-end">Price ($)</th>
            <th class="text-end">Qty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
        <tr>
            <td>{{ $item->code }}</td>
            <td>{{ $item->name }}</td>
            <td class="text-end">{{ number_format($item->price, 2) }}</td>
            <td class="text-end {{ $item->quantity == 0 ? 'text-danger' : ($item->quantity < 10 ? 'text-warning' : '') }}">
                {{ $item->quantity }}
            </td>
            <td>
                <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('items.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Soft delete this item?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">No items available.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
