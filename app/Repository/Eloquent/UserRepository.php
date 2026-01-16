<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Repository\UserRepositoryInterface;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

}
