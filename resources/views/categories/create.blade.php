@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Add New Category</h2>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Category Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Category Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
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
                                <option value="income" {{ old('type', $preselectedType ?? '') == 'income' ? 'selected' : '' }}>
                                    <i class="bi bi-cash-stack"></i> Income Category
                                </option>
                                <option value="expense" {{ old('type', $preselectedType ?? '') == 'expense' ? 'selected' : '' }}>
                                    <i class="bi bi-receipt"></i> Expense Category
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
                                  placeholder="Enter category description (optional)">{{ old('description') }}</textarea>
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
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Category</strong>
                                <div class="form-text">Active categories will appear in dropdown lists when creating income/expense entries.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Category
                        </button>
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Tips</h6>
            </div>
            <div class="card-body">
                <h6>Category Guidelines:</h6>
                <ul class="small">
                    <li><strong>Be Specific:</strong> Use clear, descriptive names</li>
                    <li><strong>Income Categories:</strong> For revenue sources like sales, services, etc.</li>
                    <li><strong>Expense Categories:</strong> For business costs like rent, utilities, supplies</li>
                    <li><strong>Avoid Duplicates:</strong> Check existing categories first</li>
                    <li><strong>Future Planning:</strong> Consider how you'll track this financially</li>
                </ul>
                
                <hr>
                
                <h6>Examples:</h6>
                <div class="small">
                    <strong>Income:</strong>
                    <ul>
                        <li>Solar Panel Sales</li>
                        <li>Installation Services</li>
                        <li>Maintenance Contracts</li>
                    </ul>
                    
                    <strong>Expense:</strong>
                    <ul>
                        <li>Equipment Purchase</li>
                        <li>Vehicle Maintenance</li>
                        <li>Marketing Campaigns</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const nameInput = document.getElementById('name');
    
    // Update icon based on type selection
    typeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value === 'income') {
            nameInput.placeholder = 'e.g., Solar Panel Sales, Installation Services';
        } else if (selectedOption.value === 'expense') {
            nameInput.placeholder = 'e.g., Equipment Purchase, Vehicle Maintenance';
        } else {
            nameInput.placeholder = 'Enter category name';
        }
    });
});
</script>
@endsection