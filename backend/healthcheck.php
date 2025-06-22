<?php
// Basic health check script

// Test database connection
try {
    $host = getenv('DB_HOST') ?: 'mysql';
    $db = getenv('DB_NAME') ?: 'sauna_booking_system';
    $user = getenv('DB_USER') ?: 'sauna_user';
    $pass = getenv('DB_PASSWORD') ?: 'sauna_password';
    $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2, // Short timeout for health check
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Simple query to verify connection is working
    $stmt = $pdo->query('SELECT 1');
    
    // Check if PHP-FPM is running
    if (function_exists('fpm_get_status')) {
        $status = fpm_get_status();
        if (!$status) {
            throw new Exception("PHP-FPM status check failed");
        }
    }
    
    // Everything is OK
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => 'connected',
        'php_version' => PHP_VERSION
    ]);
    exit(0);
    
} catch (Exception $e) {
    // Report failure
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage()
    ]);
    exit(1);
}