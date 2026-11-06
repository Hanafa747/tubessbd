<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
wajib_login();

$pageTitle = 'Dashboard';

$counts = [];
$tableNames = [
    'akun_admin' => 'Akun Admin',
    'pasien' => 'Pasien',
    'poli' => 'Poli',
    'dokter' => 'Dokter',
    'pendaftaran' => 'Pendaftaran',
    'rekam_medis' => 'Rekam Medis'
];
foreach ($tableNames as $table => $label) {
    $counts[$table] = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$statusRows = $pdo->query("SELECT status, COUNT(*) AS total FROM pendaftaran GROUP BY status")->fetchAll();
$statusCounts = ['Menunggu'=>0, 'Diperiksa'=>0, 'Selesai'=>0, 'Batal'=>0];
foreach ($statusRows as $row) {
    $statusCounts[$row['status']] = (int)$row['total'];
}
$totalPendaftaran = $counts['pendaftaran'];
$completedPercent = $totalPendaftaran > 0 ? round(($statusCounts['Selesai'] / $totalPendaftaran) * 100) : 0;

$recent = $pdo->query("
    SELECT p.id_pendaftaran, ps.nama_pasien, d.nama_dokter, po.nama_poli,
           p.tanggal_daftar, p.status
    FROM pendaftaran p
    JOIN pasien ps ON p.id_pasien = ps.id_pasien
    JOIN dokter d ON p.id_dokter = d.id_dokter
    JOIN poli po ON d.id_poli = po.id_poli
    ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC
    LIMIT 6
")->fetchAll();

$topPoli = $pdo->query("
    SELECT po.nama_poli, COUNT(p.id_pendaftaran) AS total
    FROM poli po
    LEFT JOIN dokter d ON d.id_poli = po.id_poli
    LEFT JOIN pendaftaran p ON p.id_dokter = d.id_dokter
    GROUP BY po.id_poli, po.nama_poli
    ORDER BY total DESC, po.nama_poli ASC
    LIMIT 5
")->fetchAll();
$maxPoli = 1;
foreach ($topPoli as $row) {
    $maxPoli = max($maxPoli, (int)$row['total']);
}

$monthly = $pdo->query("
    SELECT DATE_FORMAT(tanggal_daftar, '%Y-%m') AS bulan,
           DATE_FORMAT(tanggal_daftar, '%b %Y') AS label,
           COUNT(*) AS total
    FROM pendaftaran
    WHERE tanggal_daftar >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY DATE_FORMAT(tanggal_daftar, '%Y-%m'), DATE_FORMAT(tanggal_daftar, '%b %Y')
    ORDER BY bulan ASC
")->fetchAll();
$maxMonthly = 1;
foreach ($monthly as $row) {
    $maxMonthly = max($maxMonthly, (int)$row['total']);
}

require __DIR__ . '/partials/header.php';
?>
<section class="dashboard-hero">
    <div class="hero-copy">
        <span class="kicker">RINGKASAN OPERASIONAL</span>
        <h2>Selamat datang, <?= h($_SESSION['admin_name'] ?? 'Admin') ?> 👋</h2>
        <p>Pantau data dan aktivitas rumah sakit dari satu halaman. Pilih menu di samping untuk mulai mengelola informasi.</p>
        <div class="hero-actions">
            <a class="btn primary" href="data.php?t=pendaftaran">Kelola pendaftaran <span>→</span></a>
            <a class="hero-text-link" href="data.php?t=pasien">Lihat data pasien</a>
        </div>
    </div>
    <div class="hero-illustration" aria-hidden="true">
        <div class="hero-circle circle-one"></div>
        <div class="hero-circle circle-two"></div>
        <div class="medical-cross">✚</div>
        <div class="hero-pill pill-one">+</div>
        <div class="hero-pill pill-two">♡</div>
    </div>
</section>

<div class="dashboard-section-title">
    <div><h2>Ringkasan data</h2><p>Jumlah data yang tersimpan di database saat ini.</p></div>
    <span class="updated-label"><span></span> Data langsung dari database</span>
</div>

<div class="dashboard-stats">
    <a class="dashboard-stat stat-patients" href="data.php?t=pasien">
        <div class="dashboard-stat-head"><span>Total Pasien</span><span class="dashboard-stat-icon">♡</span></div>
        <strong><?= $counts['pasien'] ?></strong><small>Data pasien terdaftar</small>
    </a>
    <a class="dashboard-stat stat-doctors" href="data.php?t=dokter">
        <div class="dashboard-stat-head"><span>Total Dokter</span><span class="dashboard-stat-icon">✚</span></div>
        <strong><?= $counts['dokter'] ?></strong><small>Dokter terdaftar</small>
    </a>
    <a class="dashboard-stat stat-poli" href="data.php?t=poli">
        <div class="dashboard-stat-head"><span>Total Poli</span><span class="dashboard-stat-icon">⌂</span></div>
        <strong><?= $counts['poli'] ?></strong><small>Poli tersedia</small>
    </a>
    <a class="dashboard-stat stat-register" href="data.php?t=pendaftaran">
        <div class="dashboard-stat-head"><span>Total Pendaftaran</span><span class="dashboard-stat-icon">▤</span></div>
        <strong><?= $counts['pendaftaran'] ?></strong><small>Seluruh pendaftaran</small>
    </a>
    <a class="dashboard-stat stat-records" href="data.php?t=rekam_medis">
        <div class="dashboard-stat-head"><span>Rekam Medis</span><span class="dashboard-stat-icon">▧</span></div>
        <strong><?= $counts['rekam_medis'] ?></strong><small>Catatan pemeriksaan</small>
    </a>
    <a class="dashboard-stat stat-admins" href="data.php?t=akun_admin">
        <div class="dashboard-stat-head"><span>Administrator</span><span class="dashboard-stat-icon">♙</span></div>
        <strong><?= $counts['akun_admin'] ?></strong><small>Akun pengelola sistem</small>
    </a>
</div>

<div class="dashboard-two-columns">
    <section class="panel dashboard-panel">
        <div class="panel-heading"><div><h2>Status pendaftaran</h2><p>Perbandingan status dari seluruh data pendaftaran.</p></div><a class="link-more" href="data.php?t=pendaftaran">Buka data →</a></div>
        <div class="status-summary">
            <div class="status-total"><strong><?= $totalPendaftaran ?></strong><span>Total pendaftaran</span></div>
            <div class="completion-rate"><div class="completion-ring" style="--percent: <?= $completedPercent ?>%"><span><?= $completedPercent ?>%</span></div><small>Selesai</small></div>
        </div>
        <?php
        $statusClasses = ['Menunggu'=>'waiting', 'Diperiksa'=>'checking', 'Selesai'=>'done', 'Batal'=>'cancelled'];
        foreach ($statusCounts as $status => $total):
            $width = $totalPendaftaran > 0 ? ($total / $totalPendaftaran) * 100 : 0;
        ?>
        <div class="status-line">
            <div class="status-line-label"><span class="status-dot <?= h($statusClasses[$status]) ?>"></span><?= h($status) ?><b><?= $total ?></b></div>
            <div class="progress-track"><div class="progress-fill <?= h($statusClasses[$status]) ?>" style="width: <?= $width ?>%"></div></div>
        </div>
        <?php endforeach; ?>
    </section>

    <section class="panel dashboard-panel">
        <div class="panel-heading"><div><h2>Pendaftaran per poli</h2><p>Jumlah pendaftaran yang ditangani setiap poli.</p></div><a class="link-more" href="data.php?t=poli">Lihat poli →</a></div>
        <?php if (!$topPoli): ?><div class="empty-state">Belum ada data poli.</div><?php endif; ?>
        <?php foreach ($topPoli as $index => $row): ?>
        <div class="ranking-line">
            <div class="ranking-name"><span class="ranking-number"><?= $index + 1 ?></span><span><?= h($row['nama_poli']) ?></span><b><?= (int)$row['total'] ?></b></div>
            <div class="progress-track"><div class="progress-fill ranking-fill" style="width: <?= ((int)$row['total'] / $maxPoli) * 100 ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <p class="chart-footnote">Diurutkan berdasarkan jumlah pendaftaran terbanyak.</p>
    </section>
</div>

<div class="dashboard-two-columns lower-columns">
    <section class="panel dashboard-panel">
        <div class="panel-heading"><div><h2>Tren pendaftaran</h2><p>Jumlah pendaftaran berdasarkan bulan yang tercatat dalam 6 bulan terakhir.</p></div></div>
        <?php if (!$monthly): ?>
            <div class="empty-state">Belum ada pendaftaran dalam rentang 6 bulan terakhir.</div>
        <?php else: ?>
            <div class="monthly-chart">
            <?php foreach ($monthly as $row): ?>
                <div class="month-column">
                    <div class="month-bar-area"><span class="month-value"><?= (int)$row['total'] ?></span><div class="month-bar" style="height: <?= max(5, ((int)$row['total'] / $maxMonthly) * 100) ?>%"></div></div>
                    <span class="month-label"><?= h($row['label']) ?></span>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="panel dashboard-panel quick-panel">
        <div class="panel-heading"><div><h2>Akses cepat</h2><p>Menu yang sering digunakan untuk pengelolaan.</p></div></div>
        <a class="quick-link" href="data.php?t=pasien"><span class="quick-icon">＋</span><span><b>Tambah pasien</b><small>Masukkan data pasien baru</small></span><i>→</i></a>
        <a class="quick-link" href="data.php?t=dokter"><span class="quick-icon purple-quick">✚</span><span><b>Kelola dokter</b><small>Tambah atau perbarui data dokter</small></span><i>→</i></a>
        <a class="quick-link" href="data.php?t=pendaftaran"><span class="quick-icon orange-quick">▤</span><span><b>Kelola pendaftaran</b><small>Periksa status pendaftaran pasien</small></span><i>→</i></a>
        <a class="quick-link" href="antrean.php"><span class="quick-icon">⌛</span><span><b>Antrean pasien</b><small>Buat dan panggil nomor antrean</small></span><i>→</i></a>
        <a class="quick-link" href="data.php?t=rekam_medis"><span class="quick-icon pink-quick">▧</span><span><b>Rekam medis</b><small>Lihat catatan pemeriksaan</small></span><i>→</i></a>
    </section>
</div>

<section class="panel recent-panel">
    <div class="panel-heading"><div><h2>Aktivitas pendaftaran terbaru</h2><p>Data pendaftaran terakhir berdasarkan tanggal pendaftaran.</p></div><a class="link-more" href="data.php?t=pendaftaran">Lihat semua pendaftaran →</a></div>
    <div class="table-scroll"><table><thead><tr><th>ID</th><th>Pasien</th><th>Dokter</th><th>Poli</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>
    <?php if (!$recent): ?><tr><td colspan="6" class="empty-state">Belum ada data pendaftaran. Tambahkan data melalui menu Pendaftaran.</td></tr><?php endif; ?>
    <?php foreach ($recent as $row): ?>
    <tr><td class="id-cell">#<?= (int)$row['id_pendaftaran'] ?></td><td><?= h($row['nama_pasien']) ?></td><td><?= h($row['nama_dokter']) ?></td><td><?= h($row['nama_poli']) ?></td><td><?= h($row['tanggal_daftar']) ?></td><td><span class="status-pill status-<?= strtolower(h($row['status'])) ?>"><?= h($row['status']) ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
