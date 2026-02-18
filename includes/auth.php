<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(?string $role = null): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: /index.php');
        exit;
    }

    if ($role !== null && $_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo 'Unauthorized';
        exit;
    }
}

function redirectByRole(string $role): void
{
    if ($role === 'admin') {
        header('Location: /admin/dashboard.php');
        exit;
    }

    header('Location: /staff/dashboard.php');
    exit;
}
