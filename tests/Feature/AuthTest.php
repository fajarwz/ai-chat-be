<?php

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

it('registers a user successfully', function () {
    $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Flyingcars1!',
        'password_confirmation' => 'Flyingcars1!',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(JsonResponse::HTTP_CREATED)
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => $payload['email'],
    ]);
});

it('fails to register with invalid data', function () {
    $payload = [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure(['errors']);
});

it('logs in a user successfully', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('password123'),
    ]);

    $payload = [
        'email' => 'login@example.com',
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/login', $payload);

    $response->assertStatus(JsonResponse::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
});

it('fails to log in with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'fail@example.com',
        'password' => Hash::make('password123'),
    ]);

    $payload = [
        'email' => 'fail@example.com',
        'password' => 'wrongpassword',
    ];

    $response = $this->postJson('/api/login', $payload);

    $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
        ->assertJson([
            'message' => 'Invalid Email or Password',
            'data' => null,
        ]);
});
