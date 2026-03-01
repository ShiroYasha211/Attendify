<?php

namespace App\Http\Controllers\Api\Doctor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorConversation;
use App\Models\DoctorMessage;
use App\Models\User;
use App\Models\Academic\Subject;

class MessageController extends DoctorApiController
{
    /** GET /api/doctor/messages */
    public function index()
    {
        $conversations = DoctorConversation::where('doctor_id', Auth::id())
            ->with(['delegate:id,name', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'delegate' => $c->delegate ? ['id' => $c->delegate->id, 'name' => $c->delegate->name] : null,
                'last_message' => $c->lastMessage?->body,
                'last_message_at' => $c->last_message_at,
                'unread_count' => $c->messages()->where('sender_id', '!=', Auth::id())->whereNull('read_at')->count(),
            ]);

        return $this->success($conversations);
    }

    /** GET /api/doctor/messages/{conversation} */
    public function show($id)
    {
        $conversation = DoctorConversation::where('doctor_id', Auth::id())
            ->with('delegate:id,name')
            ->findOrFail($id);

        $conversation->markAsReadFor(Auth::id());

        $messages = $conversation->messages()->with('sender:id,name')->get()->map(fn($m) => [
            'id' => $m->id,
            'body' => $m->body,
            'sender' => $m->sender ? ['id' => $m->sender->id, 'name' => $m->sender->name] : null,
            'is_mine' => $m->sender_id === Auth::id(),
            'created_at' => $m->created_at,
        ]);

        return $this->success([
            'conversation' => [
                'id' => $conversation->id,
                'delegate' => $conversation->delegate ? ['id' => $conversation->delegate->id, 'name' => $conversation->delegate->name] : null,
            ],
            'messages' => $messages,
        ]);
    }

    /** POST /api/doctor/messages */
    public function store(Request $request)
    {
        $request->validate(['delegate_id' => 'required|exists:users,id']);

        $conversation = DoctorConversation::getOrCreate($request->delegate_id, Auth::id());

        return $this->success(['conversation_id' => $conversation->id], 'تم إنشاء المحادثة.', 201);
    }

    /** POST /api/doctor/messages/{conversation}/send */
    public function send(Request $request, $id)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $conversation = DoctorConversation::where('doctor_id', Auth::id())->findOrFail($id);

        $message = DoctorMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => $request->body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $this->success([
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at,
        ], 'تم إرسال الرسالة.');
    }
}
