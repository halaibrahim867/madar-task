<?php

namespace App\Services\Api\V1\PDF;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private int $dummySize = 1536;

    public function embed(string $text): array
    {
        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ]);

            if (isset($response['error'])) {
                Log::warning('OpenAI embedding skipped: ' . $response['error']['message']);
                return $this->dummyEmbedding();
            }

            return $response['data'][0]['embedding'] ?? $this->dummyEmbedding();

        } catch (\Throwable $e) {
            Log::warning('OpenAI embedding failed: ' . $e->getMessage());
            return $this->dummyEmbedding();
        }
    }

    private function dummyEmbedding(): array
    {
        return array_fill(0, $this->dummySize, 0.01);
    }
}
