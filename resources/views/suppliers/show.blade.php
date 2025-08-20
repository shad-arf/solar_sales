@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Supplier Details</h2>
    <div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Suppliers
        </a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary">
            <i class="bi bi-pencil-square"></i> Edit Supplier
        </a>
    </div>
</div>

<div class="row">
    <!-- Supplier Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ $supplier->name }}</h5>
                <span class="badge {{ $supplier->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                    {{ ucfirst($supplier->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Contact Information</h6>
                        @if($supplier->contact_person)
                            <p class="mb-1"><strong>Contact Person:</strong> {{ $supplier->contact_person }}</p>
                        @endif
                        @if($supplier->email)
                            <p class="mb-1"><strong>Email:</strong> {{ $supplier->email }}</p>
                        @endif
                        @if($supplier->phone)
                            <p class="mb-1"><strong>Phone:</strong> {{ $supplier->phone }}</p>
                        @endif
                        @if(!$supplier->contact_person && !$supplier->email && !$supplier->phone)
                            <p class="text-muted">No contact information available</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Address Information</h6>
                        @if($supplier->address || $supplier->city || $supplier->state || $supplier->country)
                            @if($supplier->address)
                                <p class="mb-1">{{ $supplier->address }}</p>
                            @endif
                            <p class="mb-1">
                                @if($supplier->city){{ $supplier->city }}@endif
                                @if($supplier->city && $supplier->state), @endif
                                @if($supplier->state){{ $supplier->state }}@endif
                                @if($supplier->postal_code) {{ $supplier->postal_code }}@endif
                            </p>
                            @if($supplier->country)
                                <p class="mb-1">{{ $supplier->country }}</p>
                            @endif
                        @else
                            <p class="text-muted">No address information available</p>
                        @endif
                    </div>
                </div>
                @if($supplier->notes)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary">Notes</h6>
                            <p class="mb-0">{{ $supplier->notes }}</p>
                        </div>
                    </div>
                @endif
                <div class="row mt-3">
                    <div class="col-12">
                        <small class="text-muted">
                            Added on {{ $supplier->created_at->format('M d, Y') }}
                            @if($supplier->updated_at != $supplier->created_at)
                                • Last updated {{ $supplier->updated_at->format('M d, Y') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Recent Purchases
                </h5>
            </div>
            <div class="card-body">
                @if($recentPurchases->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Purchase #</th>
                                    <th>Date</th>
                                    <th class="text-end">Items</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPurchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->purchase_number }}</td>
                                    <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                                    <td class="text-end">{{ $purchase->total_items }}</td>
                                    <td class="text-end">${{ number_format($purchase->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $purchase->status_badge }}">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('purchases.show', $purchase) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('suppliers.history', $supplier->id) }}" class="btn btn-outline-primary">
                            <i class="bi bi-list"></i> View All Purchase History
                        </a>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-cart-x fs-1 mb-3"></i>
                        <h6>No purchases yet</h6>
                        <p>This supplier doesn't have any purchase orders.</p>
                        <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Create First Purchase Order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics & Actions -->
    <div class="col-md-4">
        <!-- Statistics -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <h4 class="text-primary mb-0">{{ $stats['total_purchases'] }}</h4>
                        <small class="text-muted">Total Purchases</small>
                    </div>
                    <div class="col-12 mb-3">
                        <h4 class="text-success mb-0">${{ number_format($stats['total_amount'], 2) }}</h4>
                        <small class="text-muted">Total Amount</small>
                    </div>
                    <div class="col-12 mb-3">
                        <h4 class="text-warning mb-0">{{ $stats['pending_purchases'] }}</h4>
                        <small class="text-muted">Pending Orders</small>
                    </div>
                    <div class="col-12">
                        <h6 class="text-info mb-0">
                            @if($stats['last_purchase_date'])
                                {{ $stats['last_purchase_date']->format('M d, Y') }}
                            @else
                                Never
                            @endif
                        </h6>
                        <small class="text-muted">Last Purchase</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> New Purchase Order
                    </a>
                    <a href="{{ route('suppliers.history', $supplier->id) }}" class="btn btn-outline-info">
                        <i class="bi bi-clock-history"></i> Purchase History
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Edit Details
                    </a>
                    <form action="{{ route('suppliers.toggleStatus', $supplier) }}" method="POST">
                        @csrf
                        <button class="btn {{ $supplier->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} w-100">
                            <i class="bi bi-{{ $supplier->status === 'active' ? 'pause' : 'play' }}"></i>
                            {{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }} Supplier
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contact Card -->
        @if($supplier->email || $supplier->phone)
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Contact</h6>
            </div>
            <div class="card-body">
                @if($supplier->email)
                    <div class="mb-2">
                        <a href="mailto:{{ $supplier->email }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-envelope"></i> Send Email
                        </a>
                    </div>
                @endif
                @if($supplier->phone)
                    <div>
                        <a href="tel:{{ $supplier->phone }}" class="btn btn-outline-success btn-sm w-100">
                            <i class="bi bi-telephone"></i> Call Phone
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

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

.text-primary {
    color: #0d6efd !important;
}
</style>
@endsection