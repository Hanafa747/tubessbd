<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
wajib_login();

$definitions = [
    'akun_admin' => [
        'title'=>'Akun Admin', 'table'=>'akun_admin', 'pk'=>'id_admin',
        'fields'=>[
            'nama_admin'=>['Nama admin','text'],
            'username'=>['Username','text'],
            'password'=>['Password','password'],
            'role'=>['Hak akses','enum',['administrator','admin']]
        ],
        'columns'=>['id_admin'=>'ID','nama_admin'=>'Nama admin','username'=>'Username','role'=>'Hak akses']
    ],
    'pasien' => [
        'title'=>'Data Pasien', 'table'=>'pasien', 'pk'=>'id_pasien',
        'fields'=>[
            'nama_pasien'=>['Nama pasien','text'],
            'jenis_kelamin'=>['Jenis kelamin','enum',['Laki-laki','Perempuan']],
            'tanggal_lahir'=>['Tanggal lahir','date'],
            'alamat'=>['Alamat','textarea']
        ],
        'columns'=>['id_pasien'=>'ID','nama_pasien'=>'Nama pasien','jenis_kelamin'=>'Jenis kelamin','tanggal_lahir'=>'Tanggal lahir','alamat'=>'Alamat']
    ],
    'poli' => [
        'title'=>'Data Poli', 'table'=>'poli', 'pk'=>'id_poli',
        'fields'=>[
            'nama_poli'=>['Nama poli','text'],
            'lokasi'=>['Lokasi','text']
        ],
        'columns'=>['id_poli'=>'ID','nama_poli'=>'Nama poli','lokasi'=>'Lokasi']
    ],
    'dokter' => [
        'title'=>'Data Dokter', 'table'=>'dokter', 'pk'=>'id_dokter',
        'fields'=>[
            'id_poli'=>['Poli','fk','poli','id_poli','nama_poli'],
            'nama_dokter'=>['Nama dokter','text'],
            'spesialisasi'=>['Spesialisasi','text']
        ],
        'columns'=>['id_dokter'=>'ID','nama_dokter'=>'Nama dokter','nama_poli'=>'Poli','spesialisasi'=>'Spesialisasi']
    ],
    'pendaftaran' => [
        'title'=>'Data Pendaftaran', 'table'=>'pendaftaran', 'pk'=>'id_pendaftaran',
        'fields'=>[
            'id_pasien'=>['Pasien','fk','pasien','id_pasien','nama_pasien'],
            'id_dokter'=>['Dokter','fk','dokter','id_dokter','nama_dokter'],
            'tanggal_daftar'=>['Tanggal daftar','date'],
            'status'=>['Status','enum',['Menunggu','Diperiksa','Selesai','Batal']]
        ],
        'columns'=>['id_pendaftaran'=>'ID','nama_pasien'=>'Pasien','nama_dokter'=>'Dokter','tanggal_daftar'=>'Tanggal daftar','status'=>'Status']
    ],
    'rekam_medis' => [
        'title'=>'Data Rekam Medis', 'table'=>'rekam_medis', 'pk'=>'id_rekam',
        'fields'=>[
            'id_pendaftaran'=>['ID pendaftaran','fk','pendaftaran','id_pendaftaran','id_pendaftaran'],
            'diagnosis'=>['Diagnosis','textarea'],
            'tindakan'=>['Tindakan','textarea'],
            'resep_obat'=>['Resep obat','textarea']
        ],
        'columns'=>['id_rekam'=>'ID','id_pendaftaran'=>'ID pendaftaran','diagnosis'=>'Diagnosis','tindakan'=>'Tindakan','resep_obat'=>'Resep obat']
    ]
];

$key = $_GET['t'] ?? 'pasien';
if ($key === 'admin') { $key = 'akun_admin'; }
if (!isset($definitions[$key])) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}
$config = $definitions[$key];
$table = $config['table'];
$pk = $config['pk'];
$error = '';
$success = '';

function get_fk_options(PDO $pdo, array $field): array {
    $sql = "SELECT `{$field[3]}` AS option_id, `{$field[4]}` AS option_label FROM `{$field[2]}` ORDER BY 2";
    return $pdo->query($sql)->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            if ($key === 'akun_admin' && !adalah_administrator()) { throw new RuntimeException('Admin biasa hanya dapat melihat akun miliknya sendiri.'); }
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
            $stmt->execute([$id]);
            $success = $stmt->rowCount() ? 'Data berhasil dihapus.' : 'Data tidak ditemukan.';
        } elseif ($action === 'save') {
            if ($key === 'akun_admin' && !adalah_administrator()) { throw new RuntimeException('Admin biasa tidak boleh mengubah akun admin.'); }
            $id = (int)($_POST['id'] ?? 0);
            $values = [];

            foreach ($config['fields'] as $fieldName => $field) {
                $value = trim($_POST[$fieldName] ?? '');
                if ($fieldName === 'password') {
                    if ($value === '' && $id > 0) {
                        continue;
                    }
                    if ($value !== '') {
                        $value = password_hash($value, PASSWORD_DEFAULT);
                    }
                }
                $values[$fieldName] = $value === '' ? null : $value;
            }

            if ($id > 0) {
                if ($values) {
                    $setParts = [];
                    foreach (array_keys($values) as $column) {
                        $setParts[] = "`$column` = ?";
                    }
                    $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$pk` = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([...array_values($values), $id]);
                }
                $success = 'Data berhasil diperbarui.';
            } else {
                $columns = array_keys($values);
                $quotedColumns = array_map(fn($column) => "`$column`", $columns);
                $placeholders = array_fill(0, count($columns), '?');
                $sql = "INSERT INTO `$table` (" . implode(', ', $quotedColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($values));
                $success = 'Data baru berhasil ditambahkan.';
            }
        }
    } catch (RuntimeException $exception) { $error = $exception->getMessage(); }
    catch (PDOException $exception) {
        if ($exception->getCode() === '23000') {
            $error = 'Data tidak dapat diproses. Periksa kemungkinan username/nama poli duplikat atau data masih terhubung dengan tabel lain.';
        } else {
            $error = 'Data gagal diproses. Periksa kembali isian dan pastikan tabel database sesuai dengan rancangan.';
        }
    }
}

$editRow = null;
if (isset($_GET['edit']) && ($key !== 'akun_admin' || adalah_administrator())) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}

if ($key === 'dokter') {
    $rows = $pdo->query("SELECT d.id_dokter, d.nama_dokter, d.spesialisasi, p.nama_poli FROM dokter d JOIN poli p ON d.id_poli=p.id_poli ORDER BY d.id_dokter DESC")->fetchAll();
} elseif ($key === 'pendaftaran') {
    $rows = $pdo->query("SELECT p.id_pendaftaran, p.tanggal_daftar, p.status, ps.nama_pasien, d.nama_dokter FROM pendaftaran p JOIN pasien ps ON p.id_pasien=ps.id_pasien JOIN dokter d ON p.id_dokter=d.id_dokter ORDER BY p.id_pendaftaran DESC")->fetchAll();
} elseif ($key === 'akun_admin' && !adalah_administrator()) {
    $stmt = $pdo->prepare("SELECT id_admin,nama_admin,username,role FROM akun_admin WHERE id_admin=?");
    $stmt->execute([$_SESSION['admin_id']]);
    $rows = $stmt->fetchAll();
} elseif ($key === 'akun_admin') {
    $rows = $pdo->query("SELECT id_admin,nama_admin,username,role FROM akun_admin ORDER BY id_admin DESC")->fetchAll();
} else {
    $rows = $pdo->query("SELECT * FROM `$table` ORDER BY `$pk` DESC")->fetchAll();
}

$pageTitle = $config['title'];
$activeTable = $key;
require __DIR__ . '/partials/header.php';
?>
<div class="page-intro">
    <div><p>Kelola informasi <?= h(strtolower($config['title'])) ?> di sini. Kamu bisa menambah, mengubah, dan menghapus data.</p></div>
    <?php if ($key !== "akun_admin" || adalah_administrator()): ?><a class="btn primary" href="data.php?t=<?= h($key) ?>&add=1">＋ Tambah data</a><?php endif; ?>
</div>

<?php if ($success): ?><div class="alert success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

<?php if ((isset($_GET['add']) || $editRow) && ($key !== 'akun_admin' || adalah_administrator())): ?>
<section class="panel form-panel">
    <div class="panel-heading"><div><h2><?= $editRow ? 'Ubah data' : 'Tambah data baru' ?></h2><p>Isi kolom di bawah ini, lalu tekan tombol simpan.</p></div><a class="link-more" href="data.php?t=<?= h($key) ?>">Tutup ✕</a></div>
    <form method="post" class="data-form">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int)($editRow[$pk] ?? 0) ?>">
        <?php foreach ($config['fields'] as $fieldName => $field): $value = $editRow[$fieldName] ?? ''; ?>
        <div class="form-group">
            <label for="<?= h($fieldName) ?>"><?= h($field[0]) ?><?php if ($fieldName === 'password' && $editRow): ?> <span class="optional">(kosongkan jika tidak diubah)</span><?php endif; ?></label>
            <?php if ($field[1] === 'fk'): ?>
                <select id="<?= h($fieldName) ?>" name="<?= h($fieldName) ?>" required>
                    <option value="">-- Pilih <?= h(strtolower($field[0])) ?> --</option>
                    <?php foreach (get_fk_options($pdo, $field) as $option): ?>
                    <option value="<?= (int)$option['option_id'] ?>" <?= (string)$value === (string)$option['option_id'] ? 'selected' : '' ?>><?= h($option['option_label']) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php elseif ($field[1] === 'enum'): ?>
                <select id="<?= h($fieldName) ?>" name="<?= h($fieldName) ?>" required>
                    <option value="">-- Pilih <?= h(strtolower($field[0])) ?> --</option>
                    <?php foreach ($field[2] as $option): ?><option value="<?= h($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= h($option) ?></option><?php endforeach; ?>
                </select>
            <?php elseif ($field[1] === 'textarea'): ?>
                <textarea id="<?= h($fieldName) ?>" name="<?= h($fieldName) ?>" rows="3"><?= h($value) ?></textarea>
            <?php else: ?>
                <input id="<?= h($fieldName) ?>" type="<?= h($field[1]) ?>" name="<?= h($fieldName) ?>" value="<?= $field[1] === 'password' ? '' : h($value) ?>" <?= ($fieldName === 'password' && $editRow) ? '' : 'required' ?>>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <div class="form-actions"><button class="btn primary" type="submit">Simpan data</button><a class="btn secondary" href="data.php?t=<?= h($key) ?>">Batal</a></div>
    </form>
</section>
<?php endif; ?>

<section class="panel">
    <div class="panel-heading"><div><h2>Daftar <?= h($config['title']) ?></h2><p><?= count($rows) ?> data ditemukan</p></div></div>
    <div class="table-scroll"><table>
        <thead><tr><?php foreach ($config['columns'] as $label): ?><th><?= h($label) ?></th><?php endforeach; ?><?php if ($key !== "akun_admin" || adalah_administrator()): ?><th>Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr><td class="empty-state" colspan="<?= count($config['columns']) + 1 ?>">Belum ada data. Klik “Tambah data” untuk memasukkan data pertama.</td></tr><?php endif; ?>
        <?php foreach ($rows as $row): ?>
        <tr>
            <?php foreach ($config['columns'] as $column => $label): ?><td><?= h($row[$column] ?? '-') ?></td><?php endforeach; ?>
            <?php if ($key !== "akun_admin" || adalah_administrator()): ?><td><div class="row-actions">
                <a class="edit-action" href="data.php?t=<?= h($key) ?>&edit=<?= (int)$row[$pk] ?>">Ubah</a>
                <form method="post" onsubmit="return confirm('Yakin ingin menghapus data ini? Data yang masih digunakan tabel lain tidak dapat dihapus.')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$row[$pk] ?>">
                    <button class="delete-action" type="submit">Hapus</button>
                </form>
            </div></td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
