<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\ActivityLog;
use App\Enums\UserRole;
use Illuminate\Http\Request;

class UserController extends AdminApiController
{
    public function index(Request $request)
    {
        $query = User::with(['university', 'college', 'major', 'level'])->latest();

        if ($request->role && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated($query->paginate($request->per_page ?? 15));
    }

    public function updateStatus(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return $this->error('لا يمكنك تغيير حالة حسابك الخاص.', 422);
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::log(
            $newStatus === 'active' ? 'activate' : 'deactivate',
            'User',
            $user->id,
            $user->name,
            "تغيير حالة المستخدم إلى {$newStatus}"
        );

        return $this->success(['status' => $newStatus], 'تم تحديث حالة المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return $this->error('لا يمكنك حذف حسابك الخاص.', 422);
        }

        ActivityLog::log('delete', 'User', $user->id, $user->name, "حذف المستخدم: {$user->name}");
        $user->delete();

        return $this->success(null, 'تم حذف المستخدم بنجاح');
    }

    public function bulkActivate(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:users,id']);
        $ids = array_filter($request->ids, fn($id) => $id != auth()->id());
        $count = User::whereIn('id', $ids)->update(['status' => 'active']);
        return $this->success(['count' => $count], "تم تفعيل {$count} مستخدم بنجاح");
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:users,id']);
        $ids = array_filter($request->ids, fn($id) => $id != auth()->id());
        $count = User::whereIn('id', $ids)->update(['status' => 'inactive']);
        return $this->success(['count' => $count], "تم تعطيل {$count} مستخدم بنجاح");
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:users,id']);
        $ids = array_filter($request->ids, fn($id) => $id != auth()->id());
        $count = User::whereIn('id', $ids)->delete();
        return $this->success(['count' => $count], "تم حذف {$count} مستخدم بنجاح");
    }
}
