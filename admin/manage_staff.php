<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin('admin');
$staff = $pdo->query("SELECT u.username, u.full_name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.name='staff'")->fetchAll();
renderHeader('Manage Staff');
?>
<div class="container-fluid"><div class="row"><?php renderSidebar('admin', 'Manage Staff'); ?>
<main class="col-md-9 col-lg-10 p-4"><h4>Manage Staff</h4><table class="table table-striped"><tr><th>Username</th><th>Name</th></tr><?php foreach ($staff as $s): ?><tr><td><?= htmlspecialchars($s['username']) ?></td><td><?= htmlspecialchars($s['full_name']) ?></td></tr><?php endforeach; ?></table></main>
</div></div><?php renderFooter();
