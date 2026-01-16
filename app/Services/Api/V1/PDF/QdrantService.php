<?php

namespace App\Services\Api\V1\PDF;

use GuzzleHttp\Client;

class QdrantService
{

    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'http://localhost:6333']);
    }

    public function createCollection(string $name, int $vectorSize)
    {
        try {
            $this->client->get("/collections/$name");
            // Collection exists → do nothing
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                // Collection does not exist → create it
                $this->client->put("/collections/$name", [
                    'json' => [
                        'vectors' => [
                            'size' => $vectorSize,
                            'distance' => 'Cosine'
                        ]
                    ]
                ]);
            } else {
                throw $e;
            }
        }
    }


    public function upsertVector(string $collection, string $id, array $vector, array $payload = [])
    {
        try {
            $this->client->post("/collections/$collection/points", [
                'json' => [
                    'points' => [
                        [
                            'id' => $id,
                            'vector' => $vector,
                            'payload' => $payload
                        ]
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error("Qdrant upsert failed: " . $e->getMessage());
        }
    }

    public function searchVector(string $collection, array $vector, int $limit = 5)
    {
        try {
            $res = $this->client->post("/collections/$collection/points/search", [
                'json' => [
                    'vector' => $vector,
                    'limit' => $limit
                ]
            ]);
            return json_decode($res->getBody(), true)['result'] ?? [];
        } catch (\Throwable $e) {
            \Log::error("Qdrant search failed: " . $e->getMessage());
            return [];
        }
    }

}
