<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);
        
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        
        $token = $user->createToken('api-token')->plainTextToken;
        
        return [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'token' => $token
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }
}