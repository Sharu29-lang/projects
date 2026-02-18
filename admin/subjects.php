<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO subjects(subject_code, subject_name) VALUES(:code, :name)');
    $stmt->execute(['code' => $_POST['subject_code'], 'name' => $_POST['subject_name']]);
}
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY created_at DESC')->fetchAll();
renderHeader('Subjects');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('admin', 'Subjects'); ?>
<main class="col-md-9 col-lg-10 p-4"><h4>Subjects</h4>
<form method="post" class="row g-2 mb-3"><div class="col"><input class="form-control" name="subject_code" placeholder="Code" required></div><div class="col"><input class="form-control" name="subject_name" placeholder="Subject Name" required></div><div class="col-auto"><button class="btn btn-primary">Add</button></div></form>
<table class="table table-bordered"><tr><th>Code</th><th>Name</th></tr><?php foreach ($subjects as $subject): ?><tr><td><?= htmlspecialchars($subject['subject_code']) ?></td><td><?= htmlspecialchars($subject['subject_name']) ?></td></tr><?php endforeach; ?></table>
</main></div></div><?php renderFooter();
