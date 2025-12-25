<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'name_first',
        'name_last',
        'email',
        'password',
        'root_admin',
        'external_id',
        'uuid',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'root_admin' => 'boolean',
        'use_totp' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->name_first ?? '') . ' ' . ($this->name_last ?? '')) ?: $this->name ?? '';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->root_admin === true;
    }

    /**
     * Servers owned by this user.
     */
    public function servers()
    {
        return $this->hasMany(Server::class, 'owner_id');
    }
}
