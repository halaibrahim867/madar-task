<?php

namespace App\Services\Api\V1\PDF;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class EmbeddingService
{
    public function embed(string $text): array
    {
        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ])
                ->json();

            if (isset($response['error'])) {
                Log::warning('OpenAI embedding skipped: ' . $response['error']['message']);
                return [];
            }

            return $response['data'][0]['embedding'] ?? [];
        } catch (\Throwable $e) {
            Log::error('Embedding request failed: ' . $e->getMessage());
            return [];
        }
    }
}
