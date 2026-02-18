<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('admin');

$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$today = date('Y-m-d');
$presentToday = (int) $pdo->query("SELECT COUNT(*) FROM attendance_records ar JOIN attendance_sessions s ON s.id = ar.attendance_session_id WHERE s.attendance_date = '{$today}' AND ar.status='present'")->fetchColumn();
$absentToday = (int) $pdo->query("SELECT COUNT(*) FROM attendance_records ar JOIN attendance_sessions s ON s.id = ar.attendance_session_id WHERE s.attendance_date = '{$today}' AND ar.status='absent'")->fetchColumn();
$attendancePct = $totalStudents > 0 ? round(($presentToday / max(($presentToday + $absentToday), 1)) * 100, 2) : 0;

$weeklyQuery = $pdo->query("SELECT DATE(s.attendance_date) AS day, SUM(ar.status='present') AS present_count
FROM attendance_sessions s
LEFT JOIN attendance_records ar ON ar.attendance_session_id = s.id
WHERE s.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY DATE(s.attendance_date)
ORDER BY day ASC");
$weekly = $weeklyQuery->fetchAll();

renderHeader('Admin Dashboard');
?>
<div class="container-fluid">
    <div class="row">
        <?php renderSidebar('admin', 'Dashboard'); ?>
        <main class="col-md-9 col-lg-10 p-4">
            <h3 class="mb-4">Admin Dashboard</h3>
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Total Students</h6><h3><?= $totalStudents ?></h3></div></div></div>
                <div class="col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Present Today</h6><h3><?= $presentToday ?></h3></div></div></div>
                <div class="col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Absent Today</h6><h3><?= $absentToday ?></h3></div></div></div>
                <div class="col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><h6>Attendance %</h6><h3><?= $attendancePct ?>%</h3></div></div></div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Weekly Attendance Chart</h5>
                    <div class="canvas-holder"><canvas id="weeklyChart"></canvas></div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
const labels = <?= json_encode(array_column($weekly, 'day')) ?>;
const data = <?= json_encode(array_map('intval', array_column($weekly, 'present_count'))) ?>;
new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Present Students', data, tension: 0.3, fill: false, borderColor: '#0d6efd' }] },
});
</script>
<?php renderFooter();
