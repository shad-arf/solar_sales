
<!-- @section('content') -->
<h2>Add User</h2>
<form action="{{ route('users.store') }}" method="POST" class="mt-3">
    @csrf

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="password_confirmation" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" required>
            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
        </select>
    </div>

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
</form>
