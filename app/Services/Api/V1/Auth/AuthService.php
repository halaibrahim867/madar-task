<?php

namespace App\Services\Api\V1\Auth;

use App\Http\Resources\User\UserResource;
use App\Repository\UserRepositoryInterface;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login($request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                $token = $user->createToken('api_token')->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => new UserResource($user)
                ]);
            }

            return response()->json([
                'error'=>'Invalid credentials'
            ], 401);
        }catch (\Exception $exception){
            return response()->json([
                'error'=>$exception->getMessage()
            ]);
        }
    }

}
