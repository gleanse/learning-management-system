<?php

// hardcoded for now, use env for production later
$host = "localhost";
$db = "lmsDB";
$user = "root";
$pass = "";
$charset = "utf8mb4";

// data source connection string
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $connection = new PDO($dsn, $user, $pass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
