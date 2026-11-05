<?php
// includes/header.php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Insurance System';
$user = current_user();
$inDash = strpos($_SERVER['SCRIPT_NAME'], '/customer/') !== false
       || strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | InsureAI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= e(base_url('index.php')) ?>">🛡️ InsureAI</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
      <?php if ($user): ?>
        <li class="nav-item"><span class="navbar-text text-white me-3">
          <?= e($user['name']) ?> (<?= e($user['role']) ?>)
        </span></li>
        <li class="nav-item">
          <a class="nav-link" href="<?= e(base_url('logout.php')) ?>">Logout</a>
        </li>
      <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('login.php')) ?>">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('register.php')) ?>">Register</a></li>
      <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if ($inDash && $user):
      $links = $user['role'] === 'admin'
        ? [
            ['dashboard.php','Dashboard','admin/dashboard.php'],
            ['customers.php','Customers','admin/customers.php'],
            ['policies.php','Policies','admin/policies.php'],
            ['claims.php','Claims','admin/claims.php'],
          ]
        : [
            ['dashboard.php','Dashboard','customer/dashboard.php'],
            ['policies.php','My Policies','customer/policies.php'],
            ['submit-claim.php','Submit Claim','customer/submit-claim.php'],
            ['my-claims.php','My Claims','customer/my-claims.php'],
            ['assistant.php','AI Assistant','customer/assistant.php'],
          ];
?>
<div class="d-flex">
  <aside class="sidebar bg-white border-end p-3">
    <ul class="nav flex-column">
    <?php foreach ($links as [$file,$label,$path]):
      $active = $current === $file ? 'active' : ''; ?>
      <li class="nav-item">
        <a class="nav-link <?= $active ?>" href="<?= e(base_url($path)) ?>"><?= e($label) ?></a>
      </li>
    <?php endforeach; ?>
    </ul>
  </aside>
  <main class="flex-grow-1 p-3 p-md-4">
<?php else: ?>
  <main class="container py-4">
<?php endif; ?>