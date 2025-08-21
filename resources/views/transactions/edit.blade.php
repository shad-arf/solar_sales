@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Transaction</h2>
        <div class="btn-group">
            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-info">
                <i class="bi bi-eye"></i> View
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
                    <h5 class="card-title mb-0">Transaction Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                           id="transaction_date" name="transaction_date" 
                                           value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                           id="reference_number" name="reference_number" 
                                           value="{{ old('reference_number', $transaction->reference_number) }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ old('description', $transaction->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="account_id" class="form-label">Account <span class="text-danger">*</span></label>
                                    <select class="form-control @error('account_id') is-invalid @enderror" id="account_id" name="account_id" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $type => $accountGroup)
                                            <optgroup label="{{ ucfirst($type) }} Accounts">
                                                @foreach($accountGroup as $account)
                                                    <option value="{{ $account->id }}" 
                                                            {{ old('account_id', $transaction->account_id) == $account->id ? 'selected' : '' }}>
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('transaction_type') is-invalid @enderror" id="transaction_type" name="transaction_type" required>
                                        <option value="">Select Type</option>
                                        <option value="revenue" {{ old('transaction_type', $transaction->transaction_type) == 'revenue' ? 'selected' : '' }}>Revenue</option>
                                        <option value="expense" {{ old('transaction_type', $transaction->transaction_type) == 'expense' ? 'selected' : '' }}>Expense</option>
                                        <option value="owner_investment" {{ old('transaction_type', $transaction->transaction_type) == 'owner_investment' ? 'selected' : '' }}>Owner Investment</option>
                                        <option value="owner_drawing" {{ old('transaction_type', $transaction->transaction_type) == 'owner_drawing' ? 'selected' : '' }}>Owner Drawing</option>
                                        <option value="purchase" {{ old('transaction_type', $transaction->transaction_type) == 'purchase' ? 'selected' : '' }}>Purchase</option>
                                        <option value="sale" {{ old('transaction_type', $transaction->transaction_type) == 'sale' ? 'selected' : '' }}>Sale</option>
                                        <option value="other" {{ old('transaction_type', $transaction->transaction_type) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('transaction_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" step="0.01" min="0.01" 
                                               value="{{ old('amount', $transaction->debit_amount > 0 ? $transaction->debit_amount : $transaction->credit_amount) }}" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Entry Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Entry Type</option>
                                        <option value="debit" {{ old('type', $transaction->debit_amount > 0 ? 'debit' : 'credit') == 'debit' ? 'selected' : '' }}>Debit (Increase Assets/Expenses)</option>
                                        <option value="credit" {{ old('type', $transaction->debit_amount > 0 ? 'debit' : 'credit') == 'credit' ? 'selected' : '' }}>Credit (Increase Liabilities/Equity/Revenue)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Transaction
                            </button>
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Transaction</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Amount</label>
                        <p class="fw-bold fs-5">
                            @if($transaction->debit_amount > 0)
                                <span class="text-danger">Debit: ${{ number_format($transaction->debit_amount, 2) }}</span>
                            @else
                                <span class="text-success">Credit: ${{ number_format($transaction->credit_amount, 2) }}</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Account</label>
                        <p class="fw-bold">{{ $transaction->account->code }} - {{ $transaction->account->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <p class="fw-bold">{{ $transaction->updated_at->format('F d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Accounting Reference</h5>
                </div>
                <div class="card-body">
                    <h6>Debit vs Credit Quick Guide:</h6>
                    <div class="row">
                        <div class="col-12">
                            <strong class="text-danger">Debit Increases:</strong>
                            <ul class="small">
                                <li>Assets (Cash, Inventory, Equipment)</li>
                                <li>Expenses (Rent, Utilities, Salaries)</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <strong class="text-success">Credit Increases:</strong>
                            <ul class="small">
                                <li>Liabilities (Loans, Accounts Payable)</li>
                                <li>Equity (Owner Capital, Retained Earnings)</li>
                                <li>Revenue (Sales, Service Revenue)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection