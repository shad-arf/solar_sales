@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Payment History for {{ $customer->name }}</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sale ID</th>
            <th>Items</th>
            <th>Total ($)</th>
            <th>Paid ($)</th>
            <th>Outstanding ($)</th>
            <th>Date</th>
            <th>Payments</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sales as $sale)
            @php
                $totalAmount  = (float) $sale->total;
                $paidAmount   = (float) $sale->paid_amount;
                $outstanding  = max(0, $totalAmount - $paidAmount);
                $itemsDisplay = $sale->orderItems->map(fn($oi) => "{$oi->item->name} (×{$oi->quantity})")->implode(', ');
            @endphp
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $itemsDisplay }}</td>
                <td>{{ number_format($totalAmount, 2) }}</td>
                <td>{{ number_format($paidAmount, 2) }}</td>
                <td class="{{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($outstanding, 2) }}
                </td>
                <td>{{ $sale->sale_date}}</td>
                <td>
                    @forelse($sale->payments as $payment)
                        <div>
                            ${{ number_format($payment->amount, 2) }}
                            <small class="text-muted">{{ $payment->paid_at->format('Y-m-d H:i') }}</small>
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
