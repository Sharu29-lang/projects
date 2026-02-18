<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('staff');

$userId = $_SESSION['user']['id'];
$subjects = $pdo->query("SELECT s.id, s.subject_name FROM staff_subjects ss JOIN subjects s ON s.id=ss.subject_id WHERE ss.user_id={$userId}")->fetchAll();
renderHeader('Mark Attendance');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('staff', 'Mark Attendance'); ?>
<main class="col-md-9 col-lg-10 p-4">
    <h4 class="mb-3">Mark Attendance</h4>
    <div class="card shadow-sm p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Subject</label><select id="subjectId" class="form-select"><?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Period</label><input id="periodLabel" class="form-control" placeholder="e.g. Period 1"/></div>
            <div class="col-md-4"><button id="startCam" class="btn btn-success w-100">Start Camera & Mark</button></div>
        </div>
    </div>
    <div class="card shadow-sm p-3">
        <h6>Recognition Output</h6>
        <pre id="resultBox" class="bg-dark text-light p-3 rounded">Waiting...</pre>
    </div>
</main></div></div>
<script>
document.getElementById('startCam').addEventListener('click', async () => {
    const payload = {
        action: 'mark_attendance',
        subject_id: document.getElementById('subjectId').value,
        period_label: document.getElementById('periodLabel').value || 'Period',
        staff_id: <?= (int)$_SESSION['user']['id'] ?>
    };
    const resultBox = document.getElementById('resultBox');
    resultBox.textContent = 'Launching recognition camera...';

    const res = await fetch('/api/face_proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    resultBox.textContent = JSON.stringify(data, null, 2);
});
</script>
<?php renderFooter();
