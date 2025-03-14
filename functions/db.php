<?php
require_once __DIR__ . '/../config.php';

function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        
        // Set PDO to throw exceptions on error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Use prepared statements by default
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        return $pdo;
    } catch (PDOException $e) {
        // Log error and show user-friendly message
        error_log("Connection failed: " . $e->getMessage());
        die("Sorry, there was a problem connecting to the database. Please try again later.");
    }
}