<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DOCTOR = 'doctor';
    case DELEGATE = 'delegate';
    case STUDENT = 'student';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::DOCTOR => 'Doctor',
            self::DELEGATE => 'Class Delegate',
            self::STUDENT => 'Student',
        };
    }
}