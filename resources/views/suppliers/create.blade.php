@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Supplier</h2>
    <div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Suppliers
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Supplier Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="Enter supplier name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}" 
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
                            <input type="email" name="email" value="{{ old('email') }}" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   placeholder="supplier@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" 
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
                                      placeholder="Street address">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" 
                                   class="form-control @error('city') is-invalid @enderror" 
                                   placeholder="City">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State/Province</label>
                            <input type="text" name="state" value="{{ old('state') }}" 
                                   class="form-control @error('state') is-invalid @enderror" 
                                   placeholder="State/Province">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}" 
                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                   placeholder="Postal Code">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" value="{{ old('country') }}" 
                                   class="form-control @error('country') is-invalid @enderror" 
                                   placeholder="Country">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                      placeholder="Additional notes about this supplier...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg"></i> Create Supplier
                        </button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">After creating this supplier, you can:</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-cart-plus text-primary me-2"></i>Create purchase orders</li>
                    <li><i class="bi bi-clock-history text-info me-2"></i>View purchase history</li>
                    <li><i class="bi bi-pencil text-warning me-2"></i>Edit supplier details</li>
                    <li><i class="bi bi-graph-up text-success me-2"></i>Track spending analytics</li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>
                        <small>Add complete contact information to ensure smooth communication</small>
                    </li>
                    <li class="mb-2"><i class="bi bi-shield-check text-success me-2"></i>
                        <small>Verify supplier details before creating purchase orders</small>
                    </li>
                    <li><i class="bi bi-bookmark text-info me-2"></i>
                        <small>Use notes field for payment terms, delivery preferences, etc.</small>
                    </li>
                </ul>
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
</style>
@endsection