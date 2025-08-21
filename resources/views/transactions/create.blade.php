@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add New Transaction</h2>
        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Transactions
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaction Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                           id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                           id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income (Money Coming In)</option>
                                        <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense (Money Going Out)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                               id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Reference</h5>
                </div>
                <div class="card-body">
                    <h6>Income Examples:</h6>
                    <ul class="small">
                        <li>Solar panel sales</li>
                        <li>Installation services</li>
                        <li>Maintenance contracts</li>
                        <li>Consultation fees</li>
                    </ul>

                    <hr>
                    
                    <h6>Expense Examples:</h6>
                    <ul class="small">
                        <li>Inventory purchases</li>
                        <li>Rent and utilities</li>
                        <li>Vehicle fuel</li>
                        <li>Marketing costs</li>
                        <li>Equipment repairs</li>
                        <li>Office supplies</li>
                    </ul>

                    <hr>

                    <div class="alert alert-info small">
                        <strong>Tip:</strong> Be descriptive in your description field - it will help you track your business better!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection