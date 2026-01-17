<?php

namespace App\Services\Api\V1\PDF;

use App\Models\User;
use App\Repository\PdfChunkRepositoryInterface;
use App\Repository\PdfFileRepositoryInterface;
use Smalot\PdfParser\Parser;
use Mcpuishor\QdrantLaravel\Facades\Qdrant;
use Mcpuishor\QdrantLaravel\DTOs\Point;
use Mcpuishor\QdrantLaravel\PointsCollection;
use Illuminate\Support\Facades\Log;

class PdfService
{
    private string $qdrantCollection;
    private int $vectorSize;

    public function __construct(
        private PdfFileRepositoryInterface $pdfFileRepository,
        private PdfChunkRepositoryInterface $pdfChunkRepository,
        private EmbeddingService $embeddingService,
    ) {
        $this->qdrantCollection = config('qdrant-laravel.connections.main.collection', 'pdf_documents');
        $this->vectorSize = (int) config('qdrant-laravel.connections.main.vector_size', 1536);
    }

    /**
     * Extract text from PDF
     */
    public function extractText(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("PDF file does not exist at path: $path");
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $text = trim($pdf->getText());

        if (!$text || strlen($text) < 50) {
            throw new \Exception('Empty or unreadable PDF');
        }

        return $text;
    }

    /**
     * Chunk text into smaller pieces
     */
    public function chunkText(string $text, int $size = 150, int $overlap = 25): array
    {
        $words = explode(' ', $text);
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($size - $overlap)) {
            $chunks[] = implode(' ', array_slice($words, $i, $size));
        }

        return $chunks;
    }

    /**
     * Process PDF: store file, extract chunks, embed, save to DB and Qdrant
     */
    public function process(User $user, $file)
    {
        // Store PDF
        $storedPath = $file->storeAs('pdfs', $file->hashName() . '.pdf', 'private');

        $pdfFile = $this->pdfFileRepository->createForUser($user, [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
        ]);

        $fullPath = storage_path('app/private/' . $storedPath);
        $text = $this->extractText($fullPath);
        $chunks = $this->chunkText($text);

        foreach ($chunks as $chunk) {
            $embedding = $this->embeddingService->embed($chunk);

            // Store in DB
            $this->pdfChunkRepository->createForPdf($pdfFile, [
                'content' => $chunk,
                'embedding' => json_encode($embedding),
            ]);

            // Store in Qdrant (explicit collection)
            $points = new PointsCollection([
                new Point(
                    id: $pdfFile->id . '-' . md5($chunk),
                    vector: $embedding,
                    payload: [
                        'pdf_id' => $pdfFile->id,
                        'text' => $chunk,
                    ]
                )
            ], $this->qdrantCollection);

            try {
                Qdrant::points()->upsert($points);
            } catch (\Throwable $e) {
                Log::error('Failed to upsert points to Qdrant', [
                    'exception' => $e,
                    'pdf_id' => $pdfFile->id,
                ]);
            }
        }

        return $pdfFile;
    }

    /**
     * Search in Qdrant
     */
    public function search(string $query, int $limit = 5): array
    {
        $embedding = $this->embeddingService->embed($query);

        return Qdrant::search()
            ->vector($embedding)
            ->limit($limit)
            ->withPayload()
            ->get();
    }
}
