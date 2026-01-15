<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// runtime error handling based on app env variablw prod for deployed and dev for local ongoing development.
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
// 5 hours inactive sessions eligable for gc
ini_set('session.gc_maxlifetime', 18000);
// 5% cleanup triggering garbage collection every session start
ini_set('session.gc_probability', 5);
ini_set('session.gc_divisor', 100);

session_start();

// check if user is not logged in but has remember_token cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../app/models/User.php';
    
    $user_model = new User();
    // hash the token from cookie to match database
    $tokenHash = hash('sha256', $_COOKIE['remember_token']);
    $user = $user_model->getUserByRememberToken($tokenHash);
    
    if ($user) {
        // if valid token auto login
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_firstname'] = $user['first_name'];
        $_SESSION['user_middlename'] = $user['middle_name'];
        $_SESSION['user_lastname'] = $user['last_name'];
        
        // redirect to dashboard
        header('Location: index.php?page=dashboard');
        exit();
    } else {
        // invalid or expired token delete cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

// security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer-when-downgrade');

require_once __DIR__ . '/../app/routes.php';
