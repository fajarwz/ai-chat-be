<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'success',
            'data' => $request->user(),
        ]);
    }

    public function updateProfile(StoreProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $user->refresh(),
        ]);
    }
}
