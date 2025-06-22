<?php

namespace App\Models;

/**
 * Base model for database operations
 */
class Model
{
    protected static $pdo;

    function __construct()
    {
        if (!self::$pdo) {
            // Get database connection parameters from environment variables
            $host = $_ENV["DB_HOST"] ?? 'mysql';
            $db = $_ENV["DB_NAME"] ?? 'sauna_booking_system';
            $user = $_ENV["DB_USER"] ?? 'sauna_user';
            $pass = $_ENV["DB_PASSWORD"] ?? 'sauna_password';
            $charset = $_ENV["DB_CHARSET"] ?? 'utf8mb4';

            // Log connection attempt
            error_log("Connecting to MySQL database: host=$host, db=$db, user=$user");

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                // Add connection timeout
                \PDO::ATTR_TIMEOUT            => 5,
            ];

            try {
                // Attempt database connection with retry logic
                $retries = 3;
                $retry_interval = 2; // seconds
                $connected = false;
                
                while (!$connected && $retries > 0) {
                    try {
                        self::$pdo = new \PDO($dsn, $user, $pass, $options);
                        $connected = true;
                        error_log("Successfully connected to database");
                    } catch (\PDOException $e) {
                        $retries--;
                        if ($retries <= 0) {
                            error_log("All database connection attempts failed: " . $e->getMessage());
                            throw $e;
                        }
                        error_log("Database connection attempt failed: " . $e->getMessage() . ". Retrying in {$retry_interval} seconds...");
                        sleep($retry_interval);
                    }
                }
            } catch (\PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }
}