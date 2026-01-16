<?php

namespace App\Repository;

use App\Models\PdfFile;
use App\Models\User;

interface PdfFileRepositoryInterface extends RepositoryInterface
{
    public function createForUser(User $user, array $data);

}
