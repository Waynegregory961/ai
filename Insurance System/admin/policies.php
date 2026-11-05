<?php
$pageTitle = 'Policies';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $uid = (int)($_POST['user_id'] ?? 0);
    $pn  = trim($_POST['policy_name'] ?? '');
    $pt  = $_POST['policy_type'] ?? '';
    $desc= trim($_POST['description'] ?? '');
    $pr  = (float)($_POST['premium_amount'] ?? 0);
    $cv  = (float)($_POST['coverage_amount'] ?? 0);
    $sd  = $_POST['start_date'] ?? '';
    $ed  = $_POST['expiry_date'] ?? '';

    if (!$uid || !$pn || !in_array($pt, ['Motor','Health','Life']) || !$sd || !$ed || $pr<=0 || $cv<=0) {
        $errors[] = 'All fields required and valid.';
    } else {
        $num = 'POL-' . strtoupper(substr($pt,0,3)) . '-' . rand(1000,9999);
        $pdo->prepare("INSERT INTO policies
          (policy_number,user_id,policy_name,policy_type,description,
           premium_amount,coverage_amount,start_date,expiry_date,status)
          VALUES (?,?,?,?,?,?,?,?,?, 'Active')")
          ->execute([$num,$uid,$pn,$pt,$desc,$pr,$cv,$sd,$ed]);
        flash('success', "Policy {$num} created.");
        header('Location: ' . base_url('admin/policies.php'));
        exit;
    }
}

$customers = $pdo->query("SELECT id,name,email FROM users WHERE role='customer' ORDER BY name")->fetchAll();
$rows = $pdo->query("SELECT p.*, u.name AS owner
                     FROM policies p JOIN users u ON u.id=p.user_id
                     ORDER BY p.created_at DESC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', array_map('e',$errors)) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm"><div class="card-body">
      <h5>Create Policy</h5>
      <form method="post">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label">Customer</label>
          <select name="user_id" class="form-select" required>
            <option value="">— Select —</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= e($c['name'].' ('.$c['email'].')') ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="mb-2"><label class="form-label">Policy Name</label>
          <input name="policy_name" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Type</label>
          <select name="policy_type" class="form-select" required>
            <option>Motor</option><option>Health</option><option>Life</option>
          </select></div>
        <div class="mb-2"><label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Premium</label>
            <input type="number" step="0.01" name="premium_amount" class="form-control" required></div>
          <div class="col-6"><label class="form-label">Coverage</label>
            <input type="number" step="0.01" name="coverage_amount" class="form-control" required></div>
        </div>
        <div class="row g-2 mt-1">
          <div class="col-6"><label class="form-label">Start</label>
            <input type="date" name="start_date" class="form-control" required></div>
          <div class="col-6"><label class="form-label">Expiry</label>
            <input type="date" name="expiry_date" class="form-control" required></div>
        </div>
        <button class="btn btn-primary mt-3 w-100">Create Policy</button>
      </form>
    </div></div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
      <h5>All Policies</h5>
      <div class="table-responsive">
      <table class="table table-sm align-middle">
      <thead><tr><th>#</th><th>Owner</th><th>Type</th><th>Premium</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $p): ?>
        <tr>
          <td><?= e($p['policy_number']) ?><br><small class="text-muted"><?= e($p['policy_name']) ?></small></td>
          <td><?= e($p['owner']) ?></td>
          <td><?= e($p['policy_type']) ?></td>
          <td><?= number_format($p['premium_amount'],2) ?></td>
          <td><span class="badge bg-<?= $p['status']==='Active'?'success':'secondary' ?>">
              <?= e($p['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>