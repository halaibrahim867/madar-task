<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Services\Api\V1\Auth\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {

    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request);
    }
}
