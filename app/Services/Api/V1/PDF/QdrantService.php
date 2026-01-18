<?php

namespace App\Services\Api\V1\PDF;

use Mcpuishor\QdrantLaravel\Facades\Qdrant;

class QdrantService
{
    private string $collection = 'pdf_documents';

    /**
     * Search Qdrant for relevant chunks, filtered by user
     */
    public function search(array $vector, int $userId, int $limit = 5): array
    {
        return Qdrant::search()
            ->vector($vector)
            ->limit($limit)
            ->withPayload()
            ->filter(['must' => [
                ['key' => 'user_id', 'match' => $userId]
            ]])
            ->get();
    }
}
