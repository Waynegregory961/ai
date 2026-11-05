<?php
$pageTitle = 'Submit Claim';
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
$uid = $_SESSION['user_id'];

$st = $pdo->prepare("SELECT * FROM policies WHERE user_id=? AND status='Active'");
$st->execute([$uid]);
$policies = $st->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $policyId   = (int)($_POST['policy_id'] ?? 0);
    $type       = trim($_POST['claim_type'] ?? '');
    $incident   = $_POST['incident_date'] ?? '';
    $desc       = trim($_POST['description'] ?? '');
    $amount     = (float)($_POST['estimated_amount'] ?? 0);

    // ownership check
    $own = $pdo->prepare("SELECT id FROM policies WHERE id=? AND user_id=?");
    $own->execute([$policyId, $uid]);
    if (!$own->fetch()) $errors[] = 'Invalid policy selection.';
    if ($type === '')            $errors[] = 'Claim type is required.';
    if ($incident === '')        $errors[] = 'Incident date is required.';
    if (strlen($desc) < 10)      $errors[] = 'Description must be at least 10 characters.';
    if ($amount <= 0)            $errors[] = 'Estimated amount must be positive.';

    // file validation
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];
    $file = $_FILES['document'] ?? null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime]))   $errors[] = 'Only JPG, PNG, or PDF allowed.';
        if ($file['size'] > 5 * 1024 * 1024) $errors[] = 'File must be under 5MB.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $ref = generate_reference('CLM');
            $ins = $pdo->prepare("INSERT INTO claims
              (claim_reference,policy_id,user_id,claim_type,incident_date,
               description,estimated_amount,status)
              VALUES (?,?,?,?,?,?,?, 'Pending Review')");
            $ins->execute([$ref,$policyId,$uid,$type,$incident,$desc,$amount]);
            $claimId = $pdo->lastInsertId();

            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $ext  = $allowed[mime_content_type($file['tmp_name'])];
                $dir  = __DIR__ . '/../assets/images/claims/' . $claimId;
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $name = bin2hex(random_bytes(16)) . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
                $rel  = "assets/images/claims/{$claimId}/{$name}";
                $pdo->prepare("INSERT INTO claim_documents
                    (claim_id,file_path,original_name,mime_type,file_size)
                    VALUES (?,?,?,?,?)")
                    ->execute([$claimId,$rel,$file['name'],
                               mime_content_type($dir.'/'.$name),$file['size']]);
            }
            $pdo->commit();
            flash('success', "Claim {$ref} submitted for review.");
            header('Location: ' . base_url('customer/my-claims.php'));
            exit;
        } catch (Exception $ex) {
            $pdo->rollBack();
            $errors[] = 'Submission failed. Try again.';
        }
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">Submit Insurance Claim</h4>
<?php if ($errors): ?>
  <div class="alert alert-danger"><?= implode('<br>', array_map('e',$errors)) ?></div>
<?php endif; ?>
<?php if (!$policies): ?>
  <div class="alert alert-warning">No active policies. Contact admin to assign a policy.</div>
<?php else: ?>
<div class="card shadow-sm"><div class="card-body">
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Policy</label>
    <select name="policy_id" class="form-select" required>
      <option value="">— Select —</option>
      <?php foreach ($policies as $p): ?>
        <option value="<?= (int)$p['id'] ?>">
          <?= e($p['policy_number'].' – '.$p['policy_name']) ?>
        </option>
      <?php endforeach; ?>
    </select></div>
  <div class="mb-3"><label class="form-label">Claim Type</label>
    <input name="claim_type" class="form-control" required
           placeholder="e.g. Accident, Hospitalization, Theft"></div>
  <div class="mb-3"><label class="form-label">Incident Date</label>
    <input type="date" name="incident_date" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="4" required></textarea></div>
  <div class="mb-3"><label class="form-label">Estimated Amount</label>
    <input type="number" step="0.01" min="0" name="estimated_amount"
           class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Supporting Document (JPG/PNG/PDF ≤5MB)</label>
    <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf"></div>
  <button class="btn btn-primary">Submit Claim</button>
</form>
</div></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>