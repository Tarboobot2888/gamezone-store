<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        $user = auth()->user()->load(['roles', 'permissions']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'country' => 'nullable|string|max:100',
            'language' => 'nullable|string|in:en,ar',
            'timezone' => 'nullable|string|max:50',
        ]);

        $user = auth()->user();
        $user->update($request->only([
            'name', 'email', 'phone', 'date_of_birth', 
            'country', 'language', 'timezone'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'avatar_url' => Storage::url($path)
            ]
        ]);
    }

    /**
     * Get user statistics
     */
    public function stats(): JsonResponse
    {
        $user = auth()->user();
        
        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
            'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
            'total_spent' => $user->orders()->where('status', 'completed')->sum('total_amount'),
            'total_saved' => $user->orders()->where('status', 'completed')->sum('discount_amount'),
            'support_tickets' => $user->supportTickets()->count(),
            'open_tickets' => $user->supportTickets()->where('status', 'open')->count(),
            'member_since' => $user->created_at->format('Y-m-d'),
            'last_order' => $user->orders()->latest()->first()?->created_at?->format('Y-m-d'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get user notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        
        $notifications = auth()->user()
            ->notifications()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notifications as read
     */
    public function markNotificationsRead(Request $request): JsonResponse
    {
        $notificationIds = $request->get('notification_ids', []);
        
        if (empty($notificationIds)) {
            // Mark all as read
            auth()->user()->unreadNotifications->markAsRead();
        } else {
            // Mark specific notifications as read
            auth()->user()
                ->unreadNotifications()
                ->whereIn('id', $notificationIds)
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read'
        ]);
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'order_updates' => 'boolean',
            'promotional_emails' => 'boolean',
            'security_alerts' => 'boolean',
        ]);

        $user = auth()->user();
        
        $settings = $user->notification_settings ?? [];
        $settings = array_merge($settings, $request->only([
            'email_notifications', 'push_notifications', 'sms_notifications',
            'order_updates', 'promotional_emails', 'security_alerts'
        ]));
        
        $user->update(['notification_settings' => $settings]);

        return response()->json([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => $settings
        ]);
    }

    /**
     * Store FCM token for push notifications
     */
    public function storeFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = auth()->user();
        $user->update(['fcm_token' => $request->token]);

        return response()->json([
            'success' => true,
            'message' => 'FCM token stored successfully'
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount(): JsonResponse
    {
        $count = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect'
            ], 400);
        }

        // Check for pending orders
        $pendingOrders = $user->orders()->whereIn('status', ['pending', 'processing'])->count();
        if ($pendingOrders > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with pending orders'
            ], 400);
        }

        // Anonymize user data instead of hard delete
        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted_' . $user->id . '@example.com',
            'phone' => null,
            'avatar' => null,
            'deleted_at' => now(),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }
}
