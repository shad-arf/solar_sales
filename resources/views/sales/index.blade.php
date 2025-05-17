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
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
        @php
            $total = $sale->total;
            $outstanding = $total - $sale->paid;
        @endphp
        <tr>
            <td>{{ \Carbon\Carbon::parse($sale->date)->format('Y-m-d') }}</td>
            <td>{{ $sale->customer->name ?? 'Deleted Customer' }}</td>
            <td>{{ $sale->item->name ?? 'Deleted Item' }}</td>
            <td class="text-end">{{ $sale->quantity }}</td>
            <td class="text-end">{{ number_format($total, 2) }}</td>
            <td class="text-end">{{ number_format($sale->paid, 2) }}</td>
            <td class="text-end {{ $outstanding > 0 ? 'text-danger' : '' }}">{{ number_format($outstanding, 2) }}</td>
            <td>
                @if($sale->trashed())
                    <span class="badge bg-danger">Deleted</span>
                @else
                    <span class="badge bg-success">Active</span>
                @endif
            </td>
            <td>
                @if($sale->trashed())
                    <form action="{{ route('sales.restore', $sale->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-warning">Restore</button>
                    </form>
                    <form action="{{ route('sales.forceDelete', $sale->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this sale?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete Forever</button>
                    </form>
                @else
                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('Soft delete this sale?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                    <a href="{{ route('sales.history', $sale->customer_id) }}" class="btn btn-sm btn-info">History</a>
                   <form action="{{ route('customers.clearLoan', $sale->customer_id) }}" method="POST">
                              @csrf
                        <button class="btn btn-sm btn-warning" onclick="return confirm('Clear all loan for this customer?')">Pay All</button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center">No sales recorded.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
