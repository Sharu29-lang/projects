<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$role = $_SESSION['user']['role'] ?? null;
requireLogin($role === 'admin' ? 'admin' : 'staff');

$date = $_GET['date'] ?? date('Y-m-d');
$subjectId = $_GET['subject_id'] ?? '';
$subjectFilter = $subjectId ? ' AND s.id = :subject_id' : '';

$subjects = $pdo->query('SELECT id, subject_name FROM subjects')->fetchAll();

$sql = "SELECT st.student_id, st.name,
SUM(ar.status='present') AS present_count,
COUNT(ar.id) AS total_count,
ROUND((SUM(ar.status='present') / NULLIF(COUNT(ar.id), 0)) * 100, 2) AS attendance_percentage
FROM students st
LEFT JOIN attendance_records ar ON ar.student_id=st.id
LEFT JOIN attendance_sessions s ON s.id=ar.attendance_session_id
WHERE DATE(s.attendance_date)=:date {$subjectFilter}
GROUP BY st.id ORDER BY st.name";
$stmt = $pdo->prepare($sql);
$params = ['date' => $date];
if ($subjectId) { $params['subject_id'] = $subjectId; }
$stmt->execute($params);
$rows = $stmt->fetchAll();

$monthly = $pdo->query("SELECT
SUM(ar.status='present') AS present,
SUM(ar.status='absent') AS absent
FROM attendance_records ar
JOIN attendance_sessions s ON s.id=ar.attendance_session_id
WHERE MONTH(s.attendance_date)=MONTH(CURDATE()) AND YEAR(s.attendance_date)=YEAR(CURDATE())")->fetch() ?: ['present' => 0, 'absent' => 0];

renderHeader('Reports');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar($role, 'Reports'); ?>
<main class="col-md-9 col-lg-10 p-4">
    <h4>Attendance Reports</h4>
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Subject</label><select name="subject_id" class="form-select"><option value="">All</option><?php foreach ($subjects as $subject): ?><option value="<?= $subject['id'] ?>" <?= $subjectId == $subject['id'] ? 'selected' : '' ?>><?= htmlspecialchars($subject['subject_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2 align-self-end"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <table class="table table-bordered table-striped mb-4">
        <tr><th>Roll No</th><th>Name</th><th>Attendance %</th><th>Alert</th></tr>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars((string)($row['attendance_percentage'] ?? 0)) ?>%</td>
                <td><?= ((float)($row['attendance_percentage'] ?? 0) < 75) ? '<span class="badge bg-warning text-dark">Below 75%</span>' : '<span class="badge bg-success">Safe</span>' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="card shadow-sm p-3">
        <h5>Monthly Attendance Pie Chart</h5>
        <div class="canvas-holder"><canvas id="pieChart"></canvas></div>
    </div>
</main></div></div>
<script>
new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: ['Present', 'Absent'],
        datasets: [{ data: [<?= (int)($monthly['present'] ?? 0) ?>, <?= (int)($monthly['absent'] ?? 0) ?>], backgroundColor: ['#198754', '#dc3545'] }]
    }
});
</script>
<?php renderFooter();
