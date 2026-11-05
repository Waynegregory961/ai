<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['role']    = $u['role'];
        header('Location: ' . base_url(
            $u['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php'
        ));
        exit;
    }
    $error = 'Invalid email or password.';
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-3">Sign In</h4>
        <?php if ($m = flash('success')): ?>
          <div class="alert alert-success"><?= e($m) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required></div>
          <button class="btn btn-primary w-100">Login</button>
        </form>
        <p class="mt-3 small">New here? <a href="<?= e(base_url('register.php')) ?>">Register</a></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>