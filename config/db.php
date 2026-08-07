<?php
// config/db.php - Centralized Database Connection

define('DB_HOST', 'localhost');
define('DB_NAME', 'movie_booking_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is empty

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Production style error handling
    error_log("Database Connection Error: " . $e->getMessage());
    die("System error: Unable to connect to the database. Please try again later.");
}
?>