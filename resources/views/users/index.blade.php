@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Users</h2>
    <a href="{{ route('register.form') }}" class="btn btn-success">Add User</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registered</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>{{ $user->created_at->format('Y-m-d') }}</td>
            <td>
                @if($user->deleted_at)
                    <span class="badge bg-danger">Deleted</span>
                @else
                    <span class="badge bg-success">Active</span>
                @endif
            </td>
            <td>



                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete this user?');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>

            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="text-center">No users found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
