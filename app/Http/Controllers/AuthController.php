<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Email or Password',
                'data' => null,
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'success',
            'data' => [
                'token' => $user->createToken('token')->plainTextToken,
                'user' => $user,
            ],
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'success',
            'data' => [
                'token' => $user->createToken('token')->plainTextToken,
                'user' => $user,
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
