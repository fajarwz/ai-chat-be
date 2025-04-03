<?php

use App\Models\AiChat;
use App\Models\User;
use App\Enums\AiChatRole;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function Pest\Laravel\actingAs;

it('returns chat history for authenticated user', function () {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    AiChat::create([
        'user_id' => $user->id,
        'role' => AiChatRole::User,
        'content' => 'Hello',
    ]);
    AiChat::create([
        'user_id' => $user->id,
        'role' => AiChatRole::Assistant,
        'content' => 'Hi there',
    ]);

    $response = $this->getJson('/api/chat/history');

    $response->assertStatus(JsonResponse::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'user_id', 'role', 'content', 'created_at', 'updated_at'],
            ],
        ]);
});

it('processes chat and stores assistant response', function () {
    $user = User::factory()->create();
    actingAs($user, 'sanctum');

    $response = new \GuzzleHttp\Psr7\Response(200, [], 
        "data: {\"choices\":[{\"delta\":{\"content\":\"Hello \"}}]}\n" .
        "data: {\"choices\":[{\"delta\":{\"content\":\"World\"}}]}\n" .
        "data: [DONE]\n"
    );

    $mock = new MockHandler([$response]);

    $handler = HandlerStack::create($mock);
    $mockedClient = new Client(['handler' => $handler]);

    $this->app->instance(Client::class, $mockedClient);

    $payload = ['message' => 'Test message'];
    $this->postJson('/api/chat', $payload);

    expect(AiChat::where('user_id', $user->id)
        ->exists())->toBeTrue();
});

