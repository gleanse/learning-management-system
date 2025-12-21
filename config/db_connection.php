<?php

require_once __DIR__ . '/../vendor/autoload.php';

// for environment variables using vlucas/phpdotenv via composer
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$host = $_SERVER['ENV_DB_HOST'];
$db = $_SERVER['ENV_DB_NAME'];
$user = $_SERVER['ENV_DB_USER'];
$pass = $_SERVER['ENV_DB_PASS'];
$charset = "utf8mb4";

// data source connection string
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $connection = new PDO($dsn, $user, $pass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (($_SERVER['ENV_APP'] ?? 'dev') === 'prod') {
        http_response_code(503);
        // TODO: create generic error page in views with design
        exit;
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}
