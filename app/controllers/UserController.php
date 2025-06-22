<?php

namespace App\Controllers;

use App\Services\ResponseService;
use App\Services\UserService;
use Firebase\JWT\JWT;

class UserController extends Controller
{
    private $userService;

    function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    function login()
    {
        try {
            // Get data from request
            $data = $this->decodePostData();
            $this->validateInput(["username", "password"], $data);
            
            // Call service method to check username/password
            $user = $this->userService->checkUsernamePassword($data["username"], $data["password"]);
            
            if (!$user) {
                ResponseService::Error("Invalid credentials", 401);
                return;
            }
            
            // Generate JWT token
            $tokenResponse = $this->generateJwt($user);
            
            // Return JSON response
            ResponseService::Send($tokenResponse);
        } catch (\Exception $e) {
            ResponseService::Error("Login failed: " . $e->getMessage(), 500);
        }
    }
    
    private function generateJwt($user)
    {
        $secret_key = $_ENV["JWT_SECRET"];
        $issuer = "sauna_booking_api";
        $audience = "sauna_booking_client";
        $issuedAt = time();
        $notBefore = $issuedAt;
        $expire = $issuedAt + 3600 * 4; // 4 hours
        
        $payload = [
            "iss" => $issuer,
            "aud" => $audience,
            "iat" => $issuedAt,
            "nbf" => $notBefore,
            "exp" => $expire,
            "data" => [
                "id" => $user["id"],
                "username" => $user["username"]
            ]
        ];
        
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
        return ["jwt" => $jwt, "username" => $user["username"]];
    }
}