<?php

namespace App\Repository\Eloquent;

use App\Models\PdfChunk;
use App\Repository\PdfChunkRepositoryInterface;

class PdfChunkRepository extends Repository implements PdfChunkRepositoryInterface
{
    public function __construct(PdfChunk $user)
    {
        $this->model = $user;
    }

    public function createForPdf(\App\Models\PdfFile $pdfFile, array $data): void
    {
        $pdfFile->chunks()->create($data);
    }
}
