<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\profile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The "profile" model reads the SAME users table (it has no table of its own and no
     * user_id column), so the profile of a user is that very row. The old
     * hasOne(profile::class) looked for users.user_id, which does not exist, and any
     * call to it ended in an SQL error. Read-only use only: never call ->delete() on it.
     */
    public function profile()
    {
        return $this->hasOne(profile::class, 'id', 'id');
    }

    /** Role, is_active and system_account are never mass assignable; controllers use forceFill(). */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'system_account',
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
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->hasRole('Admin');
    }

    public function isSuperAdmin()
    {
        return $this->system_account === 'superadmin';
    }

    public function isCashier()
    {
        return $this->role === 'cashier';
    }

}
