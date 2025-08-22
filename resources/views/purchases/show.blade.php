@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Purchase Order Details</h2>
    <div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Purchases
        </a>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Edit Purchase
        </a>
    </div>
</div>

<!-- Purchase Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Purchase Order - {{ $purchase->purchase_number }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Supplier Information</h6>
                        <p class="mb-1"><strong>{{ $purchase->supplier->name }}</strong></p>
                        @if($purchase->supplier->contact_person)
                            <p class="mb-1"><small class="text-muted">Contact: {{ $purchase->supplier->contact_person }}</small></p>
                        @endif
                        @if($purchase->supplier->email)
                            <p class="mb-1"><small class="text-muted">Email: {{ $purchase->supplier->email }}</small></p>
                        @endif
                        @if($purchase->supplier->phone)
                            <p class="mb-1"><small class="text-muted">Phone: {{ $purchase->supplier->phone }}</small></p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Purchase Details</h6>
                        <p class="mb-1"><strong>Purchase Date:</strong> {{ $purchase->purchase_date->format('M d, Y') }}</p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge {{ $purchase->status_badge }}">{{ ucfirst($purchase->status) }}</span>
                        </p>
                        <p class="mb-1"><strong>Created By:</strong> {{ $purchase->creator->name }}</p>
                        <p class="mb-1"><strong>Created On:</strong> {{ $purchase->created_at->format('M d, Y g:i A') }}</p>
                        @if($purchase->updated_at != $purchase->created_at)
                            <p class="mb-1"><strong>Last Updated:</strong> {{ $purchase->updated_at->format('M d, Y g:i A') }}</p>
                        @endif
                    </div>
                </div>
                @if($purchase->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">Notes</h6>
                            <p class="mb-0">{{ $purchase->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h6 class="text-primary">Purchase Summary</h6>
                <h3 class="text-primary">${{ number_format($purchase->total_amount, 2) }}</h3>
                <p class="mb-0">Total Purchase Amount</p>
                <hr>
                <p class="mb-0"><strong>{{ $purchase->total_items }}</strong> Items</p>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                @if($purchase->status === 'pending')
                    <form action="{{ route('purchases.complete', $purchase) }}" method="POST" class="mb-2">
                        @csrf
                        <button class="btn btn-success btn-sm w-100" 
                                onclick="return confirm('Mark this purchase as completed?')">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </button>
                    </form>
                @endif
                <a href="{{ route('suppliers.history', $purchase->supplier_id) }}" class="btn btn-outline-info btn-sm w-100">
                    <i class="bi bi-building"></i> Supplier History
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Items -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-box"></i> Purchase Items ({{ $purchase->purchaseItems->count() }})
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Purchase Price</th>
                        <th class="text-end">Line Total</th>
                        @if($purchase->status === 'completed')
                            <th class="text-end">Current Selling Price</th>
                            <th class="text-end">Profit Margin</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->purchaseItems as $purchaseItem)
                    <tr>
                        <td>
                            <code>{{ $purchaseItem->item->code }}</code>
                        </td>
                        <td>
                            <strong>{{ $purchaseItem->item->name }}</strong>
                            @if($purchaseItem->item->description)
                                <br><small class="text-muted">{{ Str::limit($purchaseItem->item->description, 50) }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="badge bg-secondary">{{ number_format($purchaseItem->quantity_purchased) }}</span>
                        </td>
                        <td class="text-end">
                            ${{ number_format($purchaseItem->purchase_price, 2) }}
                        </td>
                        <td class="text-end">
                            <strong>${{ number_format($purchaseItem->line_total, 2) }}</strong>
                        </td>
                        @if($purchase->status === 'completed')
                            <td class="text-end">
                                ${{ number_format($purchaseItem->item->primary_price ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                @if($purchaseItem->profit_margin > 0)
                                    <span class="badge bg-success">+{{ number_format($purchaseItem->profit_margin, 1) }}%</span>
                                @elseif($purchaseItem->profit_margin < 0)
                                    <span class="badge bg-danger">{{ number_format($purchaseItem->profit_margin, 1) }}%</span>
                                @else
                                    <span class="badge bg-secondary">0%</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="{{ $purchase->status === 'completed' ? 4 : 4 }}">Total</th>
                        <th class="text-end">
                            <h5 class="mb-0">${{ number_format($purchase->total_amount, 2) }}</h5>
                        </th>
                        @if($purchase->status === 'completed')
                            <th colspan="2"></th>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Stock Impact Information -->
@if($purchase->status === 'completed')
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-graph-up-arrow"></i> Stock Impact
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach($purchase->purchaseItems as $purchaseItem)
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3">
                    <h6>{{ $purchaseItem->item->name }}</h6>
                    <p class="mb-1"><strong>Added to Stock:</strong> {{ number_format($purchaseItem->quantity_purchased) }}</p>
                    <p class="mb-0"><strong>Current Stock:</strong> {{ number_format($purchaseItem->item->quantity) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<style>
.card-header h5, .card-header h6 {
    color: #495057;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.border {
    border-color: #dee2e6 !important;
}
</style>
@endsection