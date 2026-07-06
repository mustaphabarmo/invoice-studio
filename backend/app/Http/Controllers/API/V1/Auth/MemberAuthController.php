<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

#[Group('Auth', 'Authentication endpoints for registration, login, and logout.')]
class MemberAuthController extends Controller
{
    /**
     * Register a new NIMN member account.
     *
     * Creates a member profile and returns a Sanctum API token for the member portal.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'membership_number' => ['nullable', 'string', 'max:80', 'unique:users,membership_number'],
            'membership_grade' => ['nullable', 'string', 'max:80'],
            'organization' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
        ]);

        $member = User::create([
            ...$data,
            'role' => 'member',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member registered successfully',
            'data' => [
                'user' => $member,
                'token' => $member->createToken('api')->plainTextToken,
            ],
        ], 201);
    }

    /**
     * Log in a NIMN user.
     *
     * Authenticates a member or administrator with email and password and returns a role-based Sanctum API token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $member = User::where('email', $credentials['email'])->first();

        if (! $member || ! Hash::check($credentials['password'], $member->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($member->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'This member account is suspended.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $member,
                'role' => $member->role,
                'token' => $member->createToken($member->role . '-api')->plainTextToken,
            ],
        ]);
    }

    /**
     * Log out the current member.
     *
     * Revokes the current member API token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }
}
