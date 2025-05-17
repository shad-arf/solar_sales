@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Out of Stock Items</h2>
    <a href="{{ route('items.index') }}" class="btn btn-secondary">All Items</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th class="text-end">Price ($)</th>
            <th class="text-end">Qty</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
        <tr>
            <td>{{ $item->code }}</td>
            <td>{{ $item->name }}</td>
            <td class="text-end">{{ number_format($item->price, 2) }}</td>
            <td class="text-end">{{ $item->quantity }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">No out of stock items.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
