@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Owner Management</h2>
    <div>
        <a href="{{ route('owners.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Add New Owner
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('owners.index') }}" id="searchForm">
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
                               placeholder="Owner name, email, phone..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('owners.index') }}" class="btn btn-outline-secondary">
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
                        <h5 class="card-title">Total Owners</h5>
                        <h3>{{ $stats['total_owners'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
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
                        <h5 class="card-title">Active Owners</h5>
                        <h3>{{ $stats['active_owners'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-person-check fs-1"></i>
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
                        <h5 class="card-title">Total Investments</h5>
                        <h3>${{ number_format($stats['total_investments'], 0) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-stack fs-1"></i>
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
                        <h5 class="card-title">Net Equity</h5>
                        <h3>${{ number_format($stats['net_equity'], 0) }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-building fs-1"></i>
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
                    <th>Contact Info</th>
                    <th class="text-center">Ownership</th>
                    <th class="text-end">Total Investments</th>
                    <th class="text-end">Total Drawings</th>
                    <th class="text-end">Net Equity</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($owners as $owner)
                <tr>
                    <td>
                        <strong>{{ $owner->name }}</strong>
                        @if($owner->notes)
                            <br><small class="text-muted">{{ Str::limit($owner->notes, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($owner->email)
                            <div><i class="bi bi-envelope me-1"></i>{{ $owner->email }}</div>
                        @endif
                        @if($owner->phone)
                            <div><i class="bi bi-telephone me-1"></i>{{ $owner->phone }}</div>
                        @endif
                        @if(!$owner->email && !$owner->phone)
                            <span class="text-muted">No contact info</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $owner->ownership_display }}</span>
                    </td>
                    <td class="text-end">
                        <strong class="text-success">${{ number_format($owner->total_investments, 2) }}</strong>
                    </td>
                    <td class="text-end">
                        <strong class="text-danger">${{ number_format($owner->total_drawings, 2) }}</strong>
                    </td>
                    <td class="text-end">
                        <strong class="{{ $owner->net_equity >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($owner->net_equity, 2) }}
                        </strong>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $owner->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $owner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('owners.show', $owner) }}"
                               class="btn btn-sm btn-outline-info"
                               title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('owners.edit', $owner) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Edit Owner">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="{{ route('owners.equity', $owner) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="View Equity History">
                                <i class="bi bi-clock-history"></i>
                            </a>
                            <form action="{{ route('owners.toggleStatus', $owner) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $owner->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                        title="{{ $owner->is_active ? 'Deactivate' : 'Activate' }} Owner">
                                    <i class="bi bi-{{ $owner->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            @if($owner->ownerEquities()->count() == 0)
                                <form action="{{ route('owners.destroy', $owner) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete owner {{ $owner->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete Owner">
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
                        @if(request()->hasAny(['search', 'status']))
                            No owners found matching your criteria.
                            <br><a href="{{ route('owners.index') }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                        @else
                            No owners found.
                            <br><a href="{{ route('owners.create') }}" class="btn btn-sm btn-success mt-2">Add First Owner</a>
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
        Showing {{ $owners->firstItem() ?? 0 }} to {{ $owners->lastItem() ?? 0 }} of {{ $owners->total() }} results
    </div>
    <div>
        {{ $owners->appends(request()->query())->links('pagination.bootstrap-5') }}
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