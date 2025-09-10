<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and create token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $result['user'],
            'token' => $result['token']
        ]);
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated user
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => $this->authService->getAuthenticatedUser()
        ]);
    }

    /**
     * Refresh a token
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'token' => $this->authService->refreshToken()
        ]);
    }
}
