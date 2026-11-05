    <?php
$pageTitle = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-5 fw-bold text-primary">AI-Powered Insurance Management</h1>
  <p class="lead text-muted mt-3">
    Manage policies, submit claims, and get instant help from our AI assistant.
  </p>
  <div class="mt-4">
    <a href="<?= e(base_url('login.php')) ?>" class="btn btn-primary btn-lg me-2">Login</a>
    <a href="<?= e(base_url('register.php')) ?>" class="btn btn-outline-primary btn-lg">Create Account</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>