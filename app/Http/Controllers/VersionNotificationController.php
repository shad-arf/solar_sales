<?php

namespace App\Http\Controllers;

use App\Models\VersionNotification;
use App\Models\UserVersionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VersionNotificationController extends Controller
{
    public function index()
    {
        $notifications = VersionNotification::with(['userNotifications' => function($query) {
            $query->where('user_id', Auth::id());
        }])
        ->active()
        ->latest()
        ->paginate(10);

        return view('version-notifications.index', compact('notifications'));
    }

    public function checkPendingNotifications()
    {
        $user = Auth::user();
        
        // Get all active notifications
        $activeNotifications = VersionNotification::active()->latest()->get();
        
        $pendingNotifications = [];
        
        foreach ($activeNotifications as $notification) {
            // Check if user has already seen this notification
            $userNotification = UserVersionNotification::where('user_id', $user->id)
                ->where('version_notification_id', $notification->id)
                ->first();
                
            if (!$userNotification) {
                // Create a record that this notification exists for this user
                UserVersionNotification::create([
                    'user_id' => $user->id,
                    'version_notification_id' => $notification->id
                ]);
                
                $pendingNotifications[] = $notification;
            } elseif (!$userNotification->viewed_at) {
                // User hasn't viewed this notification yet
                $pendingNotifications[] = $notification;
            }
        }
        
        return response()->json([
            'has_pending' => count($pendingNotifications) > 0,
            'notifications' => $pendingNotifications
        ]);
    }

    public function markAsViewed(Request $request)
    {
        $user = Auth::user();
        $notificationId = $request->get('notification_id');
        
        $userNotification = UserVersionNotification::where('user_id', $user->id)
            ->where('version_notification_id', $notificationId)
            ->first();
            
        if ($userNotification) {
            $userNotification->markAsViewed();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as viewed'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    public function dismiss(Request $request)
    {
        $user = Auth::user();
        $notificationId = $request->get('notification_id');
        
        $userNotification = UserVersionNotification::where('user_id', $user->id)
            ->where('version_notification_id', $notificationId)
            ->first();
            
        if ($userNotification) {
            $userNotification->markAsDismissed();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification dismissed'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    public function dismissAll(Request $request)
    {
        $user = Auth::user();
        
        UserVersionNotification::where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->update([
                'viewed_at' => now(),
                'dismissed_at' => now()
            ]);
            
        return response()->json([
            'success' => true,
            'message' => 'All notifications dismissed'
        ]);
    }

    // Admin methods for managing version notifications
    public function admin()
    {
        $notifications = VersionNotification::latest()->paginate(15);
        
        $stats = [
            'total_notifications' => VersionNotification::count(),
            'active_notifications' => VersionNotification::active()->count(),
            'total_user_views' => UserVersionNotification::viewed()->count(),
            'pending_views' => UserVersionNotification::pending()->count()
        ];
        
        return view('version-notifications.admin.index', compact('notifications', 'stats'));
    }

    public function create()
    {
        return view('version-notifications.admin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'version' => 'required|string|unique:version_notifications,version',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'release_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        VersionNotification::create($validated);

        return redirect()->route('version-notifications.admin')
                        ->with('success', 'Version notification created successfully.');
    }

    public function show(VersionNotification $versionNotification)
    {
        $userViews = UserVersionNotification::where('version_notification_id', $versionNotification->id)
            ->with('user')
            ->latest()
            ->paginate(20);
            
        return view('version-notifications.admin.show', compact('versionNotification', 'userViews'));
    }

    public function edit(VersionNotification $versionNotification)
    {
        return view('version-notifications.admin.edit', compact('versionNotification'));
    }

    public function update(Request $request, VersionNotification $versionNotification)
    {
        $validated = $request->validate([
            'version' => 'required|string|unique:version_notifications,version,' . $versionNotification->id,
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string',
            'release_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $versionNotification->update($validated);

        return redirect()->route('version-notifications.admin')
                        ->with('success', 'Version notification updated successfully.');
    }

    public function destroy(VersionNotification $versionNotification)
    {
        $versionNotification->delete();

        return redirect()->route('version-notifications.admin')
                        ->with('success', 'Version notification deleted successfully.');
    }

    public function toggleStatus(VersionNotification $versionNotification)
    {
        $versionNotification->update(['is_active' => !$versionNotification->is_active]);
        
        $status = $versionNotification->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Version notification {$status} successfully.");
    }
}