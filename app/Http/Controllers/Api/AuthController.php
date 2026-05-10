<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Auth Controller (API)
 *
 * Handles user authentication via Laravel Sanctum tokens.
 * Skinny controller — delegates to framework auth services.
 *
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @bodyParam name string required The user's full name. Example: أحمد محمد
     * @bodyParam email string required The user's email. Example: ahmed@example.com
     * @bodyParam password string required The password (min 8 chars). Example: password123
     * @bodyParam password_confirmation string required Must match password.
     * @bodyParam role string optional Role: researcher, supervisor, organizer. Example: researcher
     * @bodyParam department_id integer optional The department ID.
     *
     * @response 201 {"data": {"user": {...}, "token": "1|abc..."}}
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => $validated['password'], // Auto-hashed via model cast
            'role'          => $validated['role'] ?? 'researcher',
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log('registered', $user->id, User::class, $user->id);

        return response()->json([
            'message' => 'تم التسجيل بنجاح.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login and receive an API token.
     *
     * @bodyParam email string required Example: ahmed@example.com
     * @bodyParam password string required Example: password123
     *
     * @response 200 {"data": {"user": {...}, "token": "1|abc..."}}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        // Revoke previous tokens for security
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log('logged_in', $user->id);

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data'    => [
                'user'  => new UserResource($user->load('department')),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout (revoke current token).
     *
     * @authenticated
     * @response 200 {"message": "تم تسجيل الخروج بنجاح."}
     */
    public function logout(Request $request): JsonResponse
    {
        ActivityLog::log('logged_out', $request->user()->id);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @authenticated
     * @response 200 {"data": {...}}
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('department')),
        ]);
    }
}
