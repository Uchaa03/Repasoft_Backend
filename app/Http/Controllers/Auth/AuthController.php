<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TempPasswordMail;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


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
        ]);

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_changed' => true,
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

    //Login wit 2FA
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $user = Auth::user();
        $this->sendTwoFactorCode($user);

        return response()->json([
            'message' => 'Código 2FA enviado',
            'user_id' => $user->id
        ]);
    }

    //Verification 2FA and close login with token creation
    public function verify2FA(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|min:6|max:6'
        ]);

        $user = User::findOrFail($request->user_id);

        // Verify Code and Exp
        if (
            !$user->two_factor_code ||
            !Hash::check($request->code, $user->two_factor_code) ||
            $user->two_factor_expires_at < now()
        ) {
            return response()->json(['error' => 'Código inválido o expirado'], 401);
        }

        // Delete 2FA code
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null
        ]);

        // Create auth token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => $user->getRoleNames()->first(),
            'requires_password_change' => !$user->password_changed
        ]);
    }

    // Sample function for changePassword if you have forbidden
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|different:current_password'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Contraseña actual incorrecta'], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed' => true
        ]);

        return response()->json(['message' => 'Contraseña actualizada']);
    }

    //2FA verification mail send
    private function sendTwoFactorCode(User $user): void
    {
        $code = rand(100000, 999999); // 6 digits code
        $user->update([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(15)
        ]);
        Mail::to($user)->send(new TwoFactorCodeMail($code));
    }

    //Reset password
    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $tempPassword = Str::random(16);
            $user->update([
                'password' => Hash::make($tempPassword),
                'password_changed' => false
            ]);

            Mail::to($user)->send(new TempPasswordMail($tempPassword));
        }

        return response()->json(['message' => 'Si el email existe, se envió una contraseña temporal']);
    }
}
