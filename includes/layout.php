<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function renderHeader(string $title): void
{
    $username = $_SESSION['user']['full_name'] ?? 'User';
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$title}</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="/assets/css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
        <a class="navbar-brand fw-semibold" href="#">Smart Attendance</a>
        <div class="ms-auto text-white me-3">{$username}</div>
        <a href="/logout.php" class="btn btn-sm btn-light">Logout</a>
    </nav>
    HTML;
}

function renderSidebar(string $role, string $active): void
{
    $adminMenu = [
        'Dashboard' => '/admin/dashboard.php',
        'Manage Students' => '/student/register.php',
        'Manage Staff' => '/admin/manage_staff.php',
        'Subjects' => '/admin/subjects.php',
        'Assign Subjects' => '/admin/assign_subjects.php',
        'Reports' => '/admin/reports.php',
        'Logout' => '/logout.php',
    ];

    $staffMenu = [
        'Dashboard' => '/staff/dashboard.php',
        'Mark Attendance' => '/staff/mark_attendance.php',
        'View Attendance' => '/staff/attendance_table.php',
        'Reports' => '/staff/reports.php',
        'Logout' => '/logout.php',
    ];

    $menu = $role === 'admin' ? $adminMenu : $staffMenu;

    echo '<aside class="col-md-3 col-lg-2 sidebar p-3"><div class="nav flex-column gap-1">';
    foreach ($menu as $label => $link) {
        $isActive = $label === $active ? 'active' : '';
        echo "<a class='nav-link {$isActive} px-3 py-2' href='{$link}'>{$label}</a>";
    }
    echo '</div></aside>';
}

function renderFooter(): void
{
    echo <<<HTML
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    HTML;
}
