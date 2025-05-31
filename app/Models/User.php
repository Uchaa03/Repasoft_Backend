<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;

class User extends Authenticate
{
    use HasFactory, Notifiable;

    /**
     * Attributes for show.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'password_changed',
        'dni',
        'address',
        'phone',
        'profile_photo',
        'rating',
        'repairs_count',
    ];

    /**
     * Attributes hidden for security
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Native attributes for cast attributes
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed' => 'boolean',
        'rating' => 'float',
        'repairs_count' => 'integer',
    ];
}
