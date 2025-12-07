<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your DB username
define('DB_PASS', '');      // Change this to your DB password
define('DB_NAME', 'cash_register_db');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>