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
        $this->client->put("/collections/$name", [
            'json' => [
                'vectors' => [
                    'size' => $vectorSize,
                    'distance' => 'Cosine'
                ]
            ]
        ]);
    }

    public function upsertVector(string $collection, string $id, array $vector, array $payload = [])
    {
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
    }

    public function searchVector(string $collection, array $vector, int $limit = 5)
    {
        $res = $this->client->post("/collections/$collection/points/search", [
            'json' => [
                'vector' => $vector,
                'limit' => $limit
            ]
        ]);

        return json_decode($res->getBody(), true);
    }
}
