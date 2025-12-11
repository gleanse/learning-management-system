<?php

require_once __DIR__ . '/controllers/AuthController.php';

$page = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

if ($page === 'login' && $method === 'GET') {
    $controller = new AuthController();
    $controller->showLoginForm();
    exit();
}

if ($page === 'login' && $method === 'POST') {
    $controller = new AuthController();
    $controller->processLogin();
    exit();
}

if ($page === 'dashboard' && $method === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit();
    }

    // NOTE: sample login account user types role recognition for prototype, might remove later
    $userTypes = [1 => 'Student', 2 => 'Teacher', 3 => 'Admin', 4 => 'SuperAdmin'];
    $userTypeName = $userTypes[$_SESSION['user_type']] ?? 'Unknown';
    $userName = $_SESSION['user_name'];
    $userEmail = $_SESSION['user_email'];

    require __DIR__ . '/views/dashboard.php';
    exit();
}

if ($page === 'logout' && $method === 'GET') {
    $controller = new AuthController();
    $controller->logout();
    exit();
}

header('Location: index.php?page=login');
