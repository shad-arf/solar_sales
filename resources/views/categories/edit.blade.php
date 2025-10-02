@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Category</h2>
    <div>
        <a href="{{ route('categories.show', $category) }}" class="btn btn-info me-2">
            <i class="bi bi-eye"></i> View Details
        </a>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Categories
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Category Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Category Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $category->name) }}" 
                                   required
                                   placeholder="Enter category name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category Type -->
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Category Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="income" {{ old('type', $category->type) == 'income' ? 'selected' : '' }}>
                                    Income Category
                                </option>
                                <option value="expense" {{ old('type', $category->type) == 'expense' ? 'selected' : '' }}>
                                    Expense Category
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Enter category description (optional)">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Provide a brief description of what this category covers.</div>
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Category</strong>
                                <div class="form-text">Active categories will appear in dropdown lists when creating income/expense entries.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Category
                        </button>
                        <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-info">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Category Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Created:</strong> {{ $category->created_at->format('M d, Y \a\t g:i A') }}
                </div>
                <div class="mb-2">
                    <strong>Last Updated:</strong> {{ $category->updated_at->format('M d, Y \a\t g:i A') }}
                </div>
                <div class="mb-2">
                    <strong>Current Status:</strong> 
                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                
                <hr>
                
                <h6>Usage Warning:</h6>
                <div class="small text-muted">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Changing the category type may affect existing records that use this category.
                </div>
            </div>
        </div>

        @if($category->type === 'income')
            @php
                $relatedCount = $category->incomes()->count();
            @endphp
        @else
            @php
                $relatedCount = $category->expenses()->count();
            @endphp
        @endif

        @if($relatedCount > 0)
        <div class="card border-warning mt-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Usage Information</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">This category is currently being used by:</p>
                <div class="h4 text-warning">{{ $relatedCount }}</div>
                <div class="small">{{ $category->type === 'income' ? 'Income' : 'Expense' }} record(s)</div>
                
                <hr>
                
                <div class="small text-muted">
                    <i class="bi bi-info-circle"></i>
                    Be careful when modifying categories that are actively in use.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const nameInput = document.getElementById('name');
    
    // Update placeholder based on type selection
    typeSelect.addEventListener('change', function() {
        if (this.value === 'income') {
            nameInput.placeholder = 'e.g., Solar Panel Sales, Installation Services';
        } else if (this.value === 'expense') {
            nameInput.placeholder = 'e.g., Equipment Purchase, Vehicle Maintenance';
        } else {
            nameInput.placeholder = 'Enter category name';
        }
    });
});
</script>
@endsection