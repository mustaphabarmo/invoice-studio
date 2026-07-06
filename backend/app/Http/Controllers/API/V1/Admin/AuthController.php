<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

#[Group('Auth', 'Authentication endpoints for registration, login, and logout.')]
class AuthController extends Controller
{
    /**
     * Log in an administrator.
     *
     * Authenticates an admin user and returns a Sanctum API token for the admin dashboard.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $admin = User::where('email', $credentials['email'])->whereIn('role', ['admin', 'super_admin'])->first();

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($admin->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Admin account is not active.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $admin,
                'role' => $admin->role,
                'token' => $admin->createToken('admin-api')->plainTextToken,
            ],
        ]);
    }

    /**
     * Log out the current administrator.
     *
     * Revokes the current admin API token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}
