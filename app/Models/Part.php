<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial_number',
        'stock',
        'cost',
        'price',
    ];

    /**
     * Much too Much.
     */
    public function repairs()
    {
        return $this->belongsToMany(Repair::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }
}
