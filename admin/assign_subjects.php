<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT IGNORE INTO staff_subjects(user_id, subject_id) VALUES(:uid, :sid)');
    $stmt->execute(['uid' => $_POST['user_id'], 'sid' => $_POST['subject_id']]);
}

$staff = $pdo->query("SELECT u.id, u.full_name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='staff'")->fetchAll();
$subjects = $pdo->query('SELECT id, subject_name FROM subjects')->fetchAll();
$assignments = $pdo->query('SELECT u.full_name, s.subject_name FROM staff_subjects ss JOIN users u ON u.id=ss.user_id JOIN subjects s ON s.id=ss.subject_id')->fetchAll();
renderHeader('Assign Subjects');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('admin', 'Assign Subjects'); ?>
<main class="col-md-9 col-lg-10 p-4"><h4>Assign Subjects</h4>
<form method="post" class="row g-2 mb-3">
<div class="col"><select class="form-select" name="user_id" required><option value="">Staff</option><?php foreach ($staff as $u): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col"><select class="form-select" name="subject_id" required><option value="">Subject</option><?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-auto"><button class="btn btn-primary">Assign</button></div>
</form>
<table class="table table-striped"><tr><th>Staff</th><th>Subject</th></tr><?php foreach ($assignments as $a): ?><tr><td><?= htmlspecialchars($a['full_name']) ?></td><td><?= htmlspecialchars($a['subject_name']) ?></td></tr><?php endforeach; ?></table>
</main></div></div><?php renderFooter();
