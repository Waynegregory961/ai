<?php
$pageTitle = 'Claims';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
require_once __DIR__ . '/../includes/ai.php';

// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verify_csrf();
    $cid    = (int)$_POST['claim_id'];
    $status = $_POST['status'] ?? '';
    $notes  = trim($_POST['admin_notes'] ?? '');
    $allowedStatus = ['Pending Review','Under Review','Approved',
                      'Rejected','More Information Required'];
    if (in_array($status, $allowedStatus, true)) {
        $pdo->prepare("UPDATE claims SET status=?, admin_notes=? WHERE id=?")
            ->execute([$status, $notes, $cid]);
        flash('success', 'Claim updated.');
    }
    header('Location: ' . base_url('admin/claims.php?id=' . $cid));
    exit;
}

// AI summary
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ai_summary'])) {
    verify_csrf();
    $cid = (int)$_POST['claim_id'];
    $st  = $pdo->prepare("SELECT c.*, p.policy_number, p.policy_type
                          FROM claims c JOIN policies p ON p.id=c.policy_id WHERE c.id=?");
    $st->execute([$cid]);
    $c = $st->fetch();
    if ($c) {
        $docs = $pdo->prepare("SELECT original_name FROM claim_documents WHERE claim_id=?");
        $docs->execute([$cid]);
        $docs = $docs->fetchAll(PDO::FETCH_COLUMN);
        $docList = $docs ? implode(', ', $docs) : 'None uploaded';

        $prompt = "You are a claims analyst assistant. Summarize the claim below for a human "
                . "reviewer. Use bullet points. Do NOT approve or reject. Flag missing info.\n\n"
                . "Claim Reference: {$c['claim_reference']}\n"
                . "Policy: {$c['policy_number']} ({$c['policy_type']})\n"
                . "Claim Type: {$c['claim_type']}\n"
                . "Incident Date: {$c['incident_date']}\n"
                . "Estimated Amount: " . number_format($c['estimated_amount'], 2) . "\n"
                . "Documents: {$docList}\n"
                . "Description: {$c['description']}\n";

        $res = call_ai([
            ['role'=>'system','content'=>'You produce concise, factual claim summaries for insurance reviewers. Never decide outcomes.'],
            ['role'=>'user','content'=>$prompt],
        ], 500);

        if ($res['ok']) {
            $pdo->prepare("UPDATE claims SET ai_summary=? WHERE id=?")
                ->execute([$res['text'], $cid]);
            flash('success', 'AI summary generated.');
        } else {
            flash('error', 'AI unavailable: ' . $res['error']);
        }
    }
    header('Location: ' . base_url('admin/claims.php?id=' . $cid));
    exit;
}

$rows = $pdo->query("SELECT c.*, u.name AS customer, p.policy_number
                     FROM claims c
                     JOIN users u ON u.id=c.user_id
                     JOIN policies p ON p.id=c.policy_id
                     ORDER BY c.created_at DESC")->fetchAll();

$selected = null; $documents = [];
if (!empty($_GET['id'])) {
    $st = $pdo->prepare("SELECT c.*, u.name AS customer, u.email, p.policy_number
                         FROM claims c
                         JOIN users u ON u.id=c.user_id
                         JOIN policies p ON p.id=c.policy_id
                         WHERE c.id=?");
    $st->execute([(int)$_GET['id']]);
    $selected = $st->fetch();
    if ($selected) {
        $d = $pdo->prepare("SELECT * FROM claim_documents WHERE claim_id=?");
        $d->execute([$selected['id']]);
        $documents = $d->fetchAll();
    }
}

$badgeMap = [
    'Pending Review'=>'warning','Under Review'=>'info',
    'Approved'=>'success','Rejected'=>'danger','More Information Required'=>'secondary',
];
require_once __DIR__ . '/../includes/header.php';
?>
<?php if ($m = flash('success')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
<?php if ($m = flash('error')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>

<?php if ($selected): ?>
  <a href="<?= e(base_url('admin/claims.php')) ?>" class="btn btn-sm btn-outline-secondary mb-3">
    ← Back to claims
  </a>
  <div class="card shadow-sm mb-3"><div class="card-body">
    <h5>Claim <?= e($selected['claim_reference']) ?>
      <span class="badge bg-<?= $badgeMap[$selected['status']] ?? 'secondary' ?>">
        <?= e($selected['status']) ?></span></h5>
    <p><b>Customer:</b> <?= e($selected['customer']) ?> (<?= e($selected['email']) ?>)</p>
    <p><b>Policy:</b> <?= e($selected['policy_number']) ?></p>
    <p><b>Type:</b> <?= e($selected['claim_type']) ?> ·
       <b>Incident:</b> <?= e($selected['incident_date']) ?> ·
       <b>Amount:</b> <?= number_format($selected['estimated_amount'],2) ?></p>
    <p><b>Description:</b><br><?= nl2br(e($selected['description'])) ?></p>

    <h6 class="mt-3">Documents</h6>
    <?php if (!$documents): ?>
      <p class="text-muted small">None uploaded.</p>
    <?php else: ?>
      <ul>
      <?php foreach ($documents as $doc): ?>
        <li><a href="<?= e(base_url('download-document.php?id=' . $doc['id'])) ?>">
            <?= e($doc['original_name']) ?></a>
            <small class="text-muted">(<?= number_format($doc['file_size']/1024,1) ?> KB)</small>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div></div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card shadow-sm"><div class="card-body">
        <h6>Update Status</h6>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="update_status" value="1">
          <input type="hidden" name="claim_id" value="<?= (int)$selected['id'] ?>">
          <select name="status" class="form-select mb-2" required>
            <?php foreach (array_keys($badgeMap) as $s): ?>
              <option <?= $selected['status']===$s?'selected':'' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
          </select>
          <textarea name="admin_notes" class="form-control mb-2" rows="3"
            placeholder="Internal notes / reason"><?= e($selected['admin_notes']) ?></textarea>
          <button class="btn btn-primary">Save</button>
        </form>
      </div></div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm"><div class="card-body">
        <h6>AI Claim Summary</h6>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="ai_summary" value="1">
          <input type="hidden" name="claim_id" value="<?= (int)$selected['id'] ?>">
          <button class="btn btn-outline-primary">Generate AI Summary</button>
        </form>
        <div class="mt-3 small">
        <?php if ($selected['ai_summary']): ?>
          <div class="alert alert-info mb-0" style="white-space:pre-wrap;">
            <?= e($selected['ai_summary']) ?></div>
          <div class="text-muted mt-1">Advisory only — not a decision.</div>
        <?php else: ?>
          <span class="text-muted">No summary yet.</span>
        <?php endif; ?>
        </div>
      </div></div>
    </div>
  </div>

<?php else: ?>
  <h4 class="mb-3">All Claims</h4>
  <div class="table-responsive">
  <table class="table table-hover align-middle bg-white shadow-sm">
  <thead class="table-light"><tr>
    <th>Reference</th><th>Customer</th><th>Policy</th><th>Amount</th>
    <th>Status</th><th>Date</th><th></th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['claim_reference']) ?></td>
      <td><?= e($r['customer']) ?></td>
      <td><?= e($r['policy_number']) ?></td>
      <td><?= number_format($r['estimated_amount'],2) ?></td>
      <td><span class="badge bg-<?= $badgeMap[$r['status']] ?? 'secondary' ?>">
          <?= e($r['status']) ?></span></td>
      <td><?= e($r['created_at']) ?></td>
      <td><a class="btn btn-sm btn-outline-primary"
             href="<?= e(base_url('admin/claims.php?id='.$r['id'])) ?>">View</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>