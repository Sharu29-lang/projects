<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
requireLogin('staff');

header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="attendance_report.html"');

$records = $pdo->query("SELECT st.student_id, st.name, ar.status, ar.marked_at
FROM attendance_records ar
JOIN students st ON st.id=ar.student_id
ORDER BY ar.id DESC")->fetchAll();

echo '<h2>Attendance Report</h2><table border="1" cellspacing="0" cellpadding="5"><tr><th>Roll No</th><th>Name</th><th>Status</th><th>Time</th></tr>';
foreach ($records as $r) {
    echo '<tr><td>'.htmlspecialchars($r['student_id']).'</td><td>'.htmlspecialchars($r['name']).'</td><td>'.htmlspecialchars($r['status']).'</td><td>'.htmlspecialchars((string)$r['marked_at']).'</td></tr>';
}
echo '</table>';
