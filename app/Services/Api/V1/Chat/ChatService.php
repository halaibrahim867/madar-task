<?php

namespace App\Services\Api\V1\Chat;

use App\Repository\ChatRepositoryInterface;
use App\Services\Api\V1\PDF\EmbeddingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private int $embeddingSize = 1536;

    public function __construct(
        private EmbeddingService $embeddingService,
        private ChatRepositoryInterface $chatRepository
    ) {}

    /**
     * Generate a response using RAG (PDF chunks + LLM)
     */
    public function generateResponse(string $query): string
    {
        // Get embedding
        $embedding = $this->embeddingService->embed($query);

        // fallback dummy embedding
        if (empty($embedding)) {
            $embedding = array_fill(0, $this->embeddingSize, 0.01);
        }

        // Retrieve relevant chunks from Qdrant via repository
        $chunks = $this->chatRepository->getRelevantChunks($embedding, 3);

        // Build context
        $context = '';
        foreach ($chunks as $chunk) {
            $context .= $chunk['text'] . "\n";
        }

        $prompt = "Answer the user question using the context below:\n\nContext:\n$context\n\nQuestion:\n$query";

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an assistant. Use the provided context to answer the user.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ])
                ->json();

            return $response['choices'][0]['message']['content'] ?? "No answer available.";
        } catch (\Throwable $e) {
            Log::error('OpenAI Chat API failed', [
                'exception' => $e,
                'query' => $query,
            ]);

            return "Sorry, something went wrong while generating a response.";
        }
    }
}
