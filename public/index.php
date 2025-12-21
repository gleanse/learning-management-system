<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// runtime error handling based on app env variablw prod for deployed and dev for local ongoing development
$prod = ($_SERVER['ENV_APP'] ?? 'dev') === 'prod';

// if on production turn off the error logs
if ($prod) {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// force HTTPS in production (if deployed)
if ($prod && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// session security configurations
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   $prod ? '1' : '0');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_lifetime', '0');

session_start();

// security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer-when-downgrade');

require_once __DIR__ . '/../app/routes.php';
