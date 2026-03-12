<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'status' => \App\Http\Middleware\CheckUserStatus::class,
            'clinical_delegate' => \App\Http\Middleware\EnsureClinicalDelegate::class,
            'subscribed' => \App\Http\Middleware\CheckSubscription::class,
        ]);
        $middleware->redirectGuestsTo(fn() => route('admin.login'));
        $middleware->redirectUsersTo(function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user && $user->role) {
                return match ($user->role->value) {
                    \App\Enums\UserRole::ADMIN->value => route('admin.dashboard'),
                    \App\Enums\UserRole::DOCTOR->value => route('doctor.dashboard'),
                    \App\Enums\UserRole::DELEGATE->value, \App\Enums\UserRole::PRACTICAL_DELEGATE->value => route('delegate.dashboard'),
                    \App\Enums\UserRole::STUDENT->value => route('student.dashboard'),
                    default => route('admin.login'),
                };
            }
            return route('admin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
