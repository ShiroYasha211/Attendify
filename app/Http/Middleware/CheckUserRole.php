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

        // Delegate context switching logic
        // Allow practical_delegate to access delegate routes
        if ($role === 'delegate' && $userRole === 'practical_delegate') {
            return $next($request);
        }

        // Allow delegate and practical_delegate to access student routes
        if ($role === 'student' && in_array($userRole, ['delegate', 'practical_delegate'])) {
            return $next($request);
        }

        // If the route requires 'student' but user is 'practical_delegate', it's allowed above.
        // If the route requires 'delegate' but user is 'practical_delegate', it's allowed above.

        if ($userRole !== $role) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة.');
        }

        return $next($request);
    }
}
