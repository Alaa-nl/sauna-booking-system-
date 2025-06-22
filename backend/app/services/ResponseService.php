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
     * Following the pattern from Lecture 3B-2 with improvements
     */
    public static function SetCorsHeaders()
    {
        // Check the request origin
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // List of allowed origins - expanded to include common development environments
        $allowed_origins = [
            'http://localhost',          // Nginx proxy
            'http://localhost:5173',     // Vite dev server
            'http://localhost:5174',     // Vite preview server
            'http://frontend:5173',      // Docker service name
            'http://127.0.0.1',          // Alternative localhost
            'http://127.0.0.1:5173',     // Alternative localhost with port
            'http://127.0.0.1:5174'      // Alternative localhost with port
        ];
        
        // Set the appropriate CORS headers based on origin
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // If origin not in whitelist, use a safe default
            header("Access-Control-Allow-Origin: http://localhost");
        }
        
        // Always enable credentials
        header("Access-Control-Allow-Credentials: true");
        
        // Allow specific methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        
        // Allow specific headers
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
        
        // Add cache duration for preflight requests (browser caching)
        header("Access-Control-Max-Age: 86400"); // 24 hours
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }
    }
}