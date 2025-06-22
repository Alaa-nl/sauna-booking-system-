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
            $host = $_ENV["DB_HOST"];
            $db = $_ENV["DB_NAME"];
            $user = $_ENV["DB_USER"];
            $pass = $_ENV["DB_PASSWORD"];
            $charset = $_ENV["DB_CHARSET"];

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ];

            try {
                self::$pdo = new \PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                throw new \Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }
}