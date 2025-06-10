<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\User;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TempPasswordMail;
use Spatie\Permission\Models\Role;

class TechnicianController extends Controller
{

    //List data of technician
    public function listTechnicians()
    {
        $technicians = User::role('technician')
            ->with('store:id,name')
            ->select('id', 'name', 'email', 'dni', 'address', 'phone',
                'profile_photo', 'store_id', 'rating', 'repairs_count')
            ->get();

        return response()->json($technicians);
    }

    // Register a Technician
    public function createTechnician(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'store_id' => 'required|exists:stores,id',
            'dni' => 'required|string|unique:users',
            'address' => 'required|string',
            'phone' => 'required|string',
            'profile_photo' => 'required|url',
        ]);

        $tempPassword = Str::random(12);

        $technician = User::create([
            ...$validated,
            'password' => bcrypt($tempPassword),
            'password_changed' => true,
            'admin_id' => auth()->id(),
        ]);

        $technician->assignRole('technician');

        Mail::to($technician)->send(new TempPasswordMail($tempPassword));

        return response()->json([
            'message' => 'Técnico creado exitosamente',
            'technician_id' => $technician->id
        ], 201);
    }

    // Update technician data
    public function updateTechnician(Request $request, User $technician)
    {
        if (!$technician->hasRole('technician')) {
            return response()->json(['error' => 'El usuario no es un técnico'], 400);
        }

        $validated = $request->validate([
            'store_id' => 'exists:stores,id',
            'address' => 'string',
            'phone' => 'string',
            'profile_photo' => 'url'
        ]);

        $technician->update($validated);

        return response()->json(['message' => 'Técnico actualizado']);
    }

    //Delete technician
    public function deleteTechnician(User $technician)
    {
        if ($technician->admin_id != auth()->id()) {
            return response()->json(['error' => 'No tienes permiso para eliminar este técnico'], 403);
        }

        $technician->delete();

        return response()->json(['message' => 'Técnico eliminado']);
    }

    //Get all repairs for admin stores are created
    public function listAllRepairs()
    {
        $admin = Auth::user();

        $storeIds = Store::where('admin_id', $admin->id)->pluck('id');

        if ($storeIds->isEmpty()) {
            return response()->json(['message' => 'El administrador no gestiona ninguna tienda'], 400);
        }

        $repairs = Repair::whereIn('store_id', $storeIds)
            ->with(['client', 'technician', 'store', 'parts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($repairs);
    }

}
