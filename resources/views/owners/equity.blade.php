@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">{{ $owner->name }} - Equity History</h2>
    <div>
        <a href="{{ route('owners.show', $owner) }}" class="btn btn-outline-info me-2">
            <i class="bi bi-eye"></i> View Owner Details
        </a>
        <a href="{{ route('owners.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Owners
        </a>
    </div>
</div>

<!-- Owner Summary Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="text-center">
                    <h5 class="mb-1">{{ $owner->name }}</h5>
                    <span class="badge bg-info">{{ $owner->ownership_display }}</span>
                    <span class="badge {{ $owner->is_active ? 'bg-success' : 'bg-secondary' }} ms-2">
                        {{ $owner->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Total Investments</h6>
                        <h4 class="text-success mb-0">${{ number_format($owner->total_investments, 2) }}</h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Total Drawings</h6>
                        <h4 class="text-danger mb-0">${{ number_format($owner->total_drawings, 2) }}</h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Net Equity</h6>
                        <h4 class="{{ $owner->net_equity >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                            ${{ number_format($owner->net_equity, 2) }}
                        </h4>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Transactions</h6>
                        <h4 class="text-primary mb-0">{{ $transactions->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('owners.equity', $owner) }}" id="filterForm">
            <div class="row g-3">
                <!-- Transaction Type Filter -->
                <div class="col-md-3">
                    <label for="type" class="form-label">Transaction Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="investment" {{ request('type') == 'investment' ? 'selected' : '' }}>Investments</option>
                        <option value="drawing" {{ request('type') == 'drawing' ? 'selected' : '' }}>Drawings</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>

                <!-- Date To -->
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>

                <!-- Action Buttons -->
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('owners.equity', $owner) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Transaction History</h5>
        <div class="text-muted">
            @if(request()->hasAny(['type', 'date_from', 'date_to']))
                Filtered Results
            @else
                All Transactions
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @foreach($transactions as $transaction)
                            @php 
                                if($transaction->type === 'investment') {
                                    $runningBalance += $transaction->amount;
                                } else {
                                    $runningBalance -= $transaction->amount;
                                }
                            @endphp
                            <tr>
                                <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type === 'investment' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        <i class="bi bi-{{ $transaction->type === 'investment' ? 'arrow-up' : 'arrow-down' }}"></i>
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $transaction->description }}</div>
                                    @if($transaction->notes)
                                        <small class="text-muted">{{ $transaction->notes }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->reference_number)
                                        <code class="text-muted">{{ $transaction->reference_number }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="{{ $transaction->type === 'investment' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'investment' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong class="{{ $runningBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($runningBalance, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Net Total:</th>
                            <th class="text-end">
                                <span class="{{ $owner->net_equity >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($owner->net_equity, 2) }}
                                </span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No Transactions Found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['type', 'date_from', 'date_to']))
                        No transactions match your filter criteria.
                        <br><a href="{{ route('owners.equity', $owner) }}" class="btn btn-sm btn-outline-primary mt-2">Clear Filters</a>
                    @else
                        This owner hasn't made any investments or drawings yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($transactions->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} transactions
        </div>
        <div>
            {{ $transactions->appends(request()->query())->links('pagination.bootstrap-5') }}
        </div>
    </div>
@endif

<!-- Summary Cards -->
@if(request()->hasAny(['type', 'date_from', 'date_to']) && $transactions->count() > 0)
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6>Filtered Investments</h6>
                    <h4>${{ number_format($transactions->where('type', 'investment')->sum('amount'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6>Filtered Drawings</h6>
                    <h4>${{ number_format($transactions->where('type', 'drawing')->sum('amount'), 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6>Filtered Net</h6>
                    <h4>
                        ${{ number_format($transactions->where('type', 'investment')->sum('amount') - $transactions->where('type', 'drawing')->sum('amount'), 2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit on filter changes
    const autoSubmitFields = ['type'];
    
    autoSubmitFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });
});
</script>
@endsection