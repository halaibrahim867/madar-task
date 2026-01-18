<?php

namespace App\Repository;


interface ChatRepositoryInterface extends RepositoryInterface
{
    public function getRelevantChunks(array $embedding,int $userId, int $limit = 3): array;

}
