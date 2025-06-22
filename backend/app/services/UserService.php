<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Firebase\JWT\JWT;

class UserService
{
    private $userRepository;
    
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }
    
    public function checkUsernamePassword($username, $password)
    {
        try {
            $user = $this->userRepository->findByUsername($username);
            
            if (!$user) {
                return false;
            }
            
            if (!password_verify($password, $user["password"])) {
                return false;
            }
            
            return $user;
        } catch (\Exception $e) {
            throw new \Exception("Authentication error: " . $e->getMessage());
        }
    }
    
    public function generateJwt($user)
    {
        $secret_key = $_ENV["JWT_SECRET"];
        $issuer = "sauna_booking_api";
        $audience = "sauna_booking_client";
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $issuedAt + 1800; // 30 minutes
        
        $payload = [
            "iss" => $issuer,
            "aud" => $audience,
            "iat" => $issuedAt,
            "nbf" => $notBefore,
            "exp" => $expire,
            "data" => [
                "id" => $user["id"],
                "username" => $user["username"],
                "role" => $user["role"]
            ]
        ];
        
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
        return [
            "jwt" => $jwt, 
            "username" => $user["username"],
            "role" => $user["role"]
        ];
    }
    
    public function getAllUsers()
    {
        try {
            return $this->userRepository->getAll();
        } catch (\Exception $e) {
            throw new \Exception("Error retrieving users: " . $e->getMessage());
        }
    }
    
    public function createUser($username, $password, $role = 'employee')
    {
        try {
            // Check if username exists
            $existingUser = $this->userRepository->findByUsername($username);
            if ($existingUser) {
                throw new \Exception("Username already exists");
            }
            
            return $this->userRepository->create($username, $password, $role);
        } catch (\Exception $e) {
            throw new \Exception("Error creating user: " . $e->getMessage());
        }
    }
    
    public function updateUser($id, $data)
    {
        try {
            // If updating username, check if new username exists
            if (isset($data['username'])) {
                $existingUser = $this->userRepository->findByUsername($data['username']);
                if ($existingUser && $existingUser['id'] != $id) {
                    throw new \Exception("Username already exists");
                }
            }
            
            return $this->userRepository->update($id, $data);
        } catch (\Exception $e) {
            throw new \Exception("Error updating user: " . $e->getMessage());
        }
    }
    
    public function resetPassword($id, $newPassword)
    {
        try {
            return $this->userRepository->updatePassword($id, $newPassword);
        } catch (\Exception $e) {
            throw new \Exception("Error resetting password: " . $e->getMessage());
        }
    }
    
    public function changePassword($id, $currentPassword, $newPassword)
    {
        try {
            $user = $this->userRepository->findById($id);
            if (!$user) {
                throw new \Exception("User not found");
            }
            
            if (!password_verify($currentPassword, $user['password'])) {
                throw new \Exception("Current password is incorrect");
            }
            
            return $this->userRepository->updatePassword($id, $newPassword);
        } catch (\Exception $e) {
            throw new \Exception("Error changing password: " . $e->getMessage());
        }
    }
    
    public function deleteUser($id)
    {
        try {
            return $this->userRepository->delete($id);
        } catch (\Exception $e) {
            throw new \Exception("Error deleting user: " . $e->getMessage());
        }
    }
}