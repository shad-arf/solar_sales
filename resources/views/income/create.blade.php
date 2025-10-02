@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Add New Income</h2>
        <a href="{{ route('income.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Income
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Income Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('income.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                           id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                           id="reference_number" name="reference_number" value="{{ old('reference_number') }}" 
                                           placeholder="Invoice #, Receipt #, etc.">
                                    @error('reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required 
                                      placeholder="Describe what this income is for...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                    @if($categories->count() > 0)
                                        <select class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i> 
                                            Don't see the category you need? 
                                            <a href="{{ route('categories.create') }}" target="_blank">Add a new category</a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <h6><i class="bi bi-exclamation-triangle me-2"></i>No Income Categories Available</h6>
                                            <p class="mb-2">You need to create at least one income category before you can add income records.</p>
                                            <a href="{{ route('categories.create') }}?type=income" class="btn btn-warning btn-sm">
                                                <i class="bi bi-plus-circle me-1"></i>Add First Income Category
                                            </a>
                                        </div>
                                    @endif
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
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Save Income
                            </button>
                            <a href="{{ route('income.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Income Categories</h5>
                </div>
                <div class="card-body">
                    @if($categories->count() > 0)
                        @foreach($categories as $category)
                            <div class="mb-2">
                                <span class="badge bg-success">{{ $category->name }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted">
                            <i class="bi bi-exclamation-circle fs-1 mb-2"></i>
                            <p>No income categories available.</p>
                            <a href="{{ route('categories.create') }}?type=income" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle me-1"></i>Add First Category
                            </a>
                        </div>
                    @endif

                    <hr>

                    <div class="alert alert-info small">
                        <strong>Tip:</strong> Be specific in your description to help track your business income better!
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Income</h5>
                </div>
                <div class="card-body">
                    <div class="text-center text-success">
                        <h4>${{ number_format(\App\Models\Income::getTotalThisMonth(), 2) }}</h4>
                        <small class="text-muted">This Month's Income</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection