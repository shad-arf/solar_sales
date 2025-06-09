@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Sales</h2>
    <a href="{{ route('sales.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Record Sale
    </a>
</div>

<table class="table table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Items</th>
            <th class="text-end">Total ($)</th>
            <th class="text-end">Paid ($)</th>
            <th class="text-end">Outstanding ($)</th>
            <th>Invoice</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $sale)
            @php
                $totalAmount    = (float) $sale->total;
                $paidAmount     = (float) $sale->paid_amount;
                $outstandingAmt = $totalAmount - $paidAmount;
                $itemsDisplay   = $sale->orderItems->map(fn($oi) => "{$oi->item->name} (×{$oi->quantity})")->implode(', ');
            @endphp

            <tr>
                <td>{{ $sale->sale_date }}</td>
                <td>{{ $sale->customer->name ?? '— Deleted Customer —' }}</td>
                <td style="min-width: 200px;">{{ $itemsDisplay ?: '—' }}</td>
                <td class="text-end">${{ number_format($totalAmount, 2) }}</td>
                <td class="text-end">${{ number_format($paidAmount, 2) }}</td>
                  <td class="text-end
                    @if($outstandingAmt > 0) text-danger fw-bold
                    @elseif($outstandingAmt < 0) text-success fw-bold
                    @endif
                ">
                    ${{ number_format($outstandingAmt, 2) }}
                </td>


                <td>
                    @if(! $sale->trashed())
                        <a href="{{ route('sales.show', $sale->id) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="View / Print Invoice">
                            <i class="bi bi-receipt"></i>
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($sale->trashed())
                        <form action="{{ route('sales.restore', $sale->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-warning" title="Restore Sale">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </form>
                        <form action="{{ route('sales.forceDelete', $sale->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Permanently delete this sale?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Delete Forever">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('sales.edit', $sale->id) }}"
                           class="btn btn-sm btn-primary"
                           title="Edit Sale">
                            <i class="bi bi-pencil-square">  </i>
                        </a>
                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Soft delete this sale?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Soft Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('sales.history', $sale->customer_id) }}"
                           class="btn btn-sm btn-info"
                           title="View Customer History">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <form action="{{ route('customers.clearLoan', $sale->customer_id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Clear all loan for this customer?');">
                            @csrf
                            <button class="btn btn-sm btn-warning" title="Clear Loan">
                                <i class="bi bi-cash-stack"></i>
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    No sales recorded yet.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-end">
    {{ $sales->links() }}
</div>
@endsection
