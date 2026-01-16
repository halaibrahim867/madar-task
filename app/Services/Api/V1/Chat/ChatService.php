<?php

namespace App\Services\Api\V1\Chat;

use App\Repository\ChatRepositoryInterface;
use App\Services\Api\V1\PDF\EmbeddingService;
use Illuminate\Support\Facades\Http;

class ChatService
{
    public function __construct(
        private EmbeddingService $embeddingService,
        private ChatRepositoryInterface $chatRepository
    ) {}

    /**
     * Generate LLM response with RAG
     */
    public function generateResponse(string $query): string
    {
        $embedding = $this->embeddingService->embed($query);

        // fallback fake embedding
        if (empty($embedding)) {
            $embedding = array_fill(0, 1536, 0.01);
        }

        // Get relevant PDF chunks from Qdrant
        $chunks = $this->chatRepository->getRelevantChunks($embedding, 3);
        $context = '';
        foreach ($chunks as $chunk) {
            $context .= $chunk['text'] . "\n";
        }

        $prompt = "Answer the user question using the context below:\n\nContext:\n$context\n\nQuestion:\n$query";

        // Call OpenAI API
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

        dd($response);
        return $response['choices'][0]['message']['content'] ?? "No answer available.";
    }
}
