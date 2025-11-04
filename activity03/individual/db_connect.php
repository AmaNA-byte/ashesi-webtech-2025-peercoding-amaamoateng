<?php
/**
 * Database Connection
 * Simple mysqli connection for beginners
 */

// Database settings
$servername = "localhost";
$username   = "root";        // Default for XAMPP
$password   = "";            // Empty for XAMPP, "root" for MAMP
$dbname     = "school_db";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
