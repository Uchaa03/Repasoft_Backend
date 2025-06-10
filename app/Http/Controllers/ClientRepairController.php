<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientRepairController extends Controller
{
    // List client repairs
    public function listRepairs(Request $request)
    {
        $repairs = Repair::where('client_id', Auth::id())
            ->with(['technician', 'store', 'parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($repairs);
    }

    // Rate a repair when is complete
    public function rateRepair(Request $request, Repair $repair)
    {
        if ($repair->status !== 'completed' || $repair->client_id !== Auth::id()) {
            return response()->json(['message' => 'No puedes valorar esta reparación'], 403);
        }

        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        // Update repair rating
        $repair->update(['rating' => $request->rating]);

        // Update average rating technician
        $technician = User::find($repair->technician_id);
        $technicianRating = Repair::where('technician_id', $repair->technician_id)
            ->whereNotNull('rating')
            ->avg('rating');
        $technician->update(['rating' => $technicianRating]);

        // Update average rating store
        $store = Store::find($repair->store_id);
        $storeRating = Repair::where('store_id', $repair->store_id)
            ->whereNotNull('rating')
            ->avg('rating');
        $store->update(['rating' => $storeRating]);

        return response()->json([
            'message' => 'Valoración registrada correctamente',
            'repair' => $repair,
            'technician_rating' => $technicianRating,
            'store_rating' => $storeRating,
        ]);
    }
}
