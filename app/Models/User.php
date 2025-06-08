<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * Attributes for show.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed',
        'dni',
        'address',
        'phone',
        'profile_photo',
        'rating',
        'repairs_count',
        'store_id'
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

    /**
     * User clients repairs
     */
    public function clientRepairs()
    {
        return $this->hasMany(Repair::class, 'client_id');
    }

    /**
     * User technicians repairs
     */
    public function technicianRepairs()
    {
        return $this->hasMany(Repair::class, 'technician_id');
    }


    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
