<?php
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$totCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totPolicies  = $pdo->query("SELECT COUNT(*) FROM policies")->fetchColumn();
$totClaims    = $pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn();
$pending      = $pdo->query("SELECT COUNT(*) FROM claims WHERE status='Pending Review'")->fetchColumn();
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-4">Administrator Dashboard</h4>
<div class="row g-3">
  <div class="col-md-3"><div class="card text-white bg-primary"><div class="card-body">
    <h6>Customers</h6><div class="fs-2 fw-bold"><?= (int)$totCustomers ?></div></div></div></div>
  <div class="col-md-3"><div class="card text-white bg-success"><div class="card-body">
    <h6>Policies</h6><div class="fs-2 fw-bold"><?= (int)$totPolicies ?></div></div></div></div>
  <div class="col-md-3"><div class="card text-white bg-dark"><div class="card-body">
    <h6>Total Claims</h6><div class="fs-2 fw-bold"><?= (int)$totClaims ?></div></div></div></div>
  <div class="col-md-3"><div class="card text-dark bg-warning"><div class="card-body">
    <h6>Pending Review</h6><div class="fs-2 fw-bold"><?= (int)$pending ?></div></div></div></div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>