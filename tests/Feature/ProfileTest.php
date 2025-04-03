<?php

use App\Models\User;
use Illuminate\Http\JsonResponse;
use function Pest\Laravel\actingAs;

it('shows the authenticated user profile', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    actingAs($user, 'sanctum');

    $response = $this->getJson('/api/user/profile');

    $response->assertStatus(JsonResponse::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'email',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

it('updates the authenticated user profile successfully', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
    ]);

    actingAs($user, 'sanctum');

    $payload = [
        'name' => 'Jane Doe',
    ];

    $response = $this->patchJson('/api/user/profile', $payload);

    $response->assertStatus(JsonResponse::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'email',
            ],
        ])
        ->assertJson([
            'data' => [
                'name' => $payload['name'],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $payload['name'],
    ]);
});
