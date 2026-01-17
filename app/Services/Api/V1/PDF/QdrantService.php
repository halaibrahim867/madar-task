<?php

namespace App\Services\Api\V1\PDF;

use Mcpuishor\QdrantLaravel\Facades\Qdrant;

class QdrantService
{
    /**
     * Search Qdrant for relevant chunks
     */
    public function search(array $vector, int $limit = 5): array
    {
        return Qdrant::search()
            ->vector($vector)
            ->limit($limit)
            ->withPayload()
            ->get();
    }
}
