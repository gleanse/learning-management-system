<?php

require_once __DIR__ . '/models/AuthSession.php';

function checkAuth()
{
    $page = $_GET['page'] ?? 'login';

    $publicPages = [
        'login',
        'logout',
        'forgot_password',
        'forgot_password_send',
        'forgot_password_verify',
        'forgot_password_reset',
        'forgot_password_resend',
        'register',
    ];

    if (in_array($page, $publicPages)) return;

    // not logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit();
    }

    $model = new AuthSession();
    $user = $model->getUserRoleAndStatus($_SESSION['user_id']);

    // user deleted
    if (!$user) {
        session_destroy();
        header('Location: index.php?page=login&reason=not_found');
        exit();
    }

    // account suspended or deactivated
    if ($user['status'] !== 'active') {
        session_destroy();
        header('Location: index.php?page=login&reason=suspended');
        exit();
    }

    // role changed by superadmin
    if ($user['role'] !== $_SESSION['user_role']) {
        session_destroy();
        header('Location: index.php?page=login&reason=role_changed');
        exit();
    }

    // sync session with DB
    $_SESSION['user_role'] = $user['role'];
}
