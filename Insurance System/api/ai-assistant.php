<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ai.php';
header('Content-Type: application/json');

if (!is_logged_in()) { echo json_encode(['error'=>'Not authenticated']); exit; }
verify_csrf();

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$msg   = trim($input['message'] ?? ($_POST['message'] ?? ''));
if ($msg === '') { echo json_encode(['error'=>'Empty message']); exit; }
if (strlen($msg) > 1000) $msg = substr($msg, 0, 1000);

$system = "You are a friendly insurance assistant for a student demo system called InsureAI. "
        . "Answer general questions about insurance concepts: policies, premiums, coverage, "
        . "how to submit claims (customers submit via the Submit Claim page and track them in "
        . "My Claims), common documents (ID, photos, invoices, police report for theft, medical "
        . "reports for health claims, etc). "
        . "IMPORTANT RULES: never invent specific policy numbers or personal data; never promise "
        . "claim approval or rejection; always state that final decisions are made by human "
        . "administrators. Keep answers short (max 5 sentences).";

$res = call_ai([
    ['role'=>'system','content'=>$system],
    ['role'=>'user','content'=>$msg],
], 400);

if (!$res['ok']) {
    echo json_encode(['error' => 'AI is currently unavailable. Please try again later.']);
    exit;
}
echo json_encode(['reply' => $res['text']]);