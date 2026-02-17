<?php
// config/database.php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'lmsdb';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

ob_start();
