<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register
     */
    public function register(Request $request): JsonResponse
    {
        $filelds = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $filelds['password'] = Hash::make($filelds['password']);

        $filelds = request()->only(['name', 'email', 'password']);

        $user = User::create($filelds);

        return response()->json([
            'message' => 'User has been registered successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken($user->name)->plainTextToken;

        return response()->json([
            'message' => 'User has been logged in successfully.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Check User Login
     *
     * @param  LoginRequest  $request
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'message' => 'Authenticated user information.',
            'data' => $user,
        ], 200);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 401);
        }

        User::where('id', $user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ], 200);
    }
}
