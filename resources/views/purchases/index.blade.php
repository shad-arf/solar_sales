@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Purchase Management</h2>
    <div>
        <a href="{{ route('purchases.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> New Purchase Order
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('purchases.index') }}" id="searchForm">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Purchase #, supplier name..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Supplier Filter -->
                <div class="col-md-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date"
                           class="form-control"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date"
                           class="form-control"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <!-- Action Buttons -->
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Pending Orders</h5>
                        <h3>{{ $stats['pending_purchases'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">This Month</h5>
                        <h3>${{ number_format($stats['this_month_amount'], 2) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calendar-month fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Purchase #</th>
                    <th>Supplier</th>
                    <th>Purchase Date</th>
                    <th class="text-end">Total Items</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-center">Status</th>
                    <th>Created By</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>
                        <strong>{{ $purchase->purchase_number }}</strong>
                        @if($purchase->notes)
                            <br><small class="text-muted">{{ Str::limit($purchase->notes, 30) }}</small>
                        @endif
                    </td>
                    <td>
                        <div>
                            <strong>{{ $purchase->supplier->name }}</strong>
                            @if($purchase->supplier->contact_person)
                                <br><small class="text-muted">{{ $purchase->supplier->contact_person }}</small>
                            @endif
                        </div>
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
                                <form action="{{ route('purchases.complete', $purchase) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success" 
                                            title="Mark as Completed"
                                            onclick="return confirm('Mark this purchase as completed?')">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('suppliers.history', $purchase->supplier_id) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Supplier History">
                                <i class="bi bi-building"></i>
                            </a>
                            @if($purchase->status !== 'completed')
                                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete purchase {{ $purchase->purchase_number }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Purchase">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        @if(request()->hasAny(['search', 'supplier_id', 'status', 'date_from', 'date_to']))
                            No purchases found matching your criteria.
                            <br><a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                        @else
                            No purchases found.
                            <br><a href="{{ route('purchases.create') }}" class="btn btn-sm btn-success mt-2">Create First Purchase Order</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} results
    </div>
    <div>
        {{ $purchases->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    // Auto-submit on dropdown changes
    const autoSubmitFields = ['supplier_id', 'status'];
    
    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                searchForm.submit();
            });
        }
    });
});
</script>
@endsection