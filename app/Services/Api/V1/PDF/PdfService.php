<?php

namespace App\Services\Api\V1\PDF;

use App\Models\User;
use App\Repository\PdfChunkRepositoryInterface;
use App\Repository\PdfFileRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class PdfService
{
    public function __construct(
        private PdfFileRepositoryInterface $pdfFileRepository,
        private PdfChunkRepositoryInterface $pdfChunkRepository
    ) {}

    /**
     * Extract text from a PDF file (pure PHP)
     */
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

    /**
     * Split text into chunks
     */
    public function chunkText(string $text, int $size = 500, int $overlap = 50): array
    {
        $words = explode(' ', $text);
        $chunks = [];

        for ($i = 0; $i < count($words); $i += ($size - $overlap)) {
            $chunks[] = implode(' ', array_slice($words, $i, $size));
        }

        return $chunks;
    }


    /**
     * Generate OpenAI embeddings with safe fallback
     */
    public function embed(string $text): array
    {
        try {
            $response = Http::withToken(config('services.openai.key'))
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ])
                ->json();

            if (isset($response['error'])) {
                // Log the OpenAI error and skip embedding
                Log::warning('OpenAI embedding skipped: ' . $response['error']['message']);
                return [];
            }

            return $response['data'][0]['embedding'] ?? [];
        } catch (\Throwable $e) {
            Log::error('Embedding request failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Process uploaded PDF: store, extract, chunk, embed safely
     */
    public function process(User $user, $file)
    {
        // Store file with .pdf extension
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

        foreach ($chunks as $chunk) {
            $this->pdfChunkRepository->createForPdf($pdfFile, [
                'content'   => $chunk,
                'embedding' => json_encode($this->embed($chunk)),
            ]);
        }

        return $pdfFile;
    }
}
