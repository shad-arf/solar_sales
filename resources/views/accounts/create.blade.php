@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Account</h2>
    <div>
        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Accounts
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('accounts.store') }}" method="POST">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter account name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ old('code') }}" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   placeholder="e.g., 1000, 2000, 3000" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Account Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Account Details</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select account type</option>
                                @foreach(\App\Models\Account::TYPES as $key => $value)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Account
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Additional Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Optional description for this account">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Information Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Account Types Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-success">Assets</h6>
                    <p class="small text-muted">Resources owned by the business (e.g., Cash, Equipment, Inventory)</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-danger">Liabilities</h6>
                    <p class="small text-muted">Debts owed by the business (e.g., Loans, Accounts Payable)</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-primary">Equity</h6>
                    <p class="small text-muted">Owner's interest in the business (e.g., Capital, Retained Earnings)</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-info">Revenue</h6>
                    <p class="small text-muted">Income earned by the business (e.g., Sales, Service Revenue)</p>
                </div>
                <div class="mb-0">
                    <h6 class="text-warning">Expenses</h6>
                    <p class="small text-muted">Costs incurred by the business (e.g., Rent, Utilities, Supplies)</p>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Account Code Guidelines</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li><strong>1000-1999:</strong> Assets</li>
                    <li><strong>2000-2999:</strong> Liabilities</li>
                    <li><strong>3000-3999:</strong> Equity</li>
                    <li><strong>4000-4999:</strong> Revenue</li>
                    <li><strong>5000-5999:</strong> Expenses</li>
                </ul>
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