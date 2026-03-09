<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;

class MessageController extends DelegateApiController
{
    /**
     * Display a listing of conversations.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        $conversations = Conversation::where('delegate_id', $delegate->id)
            ->with(['student:id,name,avatar', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        return $this->success($conversations, 'تم جلب المحادثات بنجاح');
    }

    /**
     * Display the specified conversation with messages.
     */
    public function show(Request $request, string $id)
    {
        $delegate = $request->user();

        $conversation = Conversation::where('delegate_id', $delegate->id)
            ->with('student:id,name,avatar')
            ->find($id);

        if (!$conversation) {
            // Check if $id is actually a userId (fallback/legacy support)
            $conversation = Conversation::where('delegate_id', $delegate->id)
                ->where('student_id', $id)
                ->first();
            
            if (!$conversation) {
                return $this->error('المحادثة غير موجودة', 404);
            }
        }

        // Mark messages as read
        $conversation->markAsReadFor($delegate->id);

        $messages = $conversation->messages()->with('sender:id,name,avatar')->get();

        return $this->success([
            'conversation' => $conversation,
            'messages' => $messages
        ], 'تم جلب رسائل المحادثة بنجاح');
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $delegate = $request->user();

        $validator = Validator::make($request->all(), [
            'student_id' => 'required_without:conversation_id|exists:users,id',
            'conversation_id' => 'required_without:student_id|exists:conversations,id',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('بيانات غير صالحة', 422, $validator->errors());
        }

        if ($request->conversation_id) {
            $conversation = Conversation::where('delegate_id', $delegate->id)->find($request->conversation_id);
        } else {
            // Start or continue conversation
            $student = User::where('id', $request->student_id)
                ->whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE])
                ->where('major_id', $delegate->major_id)
                ->where('level_id', $delegate->level_id)
                ->first();

            if (!$student) {
                return $this->error('لا يمكنك مراسلة هذا المستخدم', 403);
            }

            $conversation = Conversation::getOrCreate($student->id, $delegate->id);
        }

        if (!$conversation) {
            return $this->error('المحادثة غير موجودة', 404);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $delegate->id,
            'receiver_id' => $conversation->student_id,
            'body' => $request->content,
            'type' => 'delegate_to_student',
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $this->success($message->load('sender:id,name,avatar'), 'تم إرسال الرسالة بنجاح', 201);
    }
}
