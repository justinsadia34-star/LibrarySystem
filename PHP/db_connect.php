<?php
$servername = "localhost";  // Host name
$username = "root";         // Default username for phpMyAdmin
$password = "";             // Leave empty if using XAMPP/WAMP default
$dbname = "library";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
