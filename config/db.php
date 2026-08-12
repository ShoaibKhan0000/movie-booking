<?php

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_NAME') ?: 'movie_booking_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$appDebug = filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);

try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
    $pdo->exec("SET SESSION sql_mode='STRICT_ALL_TABLES'");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    $message = $appDebug ? "Database error: " . $e->getMessage() : "System error: Unable to connect to the database. Please try again later.";
    die($message);
}