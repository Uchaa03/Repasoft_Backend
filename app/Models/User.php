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

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed',
        'two_factor_code',
        'two_factor_expires_at',
        'dni',
        'address',
        'phone',
        'profile_photo',
        'rating',
        'repairs_count',
        'store_id',
        'admin_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'api';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed' => 'boolean',
        'rating' => 'float',
        'repairs_count' => 'integer',
    ];

    public function clientRepairs()
    {
        return $this->hasMany(Repair::class, 'client_id');
    }

    public function technicianRepairs()
    {
        return $this->hasMany(Repair::class, 'technician_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
