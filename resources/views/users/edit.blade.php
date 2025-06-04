@extends('layouts.admin')

@section('content')
<h2>Edit User</h2>

<form action="{{ route('users.update', $user->id) }}" method="POST" class="mt-3">
    @csrf
    @method('PATCH') {{-- Changed from PUT to PATCH --}}

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">New Password <small>(Leave blank to keep current)</small></label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" required>
            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
            <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Manager</option>
        </select>
    </div>

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
