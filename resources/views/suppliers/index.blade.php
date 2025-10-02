@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Supplier Management</h2>
    <div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Add New Supplier
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('suppliers.index') }}" id="searchForm">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-md-6">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Supplier name, contact person, email, phone..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Suppliers</h5>
                        <h3>{{ $stats['total_suppliers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-building fs-1"></i>
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
                        <h5 class="card-title">Active Suppliers</h5>
                        <h3>{{ $stats['active_suppliers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Inactive Suppliers</h5>
                        <h3>{{ $stats['inactive_suppliers'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle fs-1"></i>
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
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Contact Info</th>
                    <th>Location</th>
                    <th class="text-center">Status</th>
                    <th>Added On</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <strong>{{ $supplier->name }}</strong>
                        @if($supplier->notes)
                            <br><small class="text-muted">{{ Str::limit($supplier->notes, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($supplier->contact_person)
                            {{ $supplier->contact_person }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($supplier->email)
                            <div><i class="bi bi-envelope me-1"></i>{{ $supplier->email }}</div>
                        @endif
                        @if($supplier->phone)
                            <div><i class="bi bi-telephone me-1"></i>{{ $supplier->phone }}</div>
                        @endif
                        @if(!$supplier->email && !$supplier->phone)
                            <span class="text-muted">No contact info</span>
                        @endif
                    </td>
                    <td>
                        @if($supplier->city || $supplier->country)
                            <div>
                                @if($supplier->city){{ $supplier->city }}@endif
                                @if($supplier->city && $supplier->country), @endif
                                @if($supplier->country){{ $supplier->country }}@endif
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $supplier->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($supplier->status) }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $supplier->created_at->format('M d, Y') }}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="btn btn-sm btn-outline-info"
                               title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Edit Supplier">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="{{ route('suppliers.history', $supplier->id) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Purchase History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <form action="{{ route('suppliers.toggleStatus', $supplier) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $supplier->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                        title="{{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }} Supplier">
                                    <i class="bi bi-{{ $supplier->status === 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            @if($supplier->purchases()->count() == 0)
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete supplier {{ $supplier->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Supplier">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        @if(request()->hasAny(['search', 'status']))
                            No suppliers found matching your criteria.
                            <br><a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                        @else
                            No suppliers found.
                            <br><a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-success mt-2">Add First Supplier</a>
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
        Showing {{ $suppliers->firstItem() ?? 0 }} to {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} results
    </div>
    <div>
        {{ $suppliers->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    // Auto-submit on dropdown changes
    const autoSubmitFields = ['status'];
    
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