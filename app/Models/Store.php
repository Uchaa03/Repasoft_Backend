<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'admin_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function admins()
    {
        return $this->users()->whereHas('roles', function($query) {
            $query->where('name', 'admin');
        });
    }

    public function technicians()
    {
        return $this->users()->whereHas('roles', function($query) {
            $query->where('name', 'technician');
        });
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    public function getTotalEarningsAttribute()
    {
        return $this->repairs()
            ->where('is_warranty', false)
            ->where('status', 'completed')
            ->sum('total_cost');
    }

    public function getTotalLossesAttribute()
    {
        return $this->repairs()
            ->where('is_warranty', true)
            ->where('status', 'completed')
            ->sum('total_cost');
    }

    public function getAverageRatingAttribute()
    {
        return $this->repairs()
            ->whereNotNull('rating')
            ->avg('rating');
    }
}
