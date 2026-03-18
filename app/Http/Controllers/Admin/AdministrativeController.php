<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Academic\College;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdministrativeController extends Controller
{
    /**
     * Display a listing of administrative users.
     */
    public function index()
    {
        $administratives = User::where('role', UserRole::ADMINISTRATIVE)
            ->with('college')
            ->latest()
            ->paginate(10);

        return view('admin.administratives.index', compact('administratives'));
    }

    /**
     * Show the form for creating a new administrative user.
     */
    public function create()
    {
        $colleges = College::orderBy('name')->get();
        return view('admin.administratives.create', compact('colleges'));
    }

    /**
     * Store a newly created administrative user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'college_id' => 'required|exists:colleges,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::ADMINISTRATIVE,
            'college_id' => $request->college_id,
            'status' => $request->status,
        ]);

        ActivityLog::log(
            'create',
            'User',
            $user->id,
            $user->name,
            "إضافة إداري جديد للكلية: {$user->college->name}"
        );

        return redirect()->route('admin.administratives.index')
            ->with('success', 'تم إضافة المسؤول الإداري بنجاح.');
    }

    /**
     * Show the form for editing the specified administrative user.
     */
    public function edit(User $administrative)
    {
        if ($administrative->role !== UserRole::ADMINISTRATIVE) {
            abort(404);
        }

        $colleges = College::orderBy('name')->get();
        return view('admin.administratives.edit', [
            'administrative' => $administrative,
            'colleges' => $colleges
        ]);
    }

    /**
     * Update the specified administrative user in storage.
     */
    public function update(Request $request, User $administrative)
    {
        if ($administrative->role !== UserRole::ADMINISTRATIVE) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $administrative->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'college_id' => 'required|exists:colleges,id',
            'status' => 'required|in:active,inactive',
        ]);

        $administrative->update([
            'name' => $request->name,
            'email' => $request->email,
            'college_id' => $request->college_id,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $administrative->update([
                'password' => Hash::make($request->password),
            ]);
        }

        ActivityLog::log(
            'update',
            'User',
            $administrative->id,
            $administrative->name,
            "تحديث بيانات المسؤول الإداري: {$administrative->name}"
        );

        return redirect()->route('admin.administratives.index')
            ->with('success', 'تم تحديث بيانات المسؤول الإداري بنجاح.');
    }

    /**
     * Remove the specified administrative user from storage.
     */
    public function destroy(User $administrative)
    {
        if ($administrative->role !== UserRole::ADMINISTRATIVE) {
            abort(404);
        }

        $name = $administrative->name;
        $administrative->delete();

        ActivityLog::log(
            'delete',
            'User',
            null,
            $name,
            "حذف المسؤول الإداري: {$name}"
        );

        return redirect()->route('admin.administratives.index')
            ->with('success', 'تم حذف المسؤول الإداري بنجاح.');
    }
}
