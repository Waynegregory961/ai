<?php
$pageTitle = 'My Policies';
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');

$st = $pdo->prepare("SELECT * FROM policies WHERE user_id=? ORDER BY created_at DESC");
$st->execute([$_SESSION['user_id']]);
$policies = $st->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">My Insurance Policies</h4>
<?php if (!$policies): ?>
  <div class="alert alert-info">You have no policies assigned yet.</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table table-hover align-middle bg-white shadow-sm">
    <thead class="table-light">
      <tr><th>Policy #</th><th>Name</th><th>Type</th><th>Premium</th>
          <th>Coverage</th><th>Expiry</th><th>Status</th></tr>
    </thead>
    <tbody>
    <?php foreach ($policies as $p): ?>
      <tr>
        <td><?= e($p['policy_number']) ?></td>
        <td><?= e($p['policy_name']) ?></td>
        <td><?= e($p['policy_type']) ?></td>
        <td><?= number_format($p['premium_amount'], 2) ?></td>
        <td><?= number_format($p['coverage_amount'], 2) ?></td>
        <td><?= e($p['expiry_date']) ?></td>
        <td><span class="badge bg-<?= $p['status']==='Active'?'success':'secondary' ?>">
            <?= e($p['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>