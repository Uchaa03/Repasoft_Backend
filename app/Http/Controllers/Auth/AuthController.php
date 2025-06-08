<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //Register Admin in Page
    public function registerAdmin(Request $request): JsonResponse
    {
        // Validate data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_changed' => true,
        ]);

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignate role admin
        $user->assignRole('admin');

        // 2FA call
        $this->sendTwoFactorCode($user);

        // Response in json
        return response()->json([
            'message' => 'Admin registrado. Verifica tu correo.',
            'user_id' => $user->id
        ], 201);
    }

    //2FA verification
    private function sendTwoFactorCode(User $user): void
    {
        $code = rand(100000, 999999); // 6 digits code
        $user->update([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(15)
        ]);
        Mail::to($user)->send(new TwoFactorCodeMail($code));
    }

}
