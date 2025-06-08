<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    // Create a store
    public function createStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
        ]);

        $store = Store::create($validated);

        return response()->json([
            'message' => 'Tienda creada exitosamente',
            'store_id' => $store->id
        ], 201);
    }

    //List stores with data
    public function listStores()
    {
        $stores = Store::withCount(['technicians', 'repairs'])
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'technicians_count' => $store->technicians_count,
                    'repairs_count' => $store->repairs_count,
                    'total_earnings' => $store->total_earnings,
                    'total_losses' => $store->total_losses,
                    'average_rating' => $store->average_rating,
                ];
            });

        return response()->json($stores);
    }


    //Delete a store if no have technicians
    public function deleteStore(Store $store)
    {
        if ($store->technicians()->exists()) {
            return response()->json(['error' => 'No se puede eliminar una tienda con técnicos asociados'], 400);
        }

        $store->delete();

        return response()->json(['message' => 'Tienda eliminada']);
    }


}
