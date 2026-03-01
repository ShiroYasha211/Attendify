<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Message;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;

class MessageController extends DelegateApiController
{
    /**
     * Display a listing of conversations (messages grouped by user).
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // Get the latest message for each conversation involving the delegate
        // Uses Eloquent subquery scope or simple fetching and grouping
        $messages = Message::where('sender_id', $delegate->id)
            ->orWhere('receiver_id', $delegate->id)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($delegate) {
                return $item->sender_id === $delegate->id ? $item->receiver_id : $item->sender_id;
            })->values();

        return $this->success($messages, 'تم جلب المحادثات بنجاح');
    }

    /**
     * Display the specified conversation with a specific user.
     */
    public function show(Request $request, string $userId)
    {
        $delegate = $request->user();

        $messages = Message::where(function ($query) use ($delegate, $userId) {
            $query->where('sender_id', $delegate->id)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($delegate, $userId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $delegate->id);
            })
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $delegate->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->success($messages, 'تم جلب رسائل المحادثة بنجاح');
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        // Validate receiver is a student in the batch
        $receiver = User::where('id', $request->receiver_id)
            ->where('role', UserRole::STUDENT)
            ->where('major_id', $delegate->major_id)
            ->where('level_id', $delegate->level_id)
            ->first();

        if (!$receiver) {
            return $this->error('لا يمكنك مراسلة هذا المستخدم', 403);
        }

        $message = Message::create([
            'sender_id' => $delegate->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
            'is_read' => false,
        ]);

        return $this->success($message->load(['sender:id,name,avatar', 'receiver:id,name,avatar']), 'تم إرسال الرسالة بنجاح', 201);
    }
}
