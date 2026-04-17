<?php
/**
 * ============================================
 * DATABASE CONFIGURATION
 * Online Food Delivery System
 * ============================================
 * This file handles the MySQL database connection.
 * Included in every page that needs DB access.
 * ============================================
 */

// Database credentials
$host = "localhost";
$username = "root";
$password = "";  // Default XAMPP has no password
$database = "online_food_delivery";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("❌ Database Connection Failed: " . mysqli_connect_error());
}

// Set character set to UTF-8 for proper encoding
mysqli_set_charset($conn, "utf8mb4");
?>
