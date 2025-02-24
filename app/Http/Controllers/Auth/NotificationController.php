<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\NewProviderNotification;
use App\Notifications\NewUserNotification;
use App\Notifications\WithdrawMoneyNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    //get notification for super admin
    public function getnotification()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
        }

        if ($user->role !== 'super_admin') {
            return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
        }

        // Count users and providers
        $totalUsers     = User::where('role', 'user')->count();
        $totalProviders = User::where('role', 'provider')->count();

        // Get user notifications
        $userNotifications = $user->notifications()
            ->where('type', NewUserNotification::class)
            ->paginate(10);

        // Get provider notifications
        $providerNotifications = $user->notifications()
            ->where('type', NewProviderNotification::class)
            ->paginate(10);

        // Count unread notifications
        $unreadUserCount = $user->unreadNotifications()
            ->where('type', NewUserNotification::class)
            ->count();

        $unreadProviderCount = $user->unreadNotifications()
            ->where('type', NewProviderNotification::class)
            ->count();

        return response()->json([
            'status'                        => 'success',
            'total_users'                   => $totalUsers,     // Total users count
            'unread_user_notifications'     => $unreadUserCount,
            'user_notifications'            => $userNotifications->items(),
            'total_providers'               => $totalProviders, // Total providers count
            'unread_provider_notifications' => $unreadProviderCount,
            'provider_notifications'        => $providerNotifications->items(),
        ], 200);
    }
    //read notification
    public function readNotification($notificationId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
        }

        if ($user->role !== 'super_admin') {
            return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 401);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.'], 200);
    }
    //read all notification
    public function readAllNotification()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
        }

        if ($user->role !== 'super_admin') {
            return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
        }

        $notifications = $user->unreadNotifications;

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No unread notifications found.'], 422);
        }

        $notifications->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ], 200);
    }
    //notify for withdraw money request
    public function WithdrawalNotify()
    {
        $admin = Auth::user();

        if (!$admin || $admin->role !== 'super_admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $notifications = $admin->notifications()
            ->where('type', WithdrawMoneyNotification::class)->paginate(10);

        return response()->json([
            'status' => true,
            'notifications' => $notifications->items(),
        ]);
    }

    //get notification for provider
    public function notification()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
        }

        if ($user->role !== 'provider') {
            return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
        }

        $notifications = $user->notifications()
            ->where('type', NewOrderNotification::class)
            ->paginate(10);

            if ($notifications->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No Notification found'], 502);
            }

        // $unreadCount = $user->unreadNotifications()
        //     ->where('type', NewOrderNotification::class)
        //     ->count();

        return response()->json([
            'status'               => 'success',
            // 'unread_notifications' => $unreadCount,
            'notifications'        => $notifications,
        ], 200);
    }
     //read notification
     public function markNotification($notificationId)
     {
         $user = Auth::user();

         if (! $user) {
             return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
         }

         if ($user->role !== 'provider') {
             return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
         }

         $notification = $user->notifications()->find($notificationId);

         if (!$notification) {
             return response()->json(['message' => 'Notification not found.'], 401);
         }

         if (!$notification->read_at) {
             $notification->markAsRead();
         }

         return response()->json([
             'status' => 'success',
             'message' => 'Notification marked as read.'], 200);
     }
     //read all notification
     public function markAllNotification()
     {
         $user = Auth::user();

         if (! $user) {
             return response()->json(['status' => false, 'message' => 'Authorization User Not Found'], 401);
         }

         if ($user->role !== 'provider') {
             return response()->json(['status' => false, 'message' => 'Access Denied'], 403);
         }

         $notifications = $user->unreadNotifications;

         if ($notifications->isEmpty()) {
             return response()->json(['message' => 'No unread notifications found.'], 422);
         }

         $notifications->markAsRead();

         return response()->json([
             'status' => 'success',
             'message' => 'All notifications marked as read.',
         ], 200);
     }

}
