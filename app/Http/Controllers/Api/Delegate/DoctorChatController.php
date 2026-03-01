<?php

namespace App\Http\Controllers\Api\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Delegate\DelegateApiController;
use App\Models\DoctorMessage;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;

class DoctorChatController extends DelegateApiController
{
    /**
     * Display a listing of conversations with doctors.
     */
    public function index(Request $request)
    {
        $delegate = $request->user();

        // Get the latest message for each conversation involving the delegate and a doctor
        $messages = DoctorMessage::where('sender_id', $delegate->id)
            ->orWhere('receiver_id', $delegate->id)
            ->with(['sender:id,name,avatar,role', 'receiver:id,name,avatar,role'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($delegate) {
                return $item->sender_id === $delegate->id ? $item->receiver_id : $item->sender_id;
            })->values();

        return $this->success($messages, 'تم جلب المحادثات مع الدكاترة بنجاح');
    }

    /**
     * Display the specified conversation with a specific doctor.
     */
    public function show(Request $request, string $doctorId)
    {
        $delegate = $request->user();

        $doctor = User::find($doctorId);
        if (!$doctor || $doctor->role !== UserRole::DOCTOR) {
            return $this->error('الدكتور غير موجود', 404);
        }

        $messages = DoctorMessage::where(function ($query) use ($delegate, $doctorId) {
            $query->where('sender_id', $delegate->id)
                ->where('receiver_id', $doctorId);
        })
            ->orWhere(function ($query) use ($delegate, $doctorId) {
                $query->where('sender_id', $doctorId)
                    ->where('receiver_id', $delegate->id);
            })
            ->with(['sender:id,name,avatar,role', 'receiver:id,name,avatar,role'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read
        DoctorMessage::where('sender_id', $doctorId)
            ->where('receiver_id', $delegate->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->success($messages, 'تم جلب رسائل المحادثة بنجاح');
    }

    /**
     * Store a newly created message to a doctor.
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

        // Validate receiver is a doctor
        $receiver = User::where('id', $request->receiver_id)
            ->where('role', UserRole::DOCTOR)
            ->first();

        if (!$receiver) {
            return $this->error('لا يمكنك مراسلة هذا المستخدم', 403);
        }

        $message = DoctorMessage::create([
            'sender_id' => $delegate->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
            'is_read' => false,
        ]);

        return $this->success($message->load(['sender:id,name,avatar,role', 'receiver:id,name,avatar,role']), 'تم إرسال الرسالة بنجاح', 201);
    }
}
