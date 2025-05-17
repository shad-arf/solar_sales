@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Deleted Items</h2>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">All Items</a>
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
            <td class="text-end">{{ $item->quantity }}</td>
            <td>
                <form action="{{ route('items.restore', $item->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-warning">Restore</button>
                </form>
                <form action="{{ route('items.forceDelete', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this item?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete Forever</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">No deleted items.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
