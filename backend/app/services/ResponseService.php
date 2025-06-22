<?php

namespace App\Services;

class ResponseService
{
    /**
     * Send a JSON response with appropriate header and status
     * 
     * @param mixed $data Data to send as JSON
     * @param int $status HTTP status code (default: 200)
     */
    public static function Send($data, $status = 200)
    {
        self::SetCorsHeaders();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    /**
     * Send JSON error response with status
     * 
     * @param string $error Error message (default: "An error occurred")
     * @param int $status HTTP status code (default: 500)
     */
    public static function Error($error = "An error occurred", $status = 500)
    {
        self::Send(["error" => $error], $status);
    }

    /**
     * Set CORS headers for cross-origin requests
     * Following the pattern from Lecture 3B-2
     */
    public static function SetCorsHeaders()
    {
        // Allow specific origin as per course pattern
        header("Access-Control-Allow-Origin: http://localhost:5173");
        
        // Allow specific methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        
        // Allow specific headers
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}