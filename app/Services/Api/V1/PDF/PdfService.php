<?php
namespace App\Services\Api\V1\PDF;

use App\Models\User;
use App\Repository\PdfChunkRepositoryInterface;
use App\Repository\PdfFileRepositoryInterface;
use Smalot\PdfParser\Parser;

class PdfService
{
    public function __construct(
        private PdfFileRepositoryInterface $pdfFileRepository,
        private PdfChunkRepositoryInterface $pdfChunkRepository,
        private EmbeddingService $embeddingService,
        private QdrantService $qdrantService,
    ) {}

    public function extractText(string $path): string
    {
        if (!file_exists($path)) {
            throw new \Exception("PDF file does not exist at path: $path");
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        if (! $text || strlen(trim($text)) < 50) {
            throw new \Exception('Empty or unreadable PDF');
        }

        return $text;
    }

    public function chunkText(string $text, int $size = 500, int $overlap = 50): array
    {
        $words = explode(' ', $text);
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($size - $overlap)) {
            $chunks[] = implode(' ', array_slice($words, $i, $size));
        }

        return $chunks;
    }

    public function process(User $user, $file)
    {
        // Store PDF
        $storedPath = $file->storeAs('pdfs', $file->hashName() . '.pdf');

        $pdfFile = $this->pdfFileRepository->createForUser($user, [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $storedPath,
        ]);

        $fullPath = storage_path('app/private/' . $storedPath);

        // Extract text
        $text = $this->extractText($fullPath);

        // Chunk text
        $chunks = $this->chunkText($text, 150, 25);

        // Ensure Qdrant collection exists
        $vectorSize = 1536; // for text-embedding-3-small
        $this->qdrantService->createCollection('pdf_documents', $vectorSize);

        foreach ($chunks as $chunk) {
            $embedding = $this->embeddingService->embed($chunk);

// fallback if embedding is empty
            if (empty($embedding)) {
                $embedding = array_fill(0, 1536, 0.01); // 1536-dim fake embedding
            }

            $this->pdfChunkRepository->createForPdf($pdfFile, [
                'content'   => $chunk,
                'embedding' => json_encode($embedding),
            ]);

            if (!empty($embedding)) {
                $this->qdrantService->upsertVector(
                    'pdf_documents',
                    $pdfFile->id . '-' . md5($chunk),
                    $embedding,
                    ['pdf_id' => $pdfFile->id, 'text' => $chunk]
                );
            }
        }

        return $pdfFile;
    }

    public function search(string $query, int $limit = 5)
    {
        $embedding = $this->embeddingService->embed($query);
        if (empty($embedding)) return [];

        return $this->qdrantService->searchVector('pdf_documents', $embedding, $limit);
    }
}
