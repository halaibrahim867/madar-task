<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\Chat\ChatService;
use App\Events\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function send(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:1000'
        ]);

        $user = $request->user();
        $query = $request->input('query');

        // Generate full response (not streaming via HTTP)
        $answer = $this->chatService->generateResponse($query);

        // Broadcast response via Pusher/WebSocket
        broadcast(new ChatMessage($user, $answer));

        // Return the answer immediately via API as well
        return response()->json([
            'status' => 'ok',
            'message' => $answer
        ]);
    }
}
