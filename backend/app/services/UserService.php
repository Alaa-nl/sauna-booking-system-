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
    
    /**
     * Authenticate a user with username and password
     * 
     * @param array $credentials Array containing username and password
     * @return array|false User data if authenticated, false otherwise
     * @throws \Exception If authentication process fails
     */
    public function authenticate($credentials)
    {
        try {
            // Extract credentials
            $username = $credentials['username'];
            $password = $credentials['password'];
            
            // Find user by username
            $user = $this->userRepository->findByUsername($username);
            
            // Check if user exists
            if (!$user) {
                return false;
            }
            
            // Verify password
            if (!password_verify($password, $user["password"])) {
                return false;
            }
            
            // Return user data without password
            unset($user['password']);
            return $user;
        } catch (\Exception $e) {
            throw new \Exception("Authentication error: " . $e->getMessage());
        }
    }
    
    /**
     * Legacy method for backward compatibility
     * @deprecated Use authenticate() instead
     */
    public function checkUsernamePassword($username, $password)
    {
        return $this->authenticate([
            'username' => $username,
            'password' => $password
        ]);
    }
    
    /**
     * Generate JWT token for authenticated user
     * 
     * @param array $user User data array
     * @return array Token response with JWT, username and role
     */
    public function generateJwt($user)
    {
        // Get JWT settings from environment
        $secret_key = $_ENV["JWT_SECRET"];
        $issuer = "sauna_booking_api";
        $audience = "sauna_booking_client";
        
        // Set token timing parameters
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $issuedAt + 3600; // 1 hour token validity
        
        // Build JWT payload
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
        
        // Generate JWT token
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
        
        // Return token response
        return [
            "jwt" => $jwt, 
            "username" => $user["username"],
            "role" => $user["role"]
        ];
    }
    
    /**
     * Get all users with optional pagination
     * 
     * @param int|null $limit Maximum number of users to return
     * @param int|null $offset Number of users to skip
     * @return array List of users and pagination metadata
     * @throws \Exception If retrieval fails
     */
    public function getAllUsers($limit = null, $offset = null)
    {
        try {
            // Get total count for pagination metadata
            $totalCount = $this->userRepository->getCount();
            
            // Get paginated users
            $users = $this->userRepository->getAll($limit, $offset);
            
            // Return data with pagination metadata
            return [
                'data' => $users,
                'pagination' => [
                    'total' => $totalCount,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $limit ? ($offset + $limit < $totalCount) : false
                ]
            ];
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