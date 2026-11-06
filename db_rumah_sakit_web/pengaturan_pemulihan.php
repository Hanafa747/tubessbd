<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/config/auth.php';
wajib_administrator();
$pageTitle='Kode Pemulihan'; $plain='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $plain=strtoupper(bin2hex(random_bytes(8)));
 $hash=password_hash($plain,PASSWORD_DEFAULT);
 $q=$pdo->prepare("INSERT INTO pemulihan_password(id,kode_hash) VALUES(1,?) ON DUPLICATE KEY UPDATE kode_hash=VALUES(kode_hash),dibuat_pada=CURRENT_TIMESTAMP");
 $q->execute([$hash]);
}
$exists=(bool)$pdo->query("SELECT COUNT(*) FROM pemulihan_password WHERE id=1")->fetchColumn();
require __DIR__.'/partials/header.php';
?>
<section class="panel form-panel"><div class="panel-heading"><div><h2>Pengaturan kode pemulihan</h2><p>Kode rahasia untuk mereset password akun admin. Kode hanya dapat dipakai sekali.</p></div></div><div class="alert">Database hanya menyimpan hash kode. Simpan kode yang muncul di tempat aman.</div><?php if($plain): ?><div class="alert success"><strong>Kode pemulihan:</strong><br><code style="font-size:20px;letter-spacing:2px"><?=h($plain)?></code><br>Salin sekarang. Kode hanya ditampilkan sekali.</div><?php endif; ?><p>Status: <strong><?=$exists?'Sudah tersedia':'Belum dibuat'?></strong></p><form method="post" onsubmit="return confirm('Kode lama akan dinonaktifkan. Lanjutkan?')"><button class="btn primary"><?=$exists?'Buat ulang kode':'Buat kode pemulihan'?></button></form></section>
<?php require __DIR__.'/partials/footer.php'; ?>
