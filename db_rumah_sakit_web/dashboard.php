<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
wajib_login();

$pageTitle = 'Dashboard';
$counts = [];
$tables = ['pasien'=>'Pasien', 'poli'=>'Poli', 'dokter'=>'Dokter', 'pendaftaran'=>'Pendaftaran', 'rekam_medis'=>'Rekam Medis'];
foreach ($tables as $table => $label) {
    $counts[$table] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$recent = $pdo->query("SELECT p.id_pendaftaran, ps.nama_pasien, d.nama_dokter, p.tanggal_daftar, p.status FROM pendaftaran p JOIN pasien ps ON p.id_pasien=ps.id_pasien JOIN dokter d ON p.id_dokter=d.id_dokter ORDER BY p.id_pendaftaran DESC LIMIT 5")->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<section class="welcome-card">
    <div><span class="kicker">RINGKASAN RUMAH SAKIT</span><h2>Selamat datang, <?= h($_SESSION['admin_name'] ?? 'Admin') ?>!</h2><p>Semua data operasional rumah sakit bisa dikelola dari dashboard ini.</p></div>
    <div class="welcome-art"><span>✚</span><i></i><i></i><i></i></div>
</section>
<div class="section-heading"><div><h2>Ikhtisar data</h2><p>Jumlah data yang tersimpan saat ini.</p></div></div>
<div class="stats-grid">
<?php
$cards = [
    ['Pasien', $counts['pasien'], '♡', 'data.php?t=pasien', 'blue'],
    ['Poli', $counts['poli'], '⌂', 'data.php?t=poli', 'purple'],
    ['Dokter', $counts['dokter'], '✚', 'data.php?t=dokter', 'green'],
    ['Pendaftaran', $counts['pendaftaran'], '▤', 'data.php?t=pendaftaran', 'orange'],
    ['Rekam Medis', $counts['rekam_medis'], '▧', 'data.php?t=rekam_medis', 'pink']
];
foreach ($cards as $card):
?>
<a class="stat-card" href="<?= h($card[3]) ?>"><div class="stat-card-top"><span><?= h($card[0]) ?></span><span class="stat-icon <?= h($card[4]) ?>"><?= h($card[2]) ?></span></div><strong><?= (int)$card[1] ?></strong><small>Data tersimpan <span>→</span></small></a>
<?php endforeach; ?>
</div>
<section class="panel">
    <div class="panel-heading"><div><h2>Pendaftaran terbaru</h2><p>Lima pendaftaran terakhir yang tercatat.</p></div><a class="link-more" href="data.php?t=pendaftaran">Lihat semua →</a></div>
    <div class="table-scroll"><table><thead><tr><th>ID</th><th>Nama pasien</th><th>Dokter</th><th>Tanggal daftar</th><th>Status</th></tr></thead><tbody>
    <?php if (!$recent): ?><tr><td colspan="5" class="empty-state">Belum ada data pendaftaran.</td></tr><?php endif; ?>
    <?php foreach ($recent as $row): ?><tr><td class="id-cell">#<?= (int)$row['id_pendaftaran'] ?></td><td><?= h($row['nama_pasien']) ?></td><td><?= h($row['nama_dokter']) ?></td><td><?= h($row['tanggal_daftar']) ?></td><td><span class="status-pill"><?= h($row['status']) ?></span></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
