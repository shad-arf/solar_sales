@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Owner Details</h2>
    <div>
        <a href="{{ route('owners.edit', $owner) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil-square"></i> Edit Owner
        </a>
        <a href="{{ route('owners.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Owners
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Owner Information Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Owner Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Name</h6>
                        <p class="mb-3"><strong>{{ $owner->name }}</strong></p>
                        
                        <h6 class="text-muted">Ownership Percentage</h6>
                        <p class="mb-3">
                            <span class="badge bg-info fs-6">{{ $owner->ownership_display }}</span>
                        </p>
                        
                        <h6 class="text-muted">Status</h6>
                        <p class="mb-3">
                            <span class="badge {{ $owner->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">
                                {{ $owner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Email</h6>
                        <p class="mb-3">
                            @if($owner->email)
                                <i class="bi bi-envelope me-1"></i>{{ $owner->email }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </p>
                        
                        <h6 class="text-muted">Phone</h6>
                        <p class="mb-3">
                            @if($owner->phone)
                                <i class="bi bi-telephone me-1"></i>{{ $owner->phone }}
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </p>
                        
                        <h6 class="text-muted">Member Since</h6>
                        <p class="mb-3">{{ $owner->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
                
                @if($owner->address)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Address</h6>
                            <p class="mb-3">{{ $owner->address }}</p>
                        </div>
                    </div>
                @endif
                
                @if($owner->notes)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted">Notes</h6>
                            <p class="mb-0">{{ $owner->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Financial Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h6 class="text-muted">Total Investments</h6>
                        <h4 class="text-success">${{ number_format($owner->total_investments, 2) }}</h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Total Drawings</h6>
                        <h4 class="text-danger">${{ number_format($owner->total_drawings, 2) }}</h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Net Equity</h6>
                        <h4 class="{{ $owner->net_equity >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($owner->net_equity, 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="{{ route('owners.equity', $owner) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-clock-history"></i> View All History
                </a>
            </div>
            <div class="card-body">
                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge {{ $transaction->type === 'investment' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td class="text-end">
                                        <span class="{{ $transaction->type === 'investment' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->type === 'investment' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No transactions recorded yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Quick Actions Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('owners.edit', $owner) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square"></i> Edit Owner Info
                    </a>
                    <a href="{{ route('owners.equity', $owner) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-clock-history"></i> View Transaction History
                    </a>
                    <form action="{{ route('owners.toggleStatus', $owner) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn w-100 {{ $owner->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                            <i class="bi bi-{{ $owner->is_active ? 'pause' : 'play' }}"></i>
                            {{ $owner->is_active ? 'Deactivate' : 'Activate' }} Owner
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="card bg-light">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Owner Statistics</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">First Investment</small>
                        <small>
                            @if($owner->ownerEquities()->where('type', 'investment')->exists())
                                {{ $owner->ownerEquities()->where('type', 'investment')->oldest()->first()->transaction_date->format('M Y') }}
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Total Transactions</small>
                        <small><strong>{{ $owner->ownerEquities()->count() }}</strong></small>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Investment Count</small>
                        <small><strong>{{ $owner->ownerEquities()->where('type', 'investment')->count() }}</strong></small>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Drawing Count</small>
                        <small><strong>{{ $owner->ownerEquities()->where('type', 'drawing')->count() }}</strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection