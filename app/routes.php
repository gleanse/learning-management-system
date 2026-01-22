<?php

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/GradeController.php';
require_once __DIR__ . '/controllers/StudentController.php';

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

// STUDENT DASHBOARD ROUTES (student only)
if ($page === 'student_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showStudentDashboard();
    exit();
}

if ($page === 'student_grades' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showYearLevels();
    exit();
}

if ($page === 'student_semesters' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showSemesters();
    exit();
}

if ($page === 'student_subjects' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showSubjects();
    exit();
}

if ($page === 'student_grades_view' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showGrades();
    exit();
}

// AUTH ROUTES
if ($page === 'login' && $method === 'GET') {
    // redirect based on role if already logged in
    if (isLoggedIn()) {
        if (isTeacher()) {
            header('Location: index.php?page=teacher_dashboard');
        } elseif (isStudent()) {
            header('Location: index.php?page=student_dashboard');
        } elseif (isAdmin()) {
            header('Location: index.php?page=admin_dashboard');
        } elseif (isSuperAdmin()) {
            header('Location: index.php?page=superadmin_dashboard');
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

    // redirect to role-specific dashboard
    if (isTeacher()) {
        header('Location: index.php?page=teacher_dashboard');
    } elseif (isStudent()) {
        header('Location: index.php?page=student_dashboard');
    } elseif (isAdmin()) {
        header('Location: index.php?page=admin_dashboard');
    } elseif (isSuperAdmin()) {
        header('Location: index.php?page=superadmin_dashboard');
    } else {
        // fallback if role is not recognized
        header('Location: index.php?page=login');
    }
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
    } elseif (isStudent()) {
        header('Location: index.php?page=student_dashboard');
    } elseif (isAdmin()) {
        header('Location: index.php?page=admin_dashboard');
    } elseif (isSuperAdmin()) {
        header('Location: index.php?page=superadmin_dashboard');
    } else {
        header('Location: index.php?page=login');
    }
} else {
    header('Location: index.php?page=login');
}
