@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Account Management</h2>
    <div>
        <a href="{{ route('accounts.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Add New Account
        </a>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('accounts.index') }}" id="searchForm">
            <div class="row g-3">
                <!-- Search Box -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               placeholder="Account name, code, description..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Type Filter -->
                <div class="col-md-3">
                    <label for="type" class="form-label">Account Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Account::TYPES as $key => $value)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
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
                    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
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
                        <h5 class="card-title">Total Accounts</h5>
                        <h3>{{ $stats['total_accounts'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-bank fs-1"></i>
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
                        <h5 class="card-title">Active Accounts</h5>
                        <h3>{{ $stats['active_accounts'] }}</h3>
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
                        <h5 class="card-title">Inactive Accounts</h5>
                        <h3>{{ $stats['inactive_accounts'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Accounts List</h5>
        <div class="text-muted">
            Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() }} accounts
        </div>
    </div>
    <div class="card-body">
        @if($accounts->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            <tr>
                                <td>
                                    <strong>{{ $account->code }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $account->name }}</strong>
                                        @if($account->description)
                                            <br><small class="text-muted">{{ Str::limit($account->description, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ \App\Models\Account::TYPES[$account->type] }}</span>
                                </td>
                                <td>
                                    @php
                                        $balance = $account->balance;
                                        $balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';
                                    @endphp
                                    <span class="{{ $balanceClass }}">
                                        ${{ number_format(abs($balance), 2) }}
                                        @if($balance < 0) (Dr) @endif
                                    </span>
                                </td>
                                <td>
                                    @if($account->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('accounts.show', $account) }}" 
                                           class="btn btn-sm btn-outline-info"
                                           title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('accounts.edit', $account) }}" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('accounts.toggleStatus', $account) }}" 
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm {{ $account->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="bi bi-{{ $account->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        @if($account->transactions()->count() == 0)
                                            <form method="POST" 
                                                  action="{{ route('accounts.destroy', $account) }}" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to delete this account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $accounts->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-bank display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No accounts found</h4>
                <p class="text-muted">Try adjusting your search criteria or create a new account.</p>
                <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create First Account
                </a>
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 1050;" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 1050;" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection