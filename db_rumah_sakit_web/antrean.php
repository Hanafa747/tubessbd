<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
wajib_login();

$pageTitle = 'Antrean Pasien';
$success = '';
$error = '';

function queue_message_status(string $status): string {
    $map = [
        'Menunggu' => 'status-menunggu',
        'Dipanggil' => 'status-diperiksa',
        'Diperiksa' => 'status-diperiksa',
        'Selesai' => 'status-selesai',
        'Batal' => 'status-batal'
    ];
    return $map[$status] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            $idPendaftaran = (int)($_POST['id_pendaftaran'] ?? 0);
            $stmt = $pdo->prepare("
                SELECT p.id_pendaftaran, p.tanggal_daftar, p.status, d.id_poli
                FROM pendaftaran p
                JOIN dokter d ON d.id_dokter = p.id_dokter
                LEFT JOIN antrean a ON a.id_pendaftaran = p.id_pendaftaran
                WHERE p.id_pendaftaran = ? AND a.id_antrean IS NULL
            ");
            $stmt->execute([$idPendaftaran]);
            $registration = $stmt->fetch();

            if (!$registration) {
                throw new RuntimeException('Pendaftaran tidak ditemukan atau sudah memiliki nomor antrean.');
            }
            if ($registration['status'] === 'Batal' || $registration['status'] === 'Selesai') {
                throw new RuntimeException('Pendaftaran yang batal atau selesai tidak dapat dibuatkan antrean.');
            }

            $pdo->beginTransaction();
            $numberQuery = $pdo->prepare("SELECT COALESCE(MAX(nomor_antrean), 0) + 1 FROM antrean WHERE id_poli = ? AND tanggal_antrean = ?");
            $numberQuery->execute([$registration['id_poli'], $registration['tanggal_daftar']]);
            $number = (int)$numberQuery->fetchColumn();

            $insert = $pdo->prepare("INSERT INTO antrean (id_pendaftaran, id_poli, nomor_antrean, tanggal_antrean, status) VALUES (?, ?, ?, ?, 'Menunggu')");
            $insert->execute([$idPendaftaran, $registration['id_poli'], $number, $registration['tanggal_daftar']]);
            $pdo->commit();
            $success = 'Nomor antrean berhasil dibuat: A' . str_pad((string)$number, 3, '0', STR_PAD_LEFT) . '.';
        } elseif (in_array($action, ['call', 'complete', 'cancel'], true)) {
            $idAntrean = (int)($_POST['id_antrean'] ?? 0);
            $stmt = $pdo->prepare("SELECT id_antrean, id_pendaftaran, status FROM antrean WHERE id_antrean = ?");
            $stmt->execute([$idAntrean]);
            $queue = $stmt->fetch();
            if (!$queue) {
                throw new RuntimeException('Data antrean tidak ditemukan.');
            }

            if ($action === 'call' && $queue['status'] === 'Menunggu') {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE antrean SET status = 'Diperiksa' WHERE id_antrean = ?")->execute([$idAntrean]);
                $pdo->prepare("UPDATE pendaftaran SET status = 'Diperiksa' WHERE id_pendaftaran = ?")->execute([$queue['id_pendaftaran']]);
                $pdo->commit();
                $success = 'Antrean berhasil dipanggil dan status pendaftaran diubah menjadi Diperiksa.';
            } elseif ($action === 'complete' && in_array($queue['status'], ['Menunggu', 'Diperiksa'], true)) {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE antrean SET status = 'Selesai' WHERE id_antrean = ?")->execute([$idAntrean]);
                $pdo->prepare("UPDATE pendaftaran SET status = 'Selesai' WHERE id_pendaftaran = ?")->execute([$queue['id_pendaftaran']]);
                $pdo->commit();
                $success = 'Antrean ditandai selesai.';
            } elseif ($action === 'cancel' && in_array($queue['status'], ['Menunggu', 'Diperiksa'], true)) {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE antrean SET status = 'Batal' WHERE id_antrean = ?")->execute([$idAntrean]);
                $pdo->prepare("UPDATE pendaftaran SET status = 'Batal' WHERE id_pendaftaran = ?")->execute([$queue['id_pendaftaran']]);
                $pdo->commit();
                $success = 'Antrean berhasil dibatalkan.';
            } else {
                throw new RuntimeException('Aksi tidak sesuai dengan status antrean saat ini.');
            }
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e instanceof RuntimeException ? $e->getMessage() : 'Proses antrean gagal. Pastikan tabel antrean sudah dibuat melalui file SQL pembaruan.';
    }
}

$available = $pdo->query("
    SELECT p.id_pendaftaran, p.tanggal_daftar, ps.nama_pasien, d.nama_dokter, po.nama_poli
    FROM pendaftaran p
    JOIN pasien ps ON ps.id_pasien = p.id_pasien
    JOIN dokter d ON d.id_dokter = p.id_dokter
    JOIN poli po ON po.id_poli = d.id_poli
    LEFT JOIN antrean a ON a.id_pendaftaran = p.id_pendaftaran
    WHERE a.id_antrean IS NULL AND p.status IN ('Menunggu', 'Diperiksa')
    ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC
")->fetchAll();

$filterDate = $_GET['tanggal'] ?? date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT a.id_antrean, a.nomor_antrean, a.tanggal_antrean, a.status,
           ps.nama_pasien, d.nama_dokter, po.nama_poli, p.id_pendaftaran
    FROM antrean a
    JOIN pendaftaran p ON p.id_pendaftaran = a.id_pendaftaran
    JOIN pasien ps ON ps.id_pasien = p.id_pasien
    JOIN dokter d ON d.id_dokter = p.id_dokter
    JOIN poli po ON po.id_poli = a.id_poli
    WHERE a.tanggal_antrean = ?
    ORDER BY po.nama_poli, a.nomor_antrean
");
$stmt->execute([$filterDate]);
$queues = $stmt->fetchAll();

$summary = ['Menunggu'=>0, 'Diperiksa'=>0, 'Selesai'=>0, 'Batal'=>0];
foreach ($queues as $queue) {
    if (isset($summary[$queue['status']])) {
        $summary[$queue['status']]++;
    }
}

require __DIR__ . '/partials/header.php';
?>
<div class="queue-intro">
    <div>
        <span class="kicker">LAYANAN PASIEN</span>
        <h2>Kelola antrean dengan lebih mudah</h2>
        <p>Buat nomor antrean dari pendaftaran yang tersedia, lalu perbarui status sesuai proses pelayanan.</p>
    </div>
    <div class="queue-date"><?= date('d M Y', strtotime($filterDate)) ?></div>
</div>

<?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

<div class="queue-summary">
    <div class="queue-summary-card"><span>Menunggu</span><strong><?= $summary['Menunggu'] ?></strong></div>
    <div class="queue-summary-card"><span>Sedang diperiksa</span><strong><?= $summary['Diperiksa'] ?></strong></div>
    <div class="queue-summary-card"><span>Selesai</span><strong><?= $summary['Selesai'] ?></strong></div>
    <div class="queue-summary-card"><span>Dibatalkan</span><strong><?= $summary['Batal'] ?></strong></div>
</div>

<section class="panel queue-create-panel">
    <div class="panel-heading">
        <div><h2>Buat nomor antrean</h2><p>Pilih pendaftaran yang belum memiliki nomor antrean.</p></div>
    </div>
    <?php if (!$available): ?>
        <div class="empty-state">Tidak ada pendaftaran yang bisa dibuatkan antrean. Tambahkan pendaftaran baru terlebih dahulu.</div>
    <?php else: ?>
    <form method="post" class="queue-create-form">
        <input type="hidden" name="action" value="create">
        <select name="id_pendaftaran" required>
            <option value="">-- Pilih pasien dari pendaftaran --</option>
            <?php foreach ($available as $item): ?>
            <option value="<?= (int)$item['id_pendaftaran'] ?>">#<?= (int)$item['id_pendaftaran'] ?> · <?= h($item['nama_pasien']) ?> — <?= h($item['nama_poli']) ?> (<?= h($item['tanggal_daftar']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <button class="btn primary" type="submit">＋ Buat antrean</button>
    </form>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="panel-heading queue-list-heading">
        <div><h2>Daftar antrean</h2><p><?= count($queues) ?> antrean pada tanggal yang dipilih.</p></div>
        <form method="get" class="queue-filter">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= h($filterDate) ?>">
            <button class="btn secondary" type="submit">Tampilkan</button>
        </form>
    </div>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Nomor</th><th>Pasien</th><th>Poli</th><th>Dokter</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$queues): ?><tr><td colspan="6" class="empty-state">Belum ada antrean pada tanggal ini.</td></tr><?php endif; ?>
            <?php foreach ($queues as $item): ?>
            <tr>
                <td><strong class="queue-number">A<?= str_pad((string)$item['nomor_antrean'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= h($item['nama_pasien']) ?></td>
                <td><?= h($item['nama_poli']) ?></td>
                <td><?= h($item['nama_dokter']) ?></td>
                <td><span class="status-pill <?= h(queue_message_status($item['status'])) ?>"><?= h($item['status']) ?></span></td>
                <td>
                    <div class="queue-actions">
                    <?php if ($item['status'] === 'Menunggu'): ?>
                        <form method="post"><input type="hidden" name="action" value="call"><input type="hidden" name="id_antrean" value="<?= (int)$item['id_antrean'] ?>"><button class="btn small primary" type="submit">Panggil</button></form>
                    <?php endif; ?>
                    <?php if (in_array($item['status'], ['Menunggu', 'Diperiksa'], true)): ?>
                        <form method="post"><input type="hidden" name="action" value="complete"><input type="hidden" name="id_antrean" value="<?= (int)$item['id_antrean'] ?>"><button class="btn small secondary" type="submit">Selesai</button></form>
                        <form method="post" onsubmit="return confirm('Batalkan antrean ini?')"><input type="hidden" name="action" value="cancel"><input type="hidden" name="id_antrean" value="<?= (int)$item['id_antrean'] ?>"><button class="btn small danger" type="submit">Batal</button></form>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
