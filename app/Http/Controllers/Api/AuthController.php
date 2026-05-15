<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required', 'password' => 'required']);
        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kredensial salah'], 401);
        }

        $abilities = $user->hasRole('admin')
            ? ['equipment:manage', 'booking:manage']
            : ['equipment:read', 'booking:create', 'booking:check-in'];
        $deviceName = $request->header('User-Agent') ?? 'Unknown Device';

        $token = $user->createToken($deviceName, $abilities)->plainTextToken;
        return response()->json(

            [
                'success' => true,
                'message' => 'Login berhasil',
                'profile'=>$user->profile,
                'access_token' => $token,
                'role' => $user->role->name
            ]
        );
    }
}
