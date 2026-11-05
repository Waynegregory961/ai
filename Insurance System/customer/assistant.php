<?php
$pageTitle = 'AI Assistant';
require_once __DIR__ . '/../includes/auth.php';
require_role('customer');
require_once __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3">AI Insurance Assistant</h4>
<p class="text-muted">Ask general questions about insurance. The AI will not approve or reject claims.</p>

<div class="card shadow-sm"><div class="card-body">
  <div id="chat-log" class="chat-log mb-3"></div>
  <form id="chat-form" class="d-flex gap-2">
    <input id="chat-input" class="form-control" placeholder="Type your question…" autocomplete="off">
    <button class="btn btn-primary" id="chat-send">Send</button>
  </form>
  <small class="text-muted d-block mt-2">
    Examples: "How do I submit a claim?" • "What is premium vs coverage?"
  </small>
</div></div>

<script>
const endpoint = "<?= e(base_url('api/ai-assistant.php')) ?>";
const csrf = "<?= e(csrf_token()) ?>";
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>