<?php
$pageTitle = 'My Claims';
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');

if ($m = flash('success')) $flash = $m;

$st = $pdo->prepare("SELECT c.*, p.policy_number, p.policy_name
                     FROM claims c JOIN policies p ON p.id=c.policy_id
                     WHERE c.user_id=? ORDER BY c.created_at DESC");
$st->execute([$_SESSION['user_id']]);
$claims = $st->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">My Claims</h4>
<?php if (!empty($flash)): ?>
  <div class="alert alert-success"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!$claims): ?>
  <div class="alert alert-info">No claims submitted yet.</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-hover align-middle bg-white shadow-sm">
<thead class="table-light"><tr>
  <th>Reference</th><th>Policy</th><th>Type</th><th>Amount</th>
  <th>Status</th><th>Submitted</th>
</tr></thead>
<tbody>
<?php foreach ($claims as $c):
  $map = [
    'Pending Review'=>'warning','Under Review'=>'info',
    'Approved'=>'success','Rejected'=>'danger','More Information Required'=>'secondary'
  ];
  $cls = $map[$c['status']] ?? 'secondary';
?>
<tr>
  <td><?= e($c['claim_reference']) ?></td>
  <td><?= e($c['policy_number']) ?></td>
  <td><?= e($c['claim_type']) ?></td>
  <td><?= number_format($c['estimated_amount'], 2) ?></td>
  <td><span class="badge bg-<?= $cls ?>"><?= e($c['status']) ?></span></td>
  <td><?= e($c['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>