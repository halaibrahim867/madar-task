<?php

namespace App\Repository;

use App\Models\PdfFile;

interface PdfChunkRepositoryInterface extends RepositoryInterface
{
    public function createForPdf(PdfFile $pdfFile, array $data);

}
