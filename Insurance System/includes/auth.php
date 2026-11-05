<?php
// includes/auth.php – sessions, CSRF, role guard, helpers
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/db.php';

/** HTML-escape helper */
function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/** Is a user logged in? */
function is_logged_in() { return isset($_SESSION['user_id']); }

/** Current user array or null */
function current_user() {
    if (!is_logged_in()) return null;
    global $pdo;
    static $u = null;
    if ($u === null) {
        $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $st->execute([$_SESSION['user_id']]);
        $u = $st->fetch() ?: null;
    }
    return $u;
}

/** Guard: require login */
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/** Guard: require a specific role */
function require_role($role) {
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        die('403 Forbidden – insufficient privileges.');
    }
}

/** Root URL helper (works on shared hosting subdirs) */
function base_url($path = '') {
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    // strip /customer or /admin subdir
    $root = preg_replace('#/(customer|admin|api)$#', '', $root);
    return $root . '/' . ltrim($path, '/');
}

/** CSRF token */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        die('Invalid CSRF token. Please refresh and try again.');
    }
}

/** Flash message helper */
function flash($key, $msg = null) {
    if ($msg === null) {
        $m = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    $_SESSION['flash'][$key] = $msg;
}

/** Generate a random reference */
function generate_reference($prefix = 'CLM') {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}