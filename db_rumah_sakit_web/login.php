<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id_admin, nama_admin, username, password FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$usernameInput]);
    $admin = $stmt->fetch();

    if ($admin) {
        $storedPassword = (string)$admin['password'];
        $valid = password_verify($passwordInput, $storedPassword) || hash_equals($storedPassword, $passwordInput);

        if ($valid) {
            if (!password_verify($passwordInput, $storedPassword)) {
                $hash = password_hash($passwordInput, PASSWORD_DEFAULT);
                $update = $pdo->prepare('UPDATE admin SET password = ? WHERE id_admin = ?');
                $update->execute([$hash, $admin['id_admin']]);
            }

            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_name'] = $admin['nama_admin'];
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'Username atau password tidak sesuai. Silakan coba lagi.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Admin | Rumah Sakit</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
<div class="login-shell">
    <div class="login-visual">
        <div class="login-brand"><span class="brand-symbol">✚</span><span>RS<span class="brand-light">Care</span></span></div>
        <div class="visual-copy">
            <span class="kicker">SISTEM INFORMASI RUMAH SAKIT</span>
            <h1>Kelola layanan kesehatan dengan lebih mudah.</h1>
            <p>Satu tempat untuk mengelola pasien, dokter, poli, pendaftaran, dan rekam medis.</p>
        </div>
        <div class="visual-note">Portal khusus administrator</div>
    </div>
    <div class="login-form-side">
        <div class="login-form">
            <div class="mobile-brand"><span class="brand-symbol">✚</span> RSCare</div>
            <span class="kicker">SELAMAT DATANG KEMBALI</span>
            <h2>Masuk ke akun admin</h2>
            <p class="subtext">Masukkan username dan password untuk melanjutkan.</p>
            <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
            <form method="post">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="Masukkan username" required autofocus autocomplete="username">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Masukkan password" required autocomplete="current-password">
                <button class="btn primary login-button" type="submit">Masuk <span>→</span></button>
            </form>
            <p class="login-hint"><span>●</span> Akses ini hanya tersedia untuk administrator.</p>
        </div>
    </div>
</div>
</body>
</html>
