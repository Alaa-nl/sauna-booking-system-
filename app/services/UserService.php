<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function checkUsernamePassword($username, $password)
    {
        $user = $this->userRepository->findByUsername($username);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user["password"])) {
            return false;
        }
        
        return $user;
    }
}