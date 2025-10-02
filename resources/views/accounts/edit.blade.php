@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Account: {{ $account->name }}</h2>
    <div>
        <a href="{{ route('accounts.show', $account) }}" class="btn btn-info me-2">
            <i class="bi bi-eye"></i> View Account
        </a>
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
                <form action="{{ route('accounts.update', $account) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $account->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter account name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" value="{{ old('code', $account->code) }}" 
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
                                    <option value="{{ $key }}" {{ old('type', $account->type) == $key ? 'selected' : '' }}>
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
                                       id="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
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
                                      placeholder="Optional description for this account">{{ old('description', $account->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('accounts.show', $account) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Information Panel -->
    <div class="col-md-4">
        <!-- Current Account Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Current Account Balance</h6>
            </div>
            <div class="card-body">
                @php
                    $balance = $account->balance;
                    $balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';
                @endphp
                <div class="text-center">
                    <h3 class="{{ $balanceClass }}">
                        ${{ number_format(abs($balance), 2) }}
                        @if($balance < 0) (Dr) @endif
                    </h3>
                    <p class="text-muted mb-0">Current Balance</p>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between">
                        <span>Total Transactions:</span>
                        <span>{{ $account->transactions()->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

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
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 1050;" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection