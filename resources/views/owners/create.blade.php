@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Add New Owner</h2>
    <a href="{{ route('owners.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Owners
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Owner Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('owners.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <!-- Owner Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
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
                                       value="{{ old('ownership_percentage', 100) }}" 
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
                                   value="{{ old('email') }}" 
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
                                   value="{{ old('phone') }}" 
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
                                  placeholder="Enter complete address">{{ old('address') }}</textarea>
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
                                  placeholder="Additional notes about this owner (optional)">{{ old('notes') }}</textarea>
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
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Owner</strong>
                                <div class="form-text">Active owners can make investments and drawings.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Owner
                        </button>
                        <a href="{{ route('owners.index') }}" class="btn btn-outline-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Owner Guidelines</h6>
            </div>
            <div class="card-body">
                <h6>Ownership Information:</h6>
                <ul class="small">
                    <li>Enter the legal name of the owner/investor</li>
                    <li>Ownership percentage can be decimal (e.g., 33.33%)</li>
                    <li>All percentages should add up to 100% across all owners</li>
                    <li>Email is recommended for notifications</li>
                </ul>
                
                <h6>Investment Tracking:</h6>
                <ul class="small">
                    <li>Track initial capital investments</li>
                    <li>Monitor ongoing investments</li>
                    <li>Record owner drawings/withdrawals</li>
                    <li>Calculate net equity per owner</li>
                </ul>
                
                <h6>Status Management:</h6>
                <ul class="small">
                    <li><strong>Active:</strong> Can make new investments/drawings</li>
                    <li><strong>Inactive:</strong> Historical records only</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection