<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address'];

    public function users()
    {
        return $this->hasMany(User::class);
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

    // Reparaciones asociadas a la tienda
    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    // Ganancias: suma de total_cost de reparaciones completadas y no en garantía
    public function getTotalEarningsAttribute()
    {
        return $this->repairs()
            ->where('is_warranty', false)
            ->where('status', 'completed')
            ->sum('total_cost');
    }

    // Pérdidas: suma de total_cost de reparaciones completadas en garantía
    public function getTotalLossesAttribute()
    {
        return $this->repairs()
            ->where('is_warranty', true)
            ->where('status', 'completed')
            ->sum('total_cost');
    }

    // Rating medio de las reparaciones asociadas a la tienda
    public function getAverageRatingAttribute()
    {
        return $this->repairs()
            ->whereNotNull('rating')
            ->avg('rating');
    }
}
