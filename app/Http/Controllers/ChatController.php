<?php

namespace App\Http\Controllers;

use App\Enums\AiChatRole;
use App\Http\Requests\ChatRequest;
use App\Models\AiChat;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(private string $aiBaseUrl = '')
    {
        $this->aiBaseUrl = config('openai.base_url');
    }

    public function chatHistory(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'success',
            'data' => AiChat::where('user_id', $request->user()->id)->get(),
        ]);
    }

    public function chat(ChatRequest $request): StreamedResponse
    {
        return DB::transaction(function () use ($request) {
            $prompt = $request->input('message');
            $user = $request->user();
    
            AiChat::create([
                'user_id' => $user->id,
                'role' => AiChatRole::User,
                'content' => $prompt
            ]);
    
            $history = AiChat::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->take(20)
                ->get(['role', 'content'])
                ->map(fn($chat) => ['role' => $chat->role->value, 'content' => $chat->content])
                ->toArray();
    
            $history[] = ['role' => AiChatRole::User->value, 'content' => $prompt];
        
            $client = new Client();
            $response = $client->post("{$this->aiBaseUrl}/chat/completions", [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('openai.api_key'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4',
                    'messages' => $history,
                    'stream' => true
                ],
                'stream' => true
            ]);
        
            return response()->stream(function () use ($response, $user) {
                $body = $response->getBody();
                $buffer = '';
                $assistantMessage = '';
        
                while(!$body->eof()) {
                    $chunk = $body->read(1024);
                    $buffer .= $chunk;
                    $lines = explode("\n", $buffer);
                    $buffer = array_pop($lines);
        
                    foreach ($lines as $line) {
                        if (str_starts_with($line, "data: ")) {
                            $data = substr($line, 6);
                            if (trim($data) === "[DONE]") break;
        
                            $json = json_decode($data, true);
                            $content = $json['choices'][0]['delta']['content'] ?? '';
        
                            echo $content;
                            ob_flush();
                            flush();
    
                            $assistantMessage .= $content;
                        }
                    }
                }
    
                AiChat::create([
                    'user_id' => $user->id,
                    'role' => AiChatRole::Assistant,
                    'content' => $assistantMessage
                ]);
            }, JsonResponse::HTTP_OK, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no'
            ]);
        });
    }
}
