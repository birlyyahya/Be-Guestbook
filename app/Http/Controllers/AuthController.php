<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            // Hapus token lama (opsional, biar 1 device 1 token)
            $user->tokens()->delete();

            // Buat token baru
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'success',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ],
                'error' => null
            ], 200);
        }

        return response()->json([
            'message' => 'error',
            'data' => null,
            'error' => 'Invalid credentials'
        ], 401);
    }


    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'role' => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Auth::login($user);

        return response()->json([
            'message' => 'success',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'error' => null,
        ], 201);
    }
}
