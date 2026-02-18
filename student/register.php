<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('admin');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO students(student_id, name, department_id, year_level, section, face_folder) VALUES(:student_id, :name, :department_id, :year_level, :section, :face_folder)');
    $faceFolder = 'dataset/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $_POST['student_id']);
    if (!is_dir(__DIR__ . '/../' . $faceFolder)) {
        mkdir(__DIR__ . '/../' . $faceFolder, 0777, true);
    }

    $stmt->execute([
        'student_id' => $_POST['student_id'],
        'name' => $_POST['name'],
        'department_id' => $_POST['department_id'],
        'year_level' => $_POST['year_level'],
        'section' => $_POST['section'],
        'face_folder' => $faceFolder,
    ]);

    $message = 'Student registered successfully. Use Capture Face to save 20 images.';
}

$departments = $pdo->query('SELECT id, name FROM departments')->fetchAll();
renderHeader('Student Registration');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('admin', 'Manage Students'); ?>
<main class="col-md-9 col-lg-10 p-4">
    <h4 class="mb-3">Student Registration</h4>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" class="card shadow-sm p-3 mb-3">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Student ID</label><input class="form-control" name="student_id" required></div>
            <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
            <div class="col-md-4"><label class="form-label">Department</label><select class="form-select" name="department_id" required><?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Year</label><input type="number" min="1" max="4" class="form-control" name="year_level" required></div>
            <div class="col-md-4"><label class="form-label">Section</label><input class="form-control" name="section" required></div>
            <div class="col-12"><button class="btn btn-primary">Save Student</button></div>
        </div>
    </form>

    <div class="card shadow-sm p-3">
        <h5>Capture Face Dataset</h5>
        <div class="input-group mb-3">
            <input id="captureStudentId" class="form-control" placeholder="Enter Student ID for Capture">
            <button id="captureBtn" class="btn btn-success">Capture Face (20 Images)</button>
        </div>
        <pre id="captureOutput" class="bg-dark text-light p-3 rounded">No capture started.</pre>
    </div>
</main></div></div>
<script>
document.getElementById('captureBtn').addEventListener('click', async () => {
    const studentId = document.getElementById('captureStudentId').value.trim();
    if (!studentId) {
        alert('Please enter Student ID');
        return;
    }
    const output = document.getElementById('captureOutput');
    output.textContent = 'Starting webcam capture...';

    const res = await fetch('/api/face_proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'capture_dataset', student_id: studentId })
    });

    const data = await res.json();
    output.textContent = JSON.stringify(data, null, 2);
});
</script>
<?php renderFooter();
