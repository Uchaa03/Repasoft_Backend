<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Mail\RepairStatusChanged;
use App\Models\Part;
use App\Models\Repair;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RepairController extends Controller
{
    // Create new repair
    public function createRepair(Request $request)
    {
        $validated = $request->validate([
            'client_id'    => 'required|exists:users,id',
            'description'  => 'required|string|max:500',
            'hours'        => 'required|numeric|min:0',
            'labor_cost'   => 'required|numeric|min:0',
            'is_warranty'  => 'required|boolean',
        ]);

        $technician = Auth::user();

        $repair = Repair::create([
            ...$validated,
            'technician_id' => $technician->id,
            'store_id'      => $technician->store_id,
            'parts_cost'    => 0,
            'status'        => 'pending',
            'rating'        => null,
            'finished_at'   => null,
        ]);

        // Get client by id selected in creation for get data
        $client = User::find($validated['client_id']);

        if (!$client || !$client->email) {
            return response()->json([
                'message' => 'No se puede enviar el correo: cliente o email no válido'
            ], 400);
        }

        Mail::to($client)
            ->send(new RepairStatusChanged($repair, 'created'));

        return response()->json([
            'message' => 'Reparación creada correctamente',
            'repair'  => $repair->load('client', 'technician'),
        ], 201);
    }


    //Update status in a repair
    public function updateStatus(Request $request, Repair $repair)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $oldStatus = $repair->status;
        $repair->update([
            'status' => $request->status,
            'finished_at' => $request->status === 'completed' ? now() : null,
        ]);

        // If repair is completed increment repairs technician count
        if ($request->status === 'completed') {
            $technician = User::find($repair->technician_id);
            if ($technician) {
                $technician->increment('repairs_count');
            }
        }

        // Get client data
        $client = User::find($repair->client_id);

        if ($repair->wasChanged('status') && $client && $client->email) {
            Mail::to($client)
                ->send(new RepairStatusChanged($repair, $oldStatus));
        }

        return response()->json([
            'message' => 'Estado de la reparación actualizado',
            'repair' => $repair,
        ]);
    }




    public function addPartToRepair(Request $request, Repair $repair)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = Part::findOrFail($request->part_id);


        if ($part->store_id !== Auth::user()->store_id) {
            return response()->json(['message' => 'No tienes permisos para usar esta pieza'], 403);
        }

        // Check repair stock
        if ($part->stock < $request->quantity) {
            return response()->json(['message' => 'No hay suficiente stock disponible'], 400);
        }

        // Add part ti repair
        $repair->parts()->attach($part->id, ['quantity' => $request->quantity]);

        // Update stock part
        $part->decrement('stock', $request->quantity);

        // Update cost repairs
        $partsCost = $repair->parts->sum(function ($part) {
            return $part->pivot->quantity * $part->price;
        });

        $repair->update([
            'parts_cost' => $partsCost,
            'total_cost' => $repair->labor_cost + $partsCost,
        ]);

        return response()->json([
            'message' => 'Pieza añadida a la reparación correctamente',
            'repair' => $repair->load('parts'),
        ]);
    }

    //List repairs
    public function listRepairs(Request $request)
    {
        $repairs = Repair::where('technician_id', Auth::id())
            ->with(['client', 'store', 'parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($repairs);
    }

}
