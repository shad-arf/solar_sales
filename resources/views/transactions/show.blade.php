@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Transaction Details</h2>
        <div class="btn-group">
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaction Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Transaction Date</label>
                                <p class="fw-bold">{{ $transaction->transaction_date->format('F d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Reference Number</label>
                                <p class="fw-bold">
                                    @if($transaction->reference_number)
                                        <span class="badge bg-secondary fs-6">{{ $transaction->reference_number }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <p class="fw-bold">{{ $transaction->description }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Account</label>
                                <p class="fw-bold">
                                    <span class="badge bg-info fs-6">{{ $transaction->account->code }}</span>
                                    {{ $transaction->account->name }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Transaction Type</label>
                                <p class="fw-bold">
                                    <span class="badge bg-{{ 
                                        $transaction->transaction_type === 'revenue' ? 'success' : 
                                        ($transaction->transaction_type === 'expense' ? 'danger' : 'primary') 
                                    }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Debit Amount</label>
                                <p class="fw-bold">
                                    @if($transaction->debit_amount > 0)
                                        <span class="text-danger fs-4">${{ number_format($transaction->debit_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Credit Amount</label>
                                <p class="fw-bold">
                                    @if($transaction->credit_amount > 0)
                                        <span class="text-success fs-4">${{ number_format($transaction->credit_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Created</label>
                                <p class="fw-bold">{{ $transaction->created_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Last Updated</label>
                                <p class="fw-bold">{{ $transaction->updated_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Account Code</label>
                        <p class="fw-bold">{{ $transaction->account->code }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Account Name</label>
                        <p class="fw-bold">{{ $transaction->account->name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Account Type</label>
                        <p class="fw-bold">
                            <span class="badge bg-secondary">{{ ucfirst($transaction->account->type) }}</span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Account Description</label>
                        <p class="fw-bold">{{ $transaction->account->description ?: 'No description available' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Current Account Balance</label>
                        <p class="fw-bold fs-5 {{ $transaction->account->balance >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($transaction->account->balance, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Transaction
                        </a>
                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this transaction? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection