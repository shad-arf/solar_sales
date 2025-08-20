@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Supplier Purchase History</h2>
    <div>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Purchases
        </a>
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> New Purchase Order
        </a>
    </div>
</div>

<!-- Supplier Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-building"></i> {{ $supplier->name }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        @if($supplier->contact_person)
                            <p class="mb-1"><strong>Contact Person:</strong> {{ $supplier->contact_person }}</p>
                        @endif
                        @if($supplier->email)
                            <p class="mb-1"><strong>Email:</strong> {{ $supplier->email }}</p>
                        @endif
                        @if($supplier->phone)
                            <p class="mb-1"><strong>Phone:</strong> {{ $supplier->phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($supplier->address)
                            <p class="mb-1"><strong>Address:</strong> {{ $supplier->address }}</p>
                        @endif
                        @if($supplier->city)
                            <p class="mb-1"><strong>City:</strong> {{ $supplier->city }}</p>
                        @endif
                        @if($supplier->country)
                            <p class="mb-1"><strong>Country:</strong> {{ $supplier->country }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle"></i> New Purchase from this Supplier
                    </a>
                    <span class="badge bg-info">Status: {{ ucfirst($supplier->status) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Purchases</h5>
                        <h3>{{ $stats['total_purchases'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-plus fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Amount</h5>
                        <h3>${{ number_format($stats['total_amount'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Last Purchase</h5>
                        <h3>
                            @if($stats['last_purchase_date'])
                                {{ \Carbon\Carbon::parse($stats['last_purchase_date'])->format('M d, Y') }}
                            @else
                                Never
                            @endif
                        </h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calendar-event fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Purchase History Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Purchase History
        </h5>
    </div>
    <div class="card-body">
        @if($purchases->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Purchase #</th>
                            <th>Purchase Date</th>
                            <th class="text-end">Total Items</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-center">Status</th>
                            <th>Created By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $purchase)
                        <tr>
                            <td>
                                <strong>{{ $purchase->purchase_number }}</strong>
                                @if($purchase->notes)
                                    <br><small class="text-muted">{{ Str::limit($purchase->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ $purchase->total_items }}</span>
                            </td>
                            <td class="text-end">
                                <strong>${{ number_format($purchase->total_amount, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $purchase->status_badge }}">
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $purchase->creator->name }}</small>
                                <br><small class="text-muted">{{ $purchase->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $purchase) }}"
                                       class="btn btn-sm btn-outline-info"
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($purchase->status === 'pending')
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit Purchase">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} results
                </div>
                <div>
                    {{ $purchases->links() }}
                </div>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-cart-x fs-1 mb-3"></i>
                <h5>No purchase history found</h5>
                <p>This supplier doesn't have any purchase orders yet.</p>
                <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Create First Purchase Order
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Recent Purchase Items -->
@if($purchases->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-box-seam"></i> Most Recently Purchased Items
        </h6>
    </div>
    <div class="card-body">
        @php
            $recentItems = $purchases->take(3)->flatMap(function($purchase) {
                return $purchase->purchaseItems;
            })->take(10);
        @endphp

        @if($recentItems->count() > 0)
            <div class="row">
                @foreach($recentItems as $item)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="border rounded p-3">
                            <h6 class="mb-1">{{ $item->item->name }}</h6>
                            <p class="mb-1"><small class="text-muted">Code: {{ $item->item->code }}</small></p>
                            <p class="mb-1"><strong>Qty:</strong> {{ number_format($item->quantity_purchased) }}</p>
                            <p class="mb-0"><strong>Price:</strong> ${{ number_format($item->purchase_price, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endsection