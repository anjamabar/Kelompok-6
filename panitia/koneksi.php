<?php
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "psb_sman6";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");
?>
