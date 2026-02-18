<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('staff');

$userId = $_SESSION['user']['id'];
$subjectCount = (int) $pdo->query("SELECT COUNT(*) FROM staff_subjects WHERE user_id={$userId}")->fetchColumn();
$sessionCount = (int) $pdo->query("SELECT COUNT(*) FROM attendance_sessions WHERE staff_id={$userId}")->fetchColumn();
$todaySessions = (int) $pdo->query("SELECT COUNT(*) FROM attendance_sessions WHERE staff_id={$userId} AND attendance_date=CURDATE()")->fetchColumn();

renderHeader('Staff Dashboard');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('staff', 'Dashboard'); ?>
<main class="col-md-9 col-lg-10 p-4">
    <h3 class="mb-4">Staff Dashboard</h3>
    <div class="row g-3">
        <div class="col-md-4"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Assigned Subjects</h6><h3><?= $subjectCount ?></h3></div></div></div>
        <div class="col-md-4"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Attendance Sessions</h6><h3><?= $sessionCount ?></h3></div></div></div>
        <div class="col-md-4"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Today Sessions</h6><h3><?= $todaySessions ?></h3></div></div></div>
    </div>
</main></div></div>
<?php renderFooter();
