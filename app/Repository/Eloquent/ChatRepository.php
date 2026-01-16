<?php

namespace App\Repository\Eloquent;

use App\Models\ChatLog;
use App\Models\PdfFile;
use App\Repository\ChatRepositoryInterface;
use App\Services\Api\V1\PDF\QdrantService;

class ChatRepository extends Repository implements ChatRepositoryInterface
{
    public function __construct(ChatLog $user, QdrantService $qdrantService)
    {
        $this->model = $user;
        $this->qdrantService = $qdrantService;
    }

    public function getRelevantChunks(array $embedding, int $limit = 3): array
    {
        $results = $this->qdrantService->searchVector('pdf_documents', $embedding, $limit);
        $chunks = [];

        foreach ($results['result'] ?? [] as $res) {
            $chunks[] = [
                'pdf_id' => $res['payload']['pdf_id'],
                'text'   => $res['payload']['text'],
            ];
        }

        return $chunks;
    }
}
