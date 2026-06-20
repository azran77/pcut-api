<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'student_id',
        'institution',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function classes()
    {
        return $this->belongsToMany(ClassGroup::class, 'class_students', 'student_id', 'class_id');
    }

    public function taughtClasses()
    {
        return $this->hasMany(ClassGroup::class, 'educator_id');
    }

    public function submissions()
    {
        return $this->hasMany(SurveySubmission::class, 'student_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isEducator(): bool { return $this->hasRole('educator'); }
    public function isStudent(): bool  { return $this->hasRole('student'); }
}
