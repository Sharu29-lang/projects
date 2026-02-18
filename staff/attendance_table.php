<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('staff');

if (isset($_POST['record_id'], $_POST['status'])) {
    $stmt = $pdo->prepare('UPDATE attendance_records SET status=:status WHERE id=:id');
    $stmt->execute(['status' => $_POST['status'], 'id' => $_POST['record_id']]);
}

$records = $pdo->query("SELECT ar.id, st.student_id AS roll_no, st.name, ar.status, ar.marked_at
FROM attendance_records ar
JOIN students st ON st.id=ar.student_id
ORDER BY ar.id DESC")->fetchAll();
renderHeader('Attendance Table');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('staff', 'View Attendance'); ?>
<main class="col-md-9 col-lg-10 p-4">
    <div class="d-flex justify-content-between mb-3"><h4>Attendance Table</h4><a class="btn btn-danger" href="/staff/export_pdf.php" target="_blank">Export PDF</a></div>
    <table class="table table-bordered table-striped">
        <tr><th>Roll No</th><th>Name</th><th>Status</th><th>Time</th><th>Edit</th></tr>
        <?php foreach ($records as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['roll_no']) ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><span class="badge <?= $r['status']==='present' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                <td><?= htmlspecialchars($r['marked_at'] ?? '-') ?></td>
                <td>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="record_id" value="<?= $r['id'] ?>">
                        <select name="status" class="form-select form-select-sm">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</main></div></div>
<?php renderFooter();
