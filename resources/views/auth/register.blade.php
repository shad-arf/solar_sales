@extends('layouts.admin')

@section('content')
<h2>Add User</h2>

<form action="{{ route('users.store') }}" method="POST" class="mt-3">
    @csrf

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password"
                   class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation"
                   class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                <option value="">Select Role</option>
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
