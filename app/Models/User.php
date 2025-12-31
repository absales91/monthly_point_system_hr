<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,  HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',

          // Salary
    'basic_salary',
    'working_days',
    'per_day_salary',

    // Attendance
    'office_in_time',
    'office_out_time',
    'late_minutes_allowed',
    'half_day_hours',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    function canManage()
{
    return auth()->check() &&
           in_array(auth()->user()->role, ['admin','manager']);
}
    function isAdmin()
{
    return auth()->check() && auth()->user()->role === 'admin';

}

function isAdminOrManager()
{
    return auth()->check() &&
           in_array(auth()->user()->role, ['admin','manager']);
}
}