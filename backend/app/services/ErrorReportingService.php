<?php

namespace App\Services;

class ErrorReportingService
{
    /**
     * Initialize error reporting settings based on environment
     */
    public static function Init()
    {
        $environment = $_ENV["ENVIRONMENT"] ?? "PRODUCTION";
        
        if ($environment === "LOCAL" || $environment === "DEVELOPMENT") {
            // Show all errors in development environment
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            // Hide errors but log them in production environment
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            ini_set('log_errors', 1);
            ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
        }
    }
}