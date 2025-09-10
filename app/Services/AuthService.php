<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        
        return User::create($data);
    }

    /**
     * Authenticate a user and return a token
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the current user
     */
    public function logout(): void
    {
        Auth::user()->currentAccessToken()->delete();
    }

    /**
     * Refresh the user's token
     */
    public function refreshToken(): string
    {
        $user = Auth::user();
        $user->tokens()->delete();
        
        return $user->createToken('auth-token')->plainTextToken;
    }

    /**
     * Get the authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }
}
