@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Version Notifications Management</h2>
    <div>
        <a href="{{ route('version-notifications.create') }}" class="btn btn-success me-2">
            <i class="bi bi-plus-circle"></i> Create New Notification
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Notifications</h5>
                        <h3>{{ $stats['total_notifications'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-bell fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Active Notifications</h5>
                        <h3>{{ $stats['active_notifications'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total Views</h5>
                        <h3>{{ $stats['total_user_views'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-eye fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Pending Views</h5>
                        <h3>{{ $stats['pending_views'] }}</h3>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Version</th>
                    <th>Title</th>
                    <th>Features</th>
                    <th class="text-center">Priority</th>
                    <th class="text-center">Status</th>
                    <th>Release Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr>
                    <td>
                        <code class="bg-light px-2 py-1 rounded">v{{ $notification->version }}</code>
                    </td>
                    <td>
                        <strong>{{ $notification->title }}</strong>
                        <br><small class="text-muted">{{ Str::limit($notification->description, 60) }}</small>
                    </td>
                    <td>
                        @if($notification->features && count($notification->features) > 0)
                            <span class="badge bg-info">{{ count($notification->features) }} features</span>
                        @else
                            <span class="text-muted">No features</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $notification->priority_badge }}">
                            {{ $notification->priority_display }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $notification->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $notification->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <small>{{ $notification->release_date->format('M d, Y') }}</small>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('version-notifications.show', $notification) }}"
                               class="btn btn-sm btn-outline-info"
                               title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('version-notifications.edit', $notification) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Edit Notification">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('version-notifications.toggle-status', $notification) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $notification->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                        title="{{ $notification->is_active ? 'Deactivate' : 'Activate' }} Notification">
                                    <i class="bi bi-{{ $notification->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('version-notifications.destroy', $notification) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete version notification {{ $notification->version }}? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete Notification">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No version notifications found.
                        <br><a href="{{ route('version-notifications.create') }}" class="btn btn-sm btn-success mt-2">Create First Notification</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="text-muted">
        Showing {{ $notifications->firstItem() ?? 0 }} to {{ $notifications->lastItem() ?? 0 }} of {{ $notifications->total() }} results
    </div>
    <div>
        {{ $notifications->links('pagination.bootstrap-5') }}
    </div>
</div>
@endsection