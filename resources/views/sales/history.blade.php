@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Payment History for {{ $customer->name }}</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sale ID</th>
            <th>Item</th>
            <th>Quantity</th>
            <th>Total ($)</th>
            <th>Paid ($)</th>
            <th>Date</th>
            <th>Payments</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
        <tr>
            <td>{{ $sale->id }}</td>
            <td>{{ $sale->item->name ?? 'Deleted Item' }}</td>
            <td>{{ $sale->quantity }}</td>
            <td>{{ number_format($sale->total, 2) }}</td>
            <td>{{ number_format($sale->paid, 2) }}</td>
            <td>{{ $sale->date }}</td>
            <td>
                @forelse($sale->payments as $payment)
                    <div>
                        ${{ number_format($payment->amount, 2) }} <small class="text-muted">{{ $payment->paid_at }}</small>
                    </div>
                @empty
                    <small class="text-muted">No payments</small>
                @endforelse
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<a href="{{ route('sales.index') }}" class="btn btn-secondary">Back to Sales</a>
@endsection
