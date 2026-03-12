<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'student_number',
        'university_id',
        'college_id',
        'major_id',
        'level_id',
        'balance',
        'subscribed_until',
        'auto_renew',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class,
        'balance' => 'decimal:2',
        'subscribed_until' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * Check if the user has an active subscription.
     */
    public function isSubscribed(): bool
    {
        if ($this->role === UserRole::ADMIN) {
            return true;
        }

        return $this->subscribed_until && $this->subscribed_until->isFuture();
    }

    /**
     * Cards used by this user.
     */
    public function usedCards()
    {
        return $this->hasMany(Card::class, 'used_by_id');
    }

    /**
     * Check if the user has a given role.
     */
    public function hasRole(UserRole|string $role): bool
    {
        if (is_string($role)) {
            return $this->role->value === $role;
        }
        return $this->role === $role;
    }
    public function university()
    {
        return $this->belongsTo(\App\Models\Academic\University::class);
    }

    public function college()
    {
        return $this->belongsTo(\App\Models\Academic\College::class);
    }

    public function major()
    {
        return $this->belongsTo(\App\Models\Academic\Major::class);
    }

    public function level()
    {
        return $this->belongsTo(\App\Models\Academic\Level::class);
    }

    /**
     * المواد التي يدرسها الدكتور.
     */
    public function subjects()
    {
        return $this->hasMany(\App\Models\Academic\Subject::class, 'doctor_id');
    }

    /**
     * Get the attendances for the student.
     */
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'student_id');
    }

    /**
     * Get the grades for the student.
     */
    public function grades()
    {
        return $this->hasMany(\App\Models\Grade::class, 'student_id');
    }

    /**
     * Get the student notes.
     */
    public function studentNotes()
    {
        return $this->hasMany(\App\Models\StudentNote::class, 'student_id');
    }

    // --- Clinical Constants Customizations (For Doctors) ---
    
    public function customClinicalDepartments()
    {
        return $this->hasMany(\App\Models\Clinical\ClinicalDepartment::class, 'doctor_id');
    }

    public function hiddenClinicalDepartments()
    {
        return $this->belongsToMany(\App\Models\Clinical\ClinicalDepartment::class, 'doctor_hidden_departments', 'doctor_id', 'department_id')->withTimestamps();
    }

    public function customBodySystems()
    {
        return $this->hasMany(\App\Models\Clinical\BodySystem::class, 'doctor_id');
    }

    public function hiddenBodySystems()
    {
        return $this->belongsToMany(\App\Models\Clinical\BodySystem::class, 'doctor_hidden_body_systems', 'doctor_id', 'body_system_id')->withTimestamps();
    }

    public function hiddenChecklists()
    {
        return $this->belongsToMany(\App\Models\Clinical\EvaluationChecklist::class, 'doctor_hidden_checklists', 'doctor_id', 'checklist_id')->withTimestamps();
    }

    /**
     * Check if the user is assigned as a clinical delegate.
     */
    public function clinicalDelegateAssignment()
    {
        return $this->hasOne(\App\Models\ClinicalDelegate::class, 'student_id');
    }

    public function isClinicalDelegate(): bool
    {
        return $this->clinicalDelegateAssignment()->exists();
    }

    public function receivedSubDelegations()
    {
        return $this->hasMany(\App\Models\Clinical\ClinicalSubDelegation::class, 'student_id');
    }

    public function isClinicalSubDelegate()
    {
        return $this->receivedSubDelegations()
            ->where('is_revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })->exists();
    }

    /**
     * Permissions relationship.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if user has a specific granular permission.
     */
    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }

    /**
     * Grant a permission to the user.
     */
    public function givePermission(string $slug): void
    {
        $permission = Permission::where('slug', $slug)->first();
        if ($permission) {
            $this->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    /**
     * Revoke a permission from the user.
     */
    public function revokePermission(string $slug): void
    {
        $permission = Permission::where('slug', $slug)->first();
        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Record a financial transaction and update balance.
     */
    public function recordTransaction($amount, $type, $action, $description, $reference = null, $metadata = null)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($amount, $type, $action, $description, $reference, $metadata) {
            $balanceBefore = $this->balance;
            
            // Adjust balance
            if ($amount > 0) {
                $this->increment('balance', $amount);
            } elseif ($amount < 0) {
                $this->decrement('balance', abs($amount));
            }
            
            $this->refresh();
            $balanceAfter = $this->balance;

            return $this->transactions()->create([
                'amount' => $amount,
                'type' => $type,
                'action' => $action,
                'description' => $description,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_id' => $reference ? $reference->id : null,
                'reference_type' => $reference ? get_class($reference) : null,
                'metadata' => $metadata,
            ]);
        });
    }
}
