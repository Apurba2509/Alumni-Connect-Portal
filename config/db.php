<?php
// =======================================================
// BCAC591: Database Connection Configuration (PDO)
// =======================================================

// Database credentials for local XAMPP
$db_host = '127.0.0.1';
$db_name = 'alumni-connect-portal';
$db_user = 'root';
$db_pass = '';
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// PDO options for security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements (prevents SQL injection)
];

try {
    // Create and expose the $pdo connection object
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In production/lab, display a clean error message
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}
