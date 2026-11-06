<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id_admin,nama_admin,username,password,role FROM akun_admin WHERE username=? LIMIT 1');
    $stmt->execute([$usernameInput]);
    $account = $stmt->fetch();
    if ($account) {
        $stored = (string)$account['password'];
        if (password_verify($passwordInput, $stored) || hash_equals($stored, $passwordInput)) {
            if (!password_verify($passwordInput, $stored)) {
                $update = $pdo->prepare('UPDATE akun_admin SET password=? WHERE id_admin=?');
                $update->execute([password_hash($passwordInput, PASSWORD_DEFAULT), $account['id_admin']]);
            }
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$account['id_admin'];
            $_SESSION['admin_name'] = $account['nama_admin'];
            $_SESSION['admin_username'] = $account['username'];
            $_SESSION['admin_role'] = $account['role'];
            header('Location: dashboard.php'); exit;
        }
    }
    $error = 'Username atau password tidak sesuai.';
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login RSCare</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><div class="login-shell"><div class="login-visual"><div class="login-brand">✚ RSCare</div><div class="visual-copy"><span class="kicker">SISTEM INFORMASI RUMAH SAKIT</span><h1>Kelola layanan kesehatan dengan lebih mudah.</h1><p>Satu tempat untuk mengelola data dan layanan rumah sakit.</p></div></div><div class="login-form-side"><div class="login-form"><h2>Masuk ke akun admin</h2><p class="subtext">Masukkan username dan password untuk melanjutkan.</p><?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?><form method="post"><label>Username</label><input name="username" required><label>Password</label><input name="password" type="password" required><button class="btn primary login-button">Masuk</button></form><p class="recovery-link"><a href="lupa_password.php">Lupa password?</a></p></div></div></div></body></html>
