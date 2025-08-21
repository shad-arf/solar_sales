@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Expense Details</h2>
        <div class="btn-group">
            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Expense Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Date</label>
                                <p class="fw-bold">{{ $expense->date->format('F d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Reference Number</label>
                                <p class="fw-bold">
                                    @if($expense->reference_number)
                                        <span class="badge bg-secondary fs-6">{{ $expense->reference_number }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Description</label>
                        <p class="fw-bold">{{ $expense->description }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Category</label>
                                <p class="fw-bold">
                                    <span class="badge bg-danger fs-6">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Amount</label>
                                <p class="fw-bold">
                                    <span class="text-danger fs-4">${{ number_format($expense->amount, 2) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Created</label>
                                <p class="fw-bold">{{ $expense->created_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Last Updated</label>
                                <p class="fw-bold">{{ $expense->updated_at->format('F d, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Summary</h5>
                </div>
                <div class="card-body text-center">
                    <div class="text-danger mb-3">
                        <i class="bi bi-receipt fs-1"></i>
                    </div>
                    <h3 class="text-danger">${{ number_format($expense->amount, 2) }}</h3>
                    <p class="text-muted">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</p>
                    <hr>
                    <small class="text-muted">
                        Spent on {{ $expense->date->format('M d, Y') }}
                    </small>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Expense
                        </a>
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this expense record? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Expense
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Summary</h5>
                </div>
                <div class="card-body text-center">
                    <div class="text-danger">
                        <h4>${{ number_format(\App\Models\Expense::getTotalThisMonth(), 2) }}</h4>
                        <small class="text-muted">Total This Month</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection