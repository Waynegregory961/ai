<?php
$pageTitle = 'Customers';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$rows = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM policies p WHERE p.user_id=u.id) AS policies,
    (SELECT COUNT(*) FROM claims c WHERE c.user_id=u.id) AS claims
    FROM users u WHERE role='customer' ORDER BY u.created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Registered Customers</h4>
<div class="table-responsive">
<table class="table table-hover align-middle bg-white shadow-sm">
<thead class="table-light"><tr>
  <th>ID</th><th>Name</th><th>Email</th><th>Phone</th>
  <th>Policies</th><th>Claims</th><th>Joined</th>
</tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><?= e($r['name']) ?></td>
  <td><?= e($r['email']) ?></td>
  <td><?= e($r['phone']) ?></td>
  <td><?= (int)$r['policies'] ?></td>
  <td><?= (int)$r['claims'] ?></td>
  <td><?= e($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>