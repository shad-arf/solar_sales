@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Financial Transactions</h2>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Transaction
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Transaction History</h5>
        </div>
        <div class="card-body">
            @if($transactions->isEmpty())
                <div class="text-center py-4">
                    <i class="bi bi-journal-x fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No transactions found. <a href="{{ route('transactions.create') }}">Add your first transaction</a></p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td>
                                        @if($transaction->reference_number)
                                            <span class="badge bg-secondary">{{ $transaction->reference_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->description }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $transaction->account->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $transaction->transaction_type === 'revenue' ? 'success' : 
                                            ($transaction->transaction_type === 'expense' ? 'danger' : 'primary') 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($transaction->debit_amount > 0)
                                            <span class="text-danger fw-bold">
                                                ${{ number_format($transaction->debit_amount, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->credit_amount > 0)
                                            <span class="text-success fw-bold">
                                                ${{ number_format($transaction->credit_amount, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this transaction?')" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $transactions->links('pagination.bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Credits</h5>
                    <h3>${{ number_format($transactions->sum('credit_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Debits</h5>
                    <h3>${{ number_format($transactions->sum('debit_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Net Balance</h5>
                    <h3>${{ number_format($transactions->sum('credit_amount') - $transactions->sum('debit_amount'), 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Transactions</h5>
                    <h3>{{ $transactions->total() }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection