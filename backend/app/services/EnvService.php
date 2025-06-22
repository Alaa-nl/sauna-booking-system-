<?php

namespace App\Services;

class EnvService
{
    /**
     * Initialize environment variables
     * Only used if .env file is not loaded automatically
     */
    public static function Init()
    {
        // Load from .env file if it exists
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos($line, '#') === 0) {
                    continue;
                }
                
                // Process key=value lines
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^"(.+)"$/', $value, $matches)) {
                        $value = $matches[1];
                    } elseif (preg_match("/^'(.+)'$/", $value, $matches)) {
                        $value = $matches[1];
                    }
                    
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        } else {
            // Fallback defaults if no .env file
            $_ENV["DB_HOST"] = "mysql";
            $_ENV["DB_NAME"] = "sauna_booking_system";
            $_ENV["DB_USER"] = "sauna_user";
            $_ENV["DB_PASSWORD"] = "sauna_password";
            $_ENV["DB_CHARSET"] = "utf8mb4";
            $_ENV["ENVIRONMENT"] = "LOCAL";
            $_ENV["JWT_SECRET"] = "8RXVjZIyszZEZSyb6h2C6xdNnH3FD2eh";
            $_ENV["JWT_ISSUER"] = "sauna-booking-api";
            $_ENV["JWT_AUDIENCE"] = "sauna-booking-frontend";
            $_ENV["JWT_EXPIRY"] = "1800";
        }
    }
}