<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $user=trim($_POST['username']??''); $code=trim($_POST['recovery_code']??'');
 $new=$_POST['new_password']??''; $confirm=$_POST['confirm_password']??'';
 if (strlen($new)<8) $error='Password baru minimal 8 karakter.';
 elseif ($new!==$confirm) $error='Konfirmasi password tidak sama.';
 else {
  $r=$pdo->query("SELECT kode_hash FROM pemulihan_password WHERE id=1")->fetch();
  $q=$pdo->prepare("SELECT id_admin FROM akun_admin WHERE username=? LIMIT 1"); $q->execute([$user]); $a=$q->fetch();
  if ($r && $a && password_verify($code,$r['kode_hash'])) {
   $pdo->beginTransaction();
   $u=$pdo->prepare("UPDATE akun_admin SET password=? WHERE id_admin=?");
   $u->execute([password_hash($new,PASSWORD_DEFAULT),$a['id_admin']]);
   $pdo->exec("DELETE FROM pemulihan_password WHERE id=1");
   $pdo->commit(); $success='Password berhasil direset. Kode pemulihan sudah tidak berlaku.';
  } else $error='Username atau kode pemulihan tidak valid.';
 }
}
?>
<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Lupa Password | RSCare</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><div class="login-shell"><div class="login-visual"><div class="login-brand">✚ RSCare</div><div class="visual-copy"><h1>Pemulihan akun admin</h1><p>Buat password baru dengan kode pemulihan sekali pakai.</p></div></div><div class="login-form-side"><div class="login-form"><h2>Lupa password?</h2><p class="subtext">Masukkan username, kode pemulihan, dan password baru.</p><?php if($error): ?><div class="alert error"><?=h($error)?></div><?php endif; ?><?php if($success): ?><div class="alert success"><?=h($success)?></div><?php endif; ?><form method="post"><label>Username</label><input name="username" required><label>Kode pemulihan</label><input name="recovery_code" required><label>Password baru (minimal 8 karakter)</label><input name="new_password" type="password" minlength="8" required><label>Konfirmasi password</label><input name="confirm_password" type="password" minlength="8" required><button class="btn primary login-button">Reset Password</button></form><p class="recovery-link"><a href="login.php">Kembali ke login</a></p></div></div></div></body></html>
