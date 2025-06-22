<?php

namespace App\Controllers;

use App\Services\ResponseService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Controller
{
    function __construct()
    {
        // Set CORS headers for all requests
        ResponseService::SetCorsHeaders();
    }

    /**
     * Validates that all expected fields are set in the data object
     * 
     * @param array $expectedFields Fields that must be present
     * @param array $data Data to validate
     */
    function validateInput($expectedFields, $data)
    {
        foreach ($expectedFields as $field) {
            if (!isset($data[$field])) {
                ResponseService::Error("Required field: $field, is missing", 400);
                exit();
            }
        }
    }

    /**
     * Gets the post data from request body and returns it as an array
     * 
     * @return array Decoded JSON data
     */
    function decodePostData()
    {
        try {
            return json_decode(file_get_contents('php://input'), true);
        } catch (\Throwable $th) {
            ResponseService::Error("Error decoding JSON in request body", 400);
            exit();
        }
    }

    /**
     * Validates JWT token from Authorization header
     * 
     * @return object|false Decoded JWT payload or false if invalid
     */
    public function checkForJwt()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (!$authHeader) {
            ResponseService::Error("No token provided", 401);
            return false;
        }
        
        $jwt = str_replace('Bearer ', '', $authHeader);
        
        try {
            $secret_key = $_ENV["JWT_SECRET"];
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            ResponseService::Error("Invalid token: " . $e->getMessage(), 401);
            return false;
        }
    }

    /**
     * Get authenticated user data from JWT
     * 
     * @return object|false User data from token or false if not authenticated
     */
    public function getAuthenticatedUser()
    {
        $decoded = $this->checkForJwt();
        if ($decoded) {
            return $decoded->data;
        }
        return false;
    }

    /**
     * Require authentication and return user data
     * 
     * @return object User data from token
     */
    public function requireAuth()
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            exit(); // checkForJwt already sent error response
        }
        return $user;
    }
    
    /**
     * Require admin role and return user data
     * 
     * @return object User data from token
     */
    public function requireAdmin()
    {
        $user = $this->requireAuth();
        if ($user->role !== 'admin') {
            ResponseService::Error("Admin access required", 403);
            exit();
        }
        return $user;
    }
}