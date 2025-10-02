@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Account Details: {{ $account->name }}</h2>
    <div>
        <a href="{{ route('accounts.edit', $account) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit Account
        </a>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Accounts
        </a>
    </div>
</div>

<div class="row">
    <!-- Account Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Account Code</label>
                            <div class="h5">{{ $account->code }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Account Name</label>
                            <div class="h5">{{ $account->name }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Account Type</label>
                            <div>
                                <span class="badge bg-secondary fs-6">{{ \App\Models\Account::TYPES[$account->type] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if($account->is_active)
                                    <span class="badge bg-success fs-6">Active</span>
                                @else
                                    <span class="badge bg-danger fs-6">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Current Balance</label>
                            @php
                                $balance = $account->balance;
                                $balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';
                            @endphp
                            <div class="h4 {{ $balanceClass }}">
                                ${{ number_format(abs($balance), 2) }}
                                @if($balance < 0) (Dr) @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Created</label>
                            <div>{{ $account->created_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>
                    </div>
                </div>
                
                @if($account->description)
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label text-muted">Description</label>
                                <div class="p-3 bg-light rounded">{{ $account->description }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Transactions</h5>
                <a href="{{ route('transactions.index', ['account_id' => $account->id]) }}" class="btn btn-sm btn-outline-primary">
                    View All Transactions
                </a>
            </div>
            <div class="card-body">
                @php
                    $recentTransactions = $account->transactions()->latest()->limit(10)->get();
                @endphp
                
                @if($recentTransactions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $runningBalance = 0; @endphp
                                @foreach($recentTransactions->reverse() as $transaction)
                                    @php
                                        if (in_array($account->type, ['asset', 'expense'])) {
                                            $runningBalance += $transaction->debit_amount - $transaction->credit_amount;
                                        } else {
                                            $runningBalance += $transaction->credit_amount - $transaction->debit_amount;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                        <td>{{ $transaction->description }}</td>
                                        <td>
                                            @if($transaction->debit_amount > 0)
                                                <span class="text-danger">${{ number_format($transaction->debit_amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->credit_amount > 0)
                                                <span class="text-success">${{ number_format($transaction->credit_amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php $balanceClass = $runningBalance >= 0 ? 'text-success' : 'text-danger'; @endphp
                                            <span class="{{ $balanceClass }}">
                                                ${{ number_format(abs($runningBalance), 2) }}
                                                @if($runningBalance < 0) (Dr) @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-receipt display-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">No Transactions Found</h5>
                        <p class="text-muted">This account has no transaction history yet.</p>
                        <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Transaction
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Account Summary -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Account Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Transactions:</span>
                    <span class="fw-bold">{{ $account->transactions()->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Debits:</span>
                    <span class="text-danger">${{ number_format($account->transactions()->sum('debit_amount'), 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Credits:</span>
                    <span class="text-success">${{ number_format($account->transactions()->sum('credit_amount'), 2) }}</span>
                </div>
                <hr>
                @php
                    $balance = $account->balance;
                    $balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Current Balance:</span>
                    <span class="fw-bold {{ $balanceClass }}">
                        ${{ number_format(abs($balance), 2) }}
                        @if($balance < 0) (Dr) @endif
                    </span>
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
                    <a href="{{ route('transactions.create', ['account_id' => $account->id]) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Transaction
                    </a>
                    <a href="{{ route('accounts.edit', $account) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit Account
                    </a>
                    <form method="POST" action="{{ route('accounts.toggleStatus', $account) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-sm w-100 {{ $account->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                            <i class="bi bi-{{ $account->is_active ? 'pause' : 'play' }}"></i>
                            {{ $account->is_active ? 'Deactivate' : 'Activate' }} Account
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Type Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">{{ \App\Models\Account::TYPES[$account->type] }} Account</h6>
            </div>
            <div class="card-body">
                @if($account->type === 'asset')
                    <p class="small text-muted">Assets represent resources owned by the business. Normal balance is Debit.</p>
                @elseif($account->type === 'liability')
                    <p class="small text-muted">Liabilities represent debts owed by the business. Normal balance is Credit.</p>
                @elseif($account->type === 'equity')
                    <p class="small text-muted">Equity represents the owner's interest in the business. Normal balance is Credit.</p>
                @elseif($account->type === 'revenue')
                    <p class="small text-muted">Revenue represents income earned by the business. Normal balance is Credit.</p>
                @elseif($account->type === 'expense')
                    <p class="small text-muted">Expenses represent costs incurred by the business. Normal balance is Debit.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 1050;" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection