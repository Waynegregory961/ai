<?php
// setup.php – one-time admin creator. DELETE AFTER USE.
require_once __DIR__ . '/includes/db.php';

$email = 'admin@insureai.test';
$pass  = 'Admin@123';
$name  = 'System Administrator';

$st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$st->execute([$email]);
if ($st->fetch()) { exit('Admin already exists. Delete setup.php.'); }

$st = $pdo->prepare("INSERT INTO users (name,email,password,role)
                     VALUES (?,?,?, 'admin')");
$st->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
echo "Admin created:<br>Email: {$email}<br>Password: {$pass}<br><b>Delete this file now.</b>";