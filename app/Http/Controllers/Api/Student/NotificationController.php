<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends StudentApiController
{
    /**
     * Display a listing of the student's notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = StudentNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->paginated($notifications, 'تم جلب الإشعارات بنجاح');
    }

    /**
     * Mark the specified notification as read.
     */
    public function markAsRead($id)
    {
        $notification = StudentNotification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->markAsRead();

        return $this->success(null, 'تم تحديد الإشعار كمقروء');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        StudentNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'تم تحديد جميع الإشعارات كمقروءة');
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $count = StudentNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return $this->success(['unread_count' => $count], 'تم جلب عدد الإشعارات غير المقروءة');
    }
}
