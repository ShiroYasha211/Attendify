<?php

namespace App\Models\Student;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TreeFarmProfile extends Model
{
    protected $fillable = [
        'user_id',
        'public_name',
        'is_public',
        'use_alias',
        'coins_balance',
        'total_focus_seconds',
        'total_public_focus_seconds',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'use_alias' => 'boolean',
        'coins_balance' => 'integer',
        'total_focus_seconds' => 'integer',
        'total_public_focus_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
