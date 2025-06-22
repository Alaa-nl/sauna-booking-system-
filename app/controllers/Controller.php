<?php

// base controller, this is a good place to put shared functionality like authentication/authorization, validation, etc

namespace App\Controllers;

use App\Services\ResponseService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Controller
{
    function __construct()
    {
        // No initialization needed for now
    }

    // ensures all expected fields are set in data object and sends a bad request response if not
    // used to make sure all expected $_POST fields are at least set, additional validation may still need to be set
    function validateInput($expectedFields, $data)
    {
        foreach ($expectedFields as $field) {
            if (!isset($data[$field])) {
                ResponseService::Error("Required field: $field, is missing", 400);
                exit();
            }
        }
    }

    // gets the post data and returns it as an array
    function decodePostData()
    {
        try {
            return json_decode(file_get_contents('php://input'), true);
        } catch (\Throwable $th) {
            ResponseService::Error("error decoding JSON in request body", 400);
        }
    }

    // JWT validation following lecture pattern
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

    // Get authenticated user data from JWT
    public function getAuthenticatedUser()
    {
        $decoded = $this->checkForJwt();
        if ($decoded) {
            return $decoded->data;
        }
        return false;
    }

    // Require authentication and return user data
    public function requireAuth()
    {
        return $this->getAuthenticatedUser();
    }
}