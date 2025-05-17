@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Sales</h2>
    <a href="{{ route('sales.create') }}" class="btn btn-success">Record Sale</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Item</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Total ($)</th>
            <th class="text-end">Paid ($)</th>
            <th class="text-end">Outstanding ($)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
        @php
            $total       = $sale->item->price * $sale->quantity;
            $outstanding = $total - $sale->paid;
        @endphp
        <tr>
            <td>{{ \Carbon\Carbon::parse($sale->date)->format('Y-m-d') }}</td>
            <td>{{ $sale->customer->name }}</td>
            <td>{{ $sale->item->name }}</td>
            <td class="text-end">{{ $sale->quantity }}</td>
            <td class="text-end">{{ number_format($total, 2) }}</td>
            <td class="text-end">{{ number_format($sale->paid, 2) }}</td>
            <td class="text-end {{ $outstanding > 0 ? 'text-danger' : '' }}">{{ number_format($outstanding, 2) }}</td>
            <td>
                <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-primary">Edit</a>
                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete sale? This will adjust customer loan.');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center">No sales recorded.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
