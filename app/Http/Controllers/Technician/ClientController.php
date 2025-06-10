<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\TempPasswordMail;

class ClientController extends Controller
{
    // Create a client
    public function createClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'dni' => 'required|string|unique:users',
            'phone' => 'required|string',
        ]);

        $tempPassword = Str::random(12);

        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dni' => $validated['dni'],
            'phone' => $validated['phone'],
            'password' => Hash::make($tempPassword),
            'password_changed' => true,
        ]);

        $client->assignRole('client');

        Mail::to($client)->send(new TempPasswordMail($tempPassword));

        return response()->json([
            'message' => 'Cliente creado exitosamente',
            'client' => $client->only(['id', 'name', 'email', 'dni', 'phone']),
        ], 201);
    }


    // Search Client by DNI
    public function findByDni(Request $request)
    {
        $request->validate([
            'dni' => 'required|string'
        ]);

        $client = User::role('client')->where('dni', $request->dni)->first();

        if (!$client) {
            return response()->json([
                'message' => 'Cliente no encontrado'
            ], 404);
        }

        return response()->json($client);
    }
}
