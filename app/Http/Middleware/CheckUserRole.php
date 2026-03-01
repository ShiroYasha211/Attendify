<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة.');
        }

        $userRole = Auth::user()->role->value;

        $isClinicalDelegate = \App\Models\ClinicalDelegate::where('student_id', Auth::id())->exists();

        // Delegate context switching logic
        // Allow clinical delegates to access delegate routes
        if ($role === 'delegate' && $isClinicalDelegate) {
            return $next($request);
        }

        // Allow delegate and clinical_delegate to access student routes
        if ($role === 'student' && ($userRole === 'delegate' || $isClinicalDelegate)) {
            return $next($request);
        }

        // If the route requires 'student' but user is clinical delegate, it's allowed above.
        // If the route requires 'delegate' but user is clinical delegate, it's allowed above.

        if ($userRole !== $role) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}
