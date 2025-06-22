<?php
// Initialize environment
require_once __DIR__ . '/../services/EnvService.php';
\App\Services\EnvService::Init();

echo "<h1>Database Connection Test</h1>";

// Display environment variables for debugging
echo "<h2>Environment Variables:</h2>";
echo "<pre>";
echo "DB_HOST: " . ($_ENV["DB_HOST"] ?? 'Not set') . "\n";
echo "DB_NAME: " . ($_ENV["DB_NAME"] ?? 'Not set') . "\n";
echo "DB_USER: " . ($_ENV["DB_USER"] ?? 'Not set') . "\n";
echo "DB_PASSWORD: " . ($_ENV["DB_PASSWORD"] ?? 'Not set') . "\n";
echo "DB_CHARSET: " . ($_ENV["DB_CHARSET"] ?? 'Not set') . "\n";
echo "</pre>";

// Attempt database connection
try {
    $host = $_ENV["DB_HOST"];
    $db = $_ENV["DB_NAME"];
    $user = $_ENV["DB_USER"];
    $pass = $_ENV["DB_PASSWORD"];
    $charset = $_ENV["DB_CHARSET"];

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    echo "<h2>Attempting to connect to database...</h2>";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color:green;font-weight:bold;'>Connection successful!</p>";
    
    // Check if tables exist
    echo "<h2>Checking tables:</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange;'>No tables found in database. Please check if SQL initialization script ran correctly.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;font-weight:bold;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>