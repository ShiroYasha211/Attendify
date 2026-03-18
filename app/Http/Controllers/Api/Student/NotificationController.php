<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentNotification;
use Illuminate\Support\Facades\Auth;

use App\Models\PollOption;
use App\Models\PollVote;

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

        // Transform to include poll data if any
        $notifications->getCollection()->transform(function ($notification) use ($user) {
            if ($notification->type === 'poll') {
                $options = PollOption::where('batch_id', $notification->batch_id)->get();
                $votedOption = PollVote::where('batch_id', $notification->batch_id)
                    ->where('student_id', $user->id)
                    ->first();
                
                $notification->poll_data = [
                    'options' => $options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'text' => $opt->option_text,
                            'votes_count' => PollVote::where('poll_option_id', $opt->id)->count(),
                        ];
                    }),
                    'has_voted' => $votedOption ? true : false,
                    'voted_option_id' => $votedOption ? $votedOption->poll_option_id : null,
                ];
            }
            return $notification;
        });

        return $this->paginated($notifications, 'تم جلب الإشعارات بنجاح');
    }

    /**
     * Cast a vote on a poll.
     */
    public function vote(Request $request, $notificationId)
    {
        $user = Auth::user();
        $notification = StudentNotification::where('user_id', $user->id)
            ->findOrFail($notificationId);

        if ($notification->type !== 'poll') {
            return $this->error('هذا الإشعار ليس استفتاءً', 400);
        }

        $request->validate([
            'poll_option_id' => 'required|exists:poll_options,id',
        ]);

        $option = PollOption::findOrFail($request->poll_option_id);
        if ($option->batch_id !== $notification->batch_id) {
            return $this->error('خيار التصويت غير صالح لهذا الاستفتاء', 400);
        }

        // Check if already voted
        $existingVote = PollVote::where('batch_id', $notification->batch_id)
            ->where('student_id', $user->id)
            ->first();

        if ($existingVote) {
            return $this->error('لقد قمت بالتصويت مسبقاً في هذا الاستفتاء', 400);
        }

        PollVote::create([
            'batch_id' => $notification->batch_id,
            'poll_option_id' => $request->poll_option_id,
            'student_id' => $user->id,
        ]);

        // Mark as read automatically when voting
        $notification->markAsRead();

        return $this->success(null, 'تم تسجيل تصويتك بنجاح');
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
