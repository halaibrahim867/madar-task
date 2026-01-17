<?php

namespace App\Repository\Eloquent;

use App\Models\ChatLog;
use App\Repository\ChatRepositoryInterface;
use App\Services\Api\V1\PDF\QdrantService;

class ChatRepository extends Repository implements ChatRepositoryInterface
{
    private QdrantService $qdrantService;

    public function __construct(ChatLog $model, QdrantService $qdrantService)
    {
        $this->model = $model;
        $this->qdrantService = $qdrantService;
    }

    public function getRelevantChunks(array $embedding, int $limit = 3): array
    {
        $results = $this->qdrantService->search($embedding, $limit);

        $chunks = [];
        foreach ($results['result'] ?? [] as $res) {
            $chunks[] = [
                'pdf_id' => $res['payload']['pdf_id'] ?? null,
                'text'   => $res['payload']['text'] ?? '',
            ];
        }

        return $chunks;
    }
}
