@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Edit Owner</h2>
    <div>
        <a href="{{ route('owners.show', $owner) }}" class="btn btn-outline-info me-2">
            <i class="bi bi-eye"></i> View Details
        </a>
        <a href="{{ route('owners.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Owners
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Owner Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('owners.update', $owner) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Owner Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $owner->name) }}" 
                                   required
                                   placeholder="Enter owner's full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ownership Percentage -->
                        <div class="col-md-6 mb-3">
                            <label for="ownership_percentage" class="form-label">Ownership Percentage <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control @error('ownership_percentage') is-invalid @enderror" 
                                       id="ownership_percentage" 
                                       name="ownership_percentage" 
                                       value="{{ old('ownership_percentage', $owner->ownership_percentage) }}" 
                                       required
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       placeholder="0.00">
                                <span class="input-group-text">%</span>
                                @error('ownership_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $owner->email) }}" 
                                   placeholder="owner@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $owner->phone) }}" 
                                   placeholder="+1234567890">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3"
                                  placeholder="Enter complete address">{{ old('address', $owner->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="Additional notes about this owner (optional)">{{ old('notes', $owner->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $owner->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Owner</strong>
                                <div class="form-text">Active owners can make investments and drawings.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Update Owner
                        </button>
                        <a href="{{ route('owners.show', $owner) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Current Information -->
        <div class="card mb-4 bg-light">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Current Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Current Name:</small>
                    <div><strong>{{ $owner->name }}</strong></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Current Ownership:</small>
                    <div><span class="badge bg-info">{{ $owner->ownership_display }}</span></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Current Status:</small>
                    <div>
                        <span class="badge {{ $owner->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $owner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Member Since:</small>
                    <div>{{ $owner->created_at->format('M d, Y') }}</div>
                </div>
                <div class="mb-0">
                    <small class="text-muted">Total Transactions:</small>
                    <div><strong>{{ $owner->ownerEquities()->count() }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Guidelines -->
        <div class="card bg-light">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Edit Guidelines</h6>
            </div>
            <div class="card-body">
                <h6>Important Notes:</h6>
                <ul class="small">
                    <li>Changing ownership percentage affects profit/loss distribution</li>
                    <li>All percentages should add up to 100% across all owners</li>
                    <li>Deactivating an owner preserves transaction history</li>
                    <li>Email changes will affect notification delivery</li>
                </ul>
                
                <h6>Before Editing:</h6>
                <ul class="small">
                    <li>Ensure ownership percentages remain balanced</li>
                    <li>Consider impact on existing equity calculations</li>
                    <li>Verify contact information accuracy</li>
                </ul>
                
                @if($owner->ownerEquities()->count() > 0)
                    <div class="alert alert-warning small mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Warning:</strong> This owner has existing transactions. Changes to ownership percentage will affect future profit distributions but not historical records.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection