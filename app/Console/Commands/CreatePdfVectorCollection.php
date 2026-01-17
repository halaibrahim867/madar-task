<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mcpuishor\QdrantLaravel\Facades\Schema;
use Mcpuishor\QdrantLaravel\Exceptions\FailedToCreateCollectionException;

class CreatePdfVectorCollection extends Command
{
    protected $signature = 'app:create-pdf-vector-collection';
    protected $description = 'Create Qdrant collection for PDF documents';

    public function handle(): int
    {
        try {
            Schema::create('pdf_documents', [
                'size' => 3072,
                'distance' => 'Cosine',
            ]);

            $this->info('Qdrant collection [pdf_documents] created successfully.');
        } catch (FailedToCreateCollectionException $e) {
            if (str_contains($e->getMessage(), 'already exists')) {
                $this->info('Collection [pdf_documents] already exists. Skipping creation.');
            } else {
                throw $e;
            }
        }

        return self::SUCCESS;
    }
}
