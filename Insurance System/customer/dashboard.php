<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$uid = $_SESSION['user_id'];

$polCount = $pdo->prepare("SELECT COUNT(*) FROM policies WHERE user_id=?");
$polCount->execute([$uid]); $polCount = $polCount->fetchColumn();

$claimCount = $pdo->prepare("SELECT COUNT(*) FROM claims WHERE user_id=?");
$claimCount->execute([$uid]); $claimCount = $claimCount->fetchColumn();

$pendingCount = $pdo->prepare("SELECT COUNT(*) FROM claims
    WHERE user_id=? AND status IN ('Pending Review','Under Review','More Information Required')");
$pendingCount->execute([$uid]); $pendingCount = $pendingCount->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Welcome, <?= e($user['name']) ?></h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card summary-card bg-primary text-white">
      <div class="card-body"><h6>Active Policies</h6>
        <div class="fs-2 fw-bold"><?= (int)$polCount ?></div></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card summary-card bg-success text-white">
      <div class="card-body"><h6>Total Claims</h6>
        <div class="fs-2 fw-bold"><?= (int)$claimCount ?></div></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card summary-card bg-warning text-dark">
      <div class="card-body"><h6>Claims In Progress</h6>
        <div class="fs-2 fw-bold"><?= (int)$pendingCount ?></div></div>
    </div>
  </div>
</div>
<div class="mt-4">
  <a href="<?= e(base_url('customer/submit-claim.php')) ?>" class="btn btn-primary">Submit New Claim</a>
  <a href="<?= e(base_url('customer/assistant.php')) ?>" class="btn btn-outline-primary">Ask AI Assistant</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>