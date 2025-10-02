@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Create Version Notification</h2>
    <a href="{{ route('version-notifications.admin') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Notifications
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notification Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('version-notifications.store') }}" method="POST" id="notificationForm">
                    @csrf
                    
                    <div class="row">
                        <!-- Version -->
                        <div class="col-md-6 mb-3">
                            <label for="version" class="form-label">Version <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('version') is-invalid @enderror" 
                                   id="version" 
                                   name="version" 
                                   value="{{ old('version') }}" 
                                   required
                                   placeholder="e.g., 1.2.0">
                            @error('version')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                <option value="">Select Priority</option>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               required
                               placeholder="e.g., Enhanced Category Management">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  required
                                  placeholder="Brief description of this version update...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Features -->
                    <div class="mb-3">
                        <label class="form-label">Features <span class="text-danger">*</span></label>
                        <div id="featuresContainer">
                            @if(old('features'))
                                @foreach(old('features') as $index => $feature)
                                    <div class="input-group mb-2 feature-input">
                                        <input type="text" 
                                               class="form-control @error('features.'.$index) is-invalid @enderror" 
                                               name="features[]" 
                                               value="{{ $feature }}" 
                                               placeholder="Describe a new feature..."
                                               required>
                                        <button type="button" class="btn btn-outline-danger remove-feature">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @error('features.'.$index)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2 feature-input">
                                    <input type="text" 
                                           class="form-control" 
                                           name="features[]" 
                                           placeholder="Describe a new feature..."
                                           required>
                                    <button type="button" class="btn btn-outline-danger remove-feature">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addFeature">
                            <i class="bi bi-plus-circle"></i> Add Feature
                        </button>
                        @error('features')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Release Date -->
                    <div class="mb-3">
                        <label for="release_date" class="form-label">Release Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('release_date') is-invalid @enderror" 
                               id="release_date" 
                               name="release_date" 
                               value="{{ old('release_date', date('Y-m-d')) }}" 
                               required>
                        @error('release_date')
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
                                <strong>Active Notification</strong>
                                <div class="form-text">Active notifications will be shown to users when they log in.</div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Notification
                        </button>
                        <a href="{{ route('version-notifications.admin') }}" class="btn btn-outline-secondary">
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
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Guidelines</h6>
            </div>
            <div class="card-body">
                <h6>Version Naming:</h6>
                <ul class="small">
                    <li>Use semantic versioning (e.g., 1.2.0)</li>
                    <li>Major.Minor.Patch format</li>
                    <li>Each version should be unique</li>
                </ul>
                
                <h6>Priority Levels:</h6>
                <ul class="small">
                    <li><strong>High:</strong> Major features or critical updates</li>
                    <li><strong>Medium:</strong> Notable improvements</li>
                    <li><strong>Low:</strong> Minor fixes or enhancements</li>
                </ul>
                
                <h6>Features:</h6>
                <ul class="small">
                    <li>List key new features or improvements</li>
                    <li>Be concise but descriptive</li>
                    <li>Focus on user benefits</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addFeatureBtn = document.getElementById('addFeature');
    const featuresContainer = document.getElementById('featuresContainer');

    // Add new feature input
    addFeatureBtn.addEventListener('click', function() {
        const featureHtml = `
            <div class="input-group mb-2 feature-input">
                <input type="text" 
                       class="form-control" 
                       name="features[]" 
                       placeholder="Describe a new feature..."
                       required>
                <button type="button" class="btn btn-outline-danger remove-feature">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        featuresContainer.insertAdjacentHTML('beforeend', featureHtml);
    });

    // Remove feature input
    featuresContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            const featureInputs = featuresContainer.querySelectorAll('.feature-input');
            if (featureInputs.length > 1) {
                e.target.closest('.feature-input').remove();
            } else {
                alert('At least one feature is required.');
            }
        }
    });
});
</script>
@endsection