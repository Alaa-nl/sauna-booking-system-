<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    /**
     * Get bearer token from Authorization header
     * 
     * @return string|null Bearer token or null if not found
     */
    public function getBearerToken()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!$authHeader) {
            return null;
        }
        
        return str_replace('Bearer ', '', $authHeader);
    }
    
    /**
     * Validate JWT token
     * 
     * @param string $token JWT token
     * @return object|false Decoded token payload or false if invalid
     */
    public function validateToken($token)
    {
        if (!$token) {
            return false;
        }
        
        try {
            $secret_key = $_ENV["JWT_SECRET"];
            return JWT::decode($token, new Key($secret_key, 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Validate JWT token and check for admin role
     * 
     * @param string $token JWT token
     * @return boolean True if token is valid and user is admin
     */
    public function validateAdminToken($token)
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded) {
            return false;
        }
        
        // Check for admin role in token
        return isset($decoded->data) && isset($decoded->data->role) && $decoded->data->role === 'admin';
    }
}