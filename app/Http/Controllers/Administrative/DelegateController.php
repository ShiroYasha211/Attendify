<?php

namespace App\Http\Controllers\Administrative;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DelegatePermission;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DelegateController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $college = auth()->user()->college;

        if (!$college) {
            abort(403, 'حسابك غير مرتبط بكلية.');
        }
        
        $query = User::where('college_id', $college->id)
            ->whereIn('role', [UserRole::STUDENT, UserRole::DELEGATE, UserRole::PRACTICAL_DELEGATE]);

        // Check if we should show practical delegate options
        $has_clinical_major = false;
        if ($request->filled('major_id')) {
            $has_clinical_major = \App\Models\Academic\Major::where('id', $request->major_id)->where('has_clinical', true)->exists();
        } else {
            $has_clinical_major = \App\Models\Academic\Major::where('college_id', $college->id)->where('has_clinical', true)->exists();
        }

        // Statistics for the header cards
        $stats = [
            'total_students' => (clone $query)->where('role', UserRole::STUDENT)->count(),
            'academic_delegates' => (clone $query)->where('role', UserRole::DELEGATE)->count(),
            'practical_delegates' => (clone $query)->where('role', UserRole::PRACTICAL_DELEGATE)->count(),
        ];

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('student_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('major_id')) {
            $query->where('major_id', $request->major_id);
        }

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        $users = $query->with(['major', 'level', 'delegatePermissions'])->latest()->paginate(25);

        $majors = \App\Models\Academic\Major::where('college_id', $college->id)->get();
        $levels = collect();
        if ($request->filled('major_id')) {
            $levels = \App\Models\Academic\Level::where('major_id', $request->major_id)->get();
        }

        return view('administrative.delegates.index', compact('users', 'majors', 'levels', 'stats', 'has_clinical_major'));
    }

    /**
     * Update the role of a student/delegate.
     */
    public function updateRole(Request $request, User $user)
    {
        $college = auth()->user()->college;

        // Security check
        if ($user->college_id !== $college->id) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|in:student,delegate,practical_delegate',
        ]);

        $newRoleValue = $request->role;

        if ($newRoleValue === 'practical_delegate' && !optional($user->major)->has_clinical) {
            return back()->with('error', 'لا يمكن تعيين مندوب عملي لطالب لا ينتمي إلى تخصص سريري.');
        }

        $oldRole = $user->role->label();
        $user->update(['role' => $newRoleValue]);
        $newRole = $user->role->label();

        // Auto-grant all permissions when promoting to delegate
        if (in_array($newRoleValue, ['delegate', 'practical_delegate'])) {
            $user->grantAllDelegatePermissions(auth()->id());
        }

        // Revoke permissions when demoting to student
        if ($newRoleValue === 'student') {
            $user->revokeDelegatePermissions();
        }

        ActivityLog::log(
            'update',
            'User',
            $user->id,
            $user->name,
            "تغيير دور المستخدم من ({$oldRole}) إلى ({$newRole}) بواسطة مسؤول الكلية"
        );

        return back()->with('success', 'تم تحديث دور المستخدم بنجاح.');
    }

    /**
     * Update delegate permissions (granular CRUD per resource).
     */
    public function updatePermissions(Request $request, User $user)
    {
        $college = auth()->user()->college;

        // Security check: must be in same college
        if ($user->college_id !== $college->id) {
            abort(403);
        }

        // Must be a delegate
        if (!in_array($user->role->value, ['delegate', 'practical_delegate'])) {
            return back()->with('error', 'يمكن إدارة صلاحيات المندوبين فقط.');
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $submittedPermissions = $request->input('permissions', []);

        // Sync: delete all and recreate from submitted
        $user->delegatePermissions()->delete();

        foreach ($submittedPermissions as $permKey) {
            // permKey format: "resource.action" e.g. "students.create"
            $parts = explode('.', $permKey);
            if (count($parts) !== 2) continue;

            [$resource, $action] = $parts;

            // Validate resource and action
            if (!array_key_exists($resource, DelegatePermission::RESOURCES)) continue;
            if (!array_key_exists($action, DelegatePermission::ACTIONS)) continue;

            DelegatePermission::create([
                'user_id' => $user->id,
                'resource' => $resource,
                'action' => $action,
                'granted_by' => auth()->id(),
            ]);
        }

        ActivityLog::log(
            'update',
            'User',
            $user->id,
            $user->name,
            "تحديث صلاحيات المندوب بواسطة مسؤول الكلية"
        );

        return back()->with('success', "تم تحديث صلاحيات المندوب ({$user->name}) بنجاح.");
    }
}
