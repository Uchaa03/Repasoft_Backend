<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Repair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartController extends Controller
{

    // Add part to stock when technician give then
    public function addPart(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:parts',
            'stock' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $part = new Part([
            'name' => $request->name,
            'serial_number' => $request->serial_number,
            'stock' => $request->stock,
            'cost' => $request->cost,
            'price' => $request->price,
            'store_id' => Auth::user()->store_id,
        ]);

        $part->save();

        return response()->json([
            'message' => 'Pieza añadida correctamente',
            'part' => $part,
        ], 201);
    }


    public function incrementStockPart(Part $part)
    {
        if ($part->store_id !== Auth::user()->store_id) {
            return response()->json([
                'message' => 'No tienes permisos para modificar esta pieza'
            ], 403);
        }

        $part->increment('stock');

        return response()->json([
            'message' => 'Stock incrementado correctamente',
            'new_stock' => $part->stock
        ]);
    }

    // Add part to repair
    public function addToRepairPart(Request $request, Repair $repair)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = Part::findOrFail($request->part_id);

        // Verify part is added in store stock
        if ($part->store_id !== Auth::user()->store_id) {
            return response()->json([
                'message' => 'No tienes permisos para usar esta pieza'
            ], 403);
        }

        // Verify if part have in stock
        if ($part->stock < $request->quantity) {
            return response()->json([
                'message' => 'No hay suficiente stock disponible'
            ], 400);
        }

        // Add part to repair
        $repair->parts()->attach($part->id, ['quantity' => $request->quantity]);

        // Delete quantity stock
        $part->decrement('stock', $request->quantity);

        // If stock get 0 when a part is added in a repair warning to technician
        if ($part->stock == 0) {
            return response()->json([
                'message' => 'Pieza añadida a la reparación correctamente. ¡Atención! El stock de esta pieza ha llegado a cero.',
                'repair' => $repair->load('parts'),
            ]);
        }

        return response()->json([
            'message' => 'Pieza añadida a la reparación correctamente',
            'repair' => $repair->load('parts'),
        ]);
    }

    //List parts for get
    public function listParts(Request $request)
    {
        $user = Auth::user();
        $parts = Part::where('store_id', $user->store_id)
            ->orderBy('name')
            ->get();

        return response()->json($parts);
    }
}
