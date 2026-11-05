<?php
$pageTitle = 'Register';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password']   ?? '';
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '' || $pass === '') $errors[] = 'All fields are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) $errors[] = 'Email already registered.';
    }
    if (!$errors) {
        $st = $pdo->prepare("INSERT INTO users (name,email,password,role,phone)
                             VALUES (?,?,?, 'customer', ?)");
        $st->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $phone]);
        flash('success', 'Account created. Please log in.');
        header('Location: ' . base_url('login.php'));
        exit;
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-3">Create an account</h4>
        <?php if ($errors): ?>
          <div class="alert alert-danger"><?= implode('<br>', array_map('e', $errors)) ?></div>
        <?php endif; ?>
        <form method="post" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3"><label class="form-label">Full Name</label>
            <input name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Phone</label>
            <input name="phone" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6"></div>
          <button class="btn btn-primary w-100">Register</button>
        </form>
        <p class="mt-3 small">Already registered? <a href="<?= e(base_url('login.php')) ?>">Login</a></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>