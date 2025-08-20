@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Supplier</h2>
    <div>
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Supplier
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Supplier Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter supplier name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" 
                                   class="form-control @error('contact_person') is-invalid @enderror" 
                                   placeholder="Contact person name">
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Contact Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $supplier->email) }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="supplier@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   placeholder="+1 234 567 8900">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Address Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Street Address</label>
                            <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror" 
                                      placeholder="Street address">{{ old('address', $supplier->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" value="{{ old('city', $supplier->city) }}" 
                                   class="form-control @error('city') is-invalid @enderror" 
                                   placeholder="City">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State/Province</label>
                            <input type="text" name="state" value="{{ old('state', $supplier->state) }}" 
                                   class="form-control @error('state') is-invalid @enderror" 
                                   placeholder="State/Province">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $supplier->postal_code) }}" 
                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                   placeholder="Postal Code">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" value="{{ old('country', $supplier->country) }}" 
                                   class="form-control @error('country') is-invalid @enderror" 
                                   placeholder="Country">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Additional Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" 
                                      placeholder="Additional notes about this supplier...">{{ old('notes', $supplier->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Supplier
                        </button>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Current Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <span class="badge {{ $supplier->status === 'active' ? 'bg-success' : 'bg-secondary' }} fs-6">
                        {{ ucfirst($supplier->status) }}
                    </span>
                    <p class="mt-2 mb-0 text-muted">
                        {{ $supplier->status === 'active' ? 'This supplier is currently active and can receive purchase orders.' : 'This supplier is inactive and cannot receive new purchase orders.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Purchase History</h6>
            </div>
            <div class="card-body">
                @if($supplier->purchases()->count() > 0)
                    <p class="mb-2">This supplier has <strong>{{ $supplier->purchases()->count() }} purchase orders</strong>.</p>
                    <a href="{{ route('suppliers.history', $supplier->id) }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="bi bi-clock-history"></i> View Purchase History
                    </a>
                @else
                    <p class="text-muted mb-2">No purchase orders yet.</p>
                    <a href="{{ route('purchases.create') }}?supplier_id={{ $supplier->id }}" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-plus-circle"></i> Create First Purchase
                    </a>
                @endif
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Last Updated</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Created:</strong> {{ $supplier->created_at->format('M d, Y g:i A') }}</p>
                @if($supplier->updated_at != $supplier->created_at)
                    <p class="mb-0"><strong>Last Updated:</strong> {{ $supplier->updated_at->format('M d, Y g:i A') }}</p>
                @else
                    <p class="mb-0 text-muted">Never updated</p>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.border-bottom {
    border-color: #dee2e6 !important;
}

.card-header h6 {
    color: #495057;
}

.form-label {
    font-weight: 500;
}

.text-primary {
    color: #0d6efd !important;
}

.badge.fs-6 {
    font-size: 1rem !important;
    padding: 0.5rem 1rem;
}
</style>
@endsection