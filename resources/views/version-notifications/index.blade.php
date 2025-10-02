@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">What's New</h2>
    <div>
        <small class="text-muted">Stay updated with the latest features and improvements</small>
    </div>
</div>

<div class="row">
    @forelse($notifications as $notification)
    <div class="col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-rocket-takeoff me-2"></i>
                        {{ $notification->title }}
                    </h5>
                    <div>
                        <span class="badge bg-white text-primary">v{{ $notification->version }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">{{ $notification->description }}</p>
                
                @if($notification->features && count($notification->features) > 0)
                    <h6 class="text-primary mb-3"><i class="bi bi-stars me-2"></i>New Features:</h6>
                    <ul class="list-unstyled">
                        @foreach($notification->features as $feature)
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Released: {{ $notification->release_date->format('M d, Y') }}
                    </small>
                    <span class="badge {{ $notification->priority_badge }}">
                        {{ $notification->priority_display }}
                    </span>
                </div>
                
                @php
                    $userNotification = $notification->userNotifications->where('user_id', auth()->id())->first();
                @endphp
                
                @if($userNotification && $userNotification->viewed_at)
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Viewed on {{ $userNotification->viewed_at->format('M d, Y \a\t g:i A') }}
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No Version Notifications</h5>
                <p class="text-muted">There are no version notifications available at this time.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $notifications->links('pagination.bootstrap-5') }}
</div>
@endif
@endsection