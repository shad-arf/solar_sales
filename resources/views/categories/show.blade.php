@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Category Details</h2>
    <div>
        <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil-square"></i> Edit Category
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
                <h5 class="mb-0">
                    <i class="bi bi-{{ $category->type === 'income' ? 'cash-stack' : 'receipt' }} me-2"></i>
                    {{ $category->name }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Category Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Name:</td>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Type:</td>
                                <td>
                                    <span class="badge {{ $category->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                        <i class="bi bi-{{ $category->type === 'income' ? 'cash-stack' : 'receipt' }} me-1"></i>
                                        {{ ucfirst($category->type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Status:</td>
                                <td>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        <i class="bi bi-{{ $category->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Created:</td>
                                <td>{{ $category->created_at->format('M d, Y \a\t g:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Last Updated:</td>
                                <td>{{ $category->updated_at->format('M d, Y \a\t g:i A') }}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Description</h6>
                        @if($category->description)
                            <p class="text-muted">{{ $category->description }}</p>
                        @else
                            <p class="text-muted fst-italic">No description provided.</p>
                        @endif
                        
                        <h6>Usage Statistics</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="h3 mb-0 text-primary">{{ $relatedCount }}</div>
                                <small class="text-muted">{{ $category->type === 'income' ? 'Income' : 'Expense' }} Records</small>
                            </div>
                            <div class="text-muted">
                                <i class="bi bi-{{ $category->type === 'income' ? 'cash-stack' : 'receipt' }} fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Edit Category
                    </a>
                    
                    <form action="{{ route('categories.toggleStatus', $category) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn {{ $category->is_active ? 'btn-warning' : 'btn-success' }}">
                            <i class="bi bi-{{ $category->is_active ? 'pause' : 'play' }}"></i>
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    
                    @if($relatedCount == 0)
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete Category
                            </button>
                        </form>
                    @else
                        <button class="btn btn-outline-danger" disabled title="Cannot delete category with existing records">
                            <i class="bi bi-trash"></i> Delete Category
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($relatedCount > 0)
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Usage Information</h6>
            </div>
            <div class="card-body">
                <p>This category is currently being used by:</p>
                <div class="text-center">
                    <div class="h2 text-info">{{ $relatedCount }}</div>
                    <div class="text-muted">{{ $category->type === 'income' ? 'Income' : 'Expense' }} record(s)</div>
                </div>
                
                <hr>
                
                <div class="small text-muted">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Categories with existing records cannot be deleted, but can be deactivated to prevent future use.
                </div>
            </div>
        </div>
        @else
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Available Actions</h6>
            </div>
            <div class="card-body">
                <p>This category is not currently in use.</p>
                
                <div class="d-grid gap-2">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Since this category has no associated records, you can safely edit or delete it.
                    </small>
                </div>
            </div>
        </div>
        @endif

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Links</h6>
            </div>
            <div class="card-body">
                @if($category->type === 'income')
                    <a href="{{ route('income.create') }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Add New Income
                    </a>
                    <a href="{{ route('income.index') }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-list"></i> View All Income
                    </a>
                @else
                    <a href="{{ route('expenses.create') }}" class="btn btn-outline-danger btn-sm w-100 mb-2">
                        <i class="bi bi-plus-circle"></i> Add New Expense
                    </a>
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-list"></i> View All Expenses
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection