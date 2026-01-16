<?php

namespace App\Repository\Eloquent;

use App\Models\PdfFile;
use App\Repository\PdfFileRepositoryInterface;

class PdfFileRepository extends Repository implements PdfFileRepositoryInterface
{
    public function __construct(PdfFile $user)
    {
        $this->model = $user;
    }

    public function createForUser(\App\Models\User $user, array $data)
    {
        return $user->pdfFiles()->create($data);
    }

}
