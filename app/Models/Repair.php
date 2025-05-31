<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'status',
        'client_id',
        'technician_id',
        'store_id',
        'hours',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'is_warranty',
        'rating',
        'description',
        'finished_at',
    ];

    // Relaciones (solo las que existen actualmente)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($repair) {
            $prefix = 'REP-';
            $random = rand(1000, 9999);
            $namePart = 'XXX';

            if ($repair->client_id) {
                $client = User::find($repair->client_id);
                $namePart = $client ? strtoupper(substr($client->name, 0, 3)) : 'XXX';
            }

            // Generación única simplificada (sin stores ni parts)
            do {
                $ticket = $prefix . $random . $namePart;
                $random = rand(1000, 9999);
            } while (Repair::where('ticket_number', $ticket)->exists());

            $repair->ticket_number = $ticket;
        });
    }
}
