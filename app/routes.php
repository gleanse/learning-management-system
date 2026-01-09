<?php

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/GradeController.php';

$page = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

// HELPER functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isTeacher()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher';
}

function isStudent()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isSuperAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin';
}

// TEACHER DASHBOARD ROUTES (teacher only)
if ($page === 'teacher_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showTeacherDashboard();
    exit();
}

// GRADING MANAGEMENT ROUTES (teacher only)
if ($page === 'grading' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showYearLevels();
    exit();
}

if ($page === 'grading_subjects' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showSubjects();
    exit();
}

if ($page === 'grading_sections' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showSections();
    exit();
}

if ($page === 'grading_students' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showStudentList();
    exit();
}

if ($page === 'save_grade' && $method === 'POST') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->processSaveGrade();
    exit();
}

// AUTH ROUTES
if ($page === 'login' && $method === 'GET') {
    // redirect based on role if already logged in
    if (isLoggedIn()) {
        if (isTeacher()) {
            header('Location: index.php?page=teacher_dashboard');
        } else {
            header('Location: index.php?page=dashboard');
        }
        exit();
    }

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
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit();
    }

    // redirect teacher to their dashboard
    if (isTeacher()) {
        header('Location: index.php?page=teacher_dashboard');
        exit();
    }

    require __DIR__ . '/views/dashboard.php';
    exit();
}

if ($page === 'logout' && $method === 'GET') {
    $controller = new AuthController();
    $controller->logout();
    exit();
}

if ($page === 'register' && $method === 'GET') {
    $controller = new AuthController();
    $controller->showRegisterForm();
    exit();
}

if ($page === 'register' && $method === 'POST') {
    $controller = new AuthController();
    $controller->processRegister();
    exit();
}

// 404 fallback
if (isLoggedIn()) {
    if (isTeacher()) {
        header('Location: index.php?page=teacher_dashboard');
    } else {
        header('Location: index.php?page=dashboard');
    }
} else {
    header('Location: index.php?page=login');
}