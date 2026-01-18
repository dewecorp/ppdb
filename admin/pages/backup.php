<?php
function generate_backup_sql(mysqli $mysqli): string
{
    $sql = "-- Backup database\n";
    $sql .= "-- Waktu: " . date('Y-m-d H:i:s') . "\n\n";

    $tables = [];
    if ($resTables = $mysqli->query('SHOW TABLES')) {
        while ($row = $resTables->fetch_array()) {
            $tables[] = $row[0];
        }
        $resTables->free();
    }

    foreach ($tables as $table) {
        if (!$resCreate = $mysqli->query("SHOW CREATE TABLE `$table`")) {
            continue;
        }
        $rowCreate = $resCreate->fetch_assoc();
        $resCreate->free();

        $sql .= "--\n-- Struktur tabel `$table`\n--\n\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $rowCreate['Create Table'] . ";\n\n";

        if (!$resData = $mysqli->query("SELECT * FROM `$table`")) {
            continue;
        }
        if ($resData->num_rows > 0) {
            $columns = [];
            $fields = $resData->fetch_fields();
            foreach ($fields as $field) {
                $columns[] = "`" . $field->name . "`";
            }
            $sql .= "--\n-- Data untuk tabel `$table`\n--\n";
            $sql .= "INSERT INTO `$table` (" . implode(',', $columns) . ") VALUES\n";

            $rowsSql = [];
            $resData->data_seek(0);
            while ($row = $resData->fetch_assoc()) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . $mysqli->real_escape_string($value) . "'";
                    }
                }
                $rowsSql[] = '(' . implode(',', $values) . ')';
            }
            $sql .= implode(",\n", $rowsSql) . ";\n\n";
        }
        $resData->free();
    }

    return $sql;
}

$backupDir = __DIR__ . '/../../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'backup') {
        $sql = generate_backup_sql($mysqli);
        $fileName = 'backup-' . date('Ymd-His') . '.sql';
        file_put_contents($backupDir . '/' . $fileName, $sql);
        flash('success', 'Backup database berhasil dibuat.');
        log_activity('backup', 'Membuat backup ' . $fileName);
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=backup')) . '";</script>';
        exit;
    }

    if ($aksi === 'restore' && isset($_FILES['file_backup']) && $_FILES['file_backup']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['file_backup']['tmp_name'];
        $sql = file_get_contents($tmp);
        if ($sql !== false) {
            $mysqli->multi_query($sql);
            while ($mysqli->more_results() && $mysqli->next_result()) {
            }
            flash('success', 'Restore database berhasil.');
            log_activity('restore', 'Restore database dari file upload');
        } else {
            flash('error', 'Gagal membaca file backup.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=backup')) . '";</script>';
        exit;
    }

    if ($aksi === 'hapus' && isset($_POST['nama'])) {
        $nama = basename($_POST['nama']);
        $path = $backupDir . '/' . $nama;
        if (is_file($path)) {
            unlink($path);
            flash('success', 'File backup berhasil dihapus.');
            log_activity('delete_backup', 'Hapus backup ' . $nama);
        }
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=backup')) . '";</script>';
        exit;
    }
}

$files = [];
foreach (glob($backupDir . '/*.sql') as $file) {
    $files[] = [
        'nama' => basename($file),
        'tanggal' => date('Y-m-d H:i:s', filemtime($file)),
        'ukuran' => filesize($file),
    ];
}
usort($files, function ($a, $b) {
    return strcmp($b['nama'], $a['nama']);
});
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Backup &amp; Restore</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Backup Database</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="aksi" value="backup">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-database"></i> Backup Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Restore Database</h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="aksi" value="restore">
                        <div class="form-group">
                            <input type="file" name="file_backup" class="form-control-file" required>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-upload"></i> Restore
                        </button>
                        <small class="d-block text-muted mt-2">Pilih file .sql hasil backup.</small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar File Backup</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Backup</th>
                            <th>Tanggal Backup</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($files as $file): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= esc($file['nama']); ?></td>
                            <td><?= esc($file['tanggal']); ?></td>
                            <td><?= number_format($file['ukuran'] / 1024, 2); ?> KB</td>
                            <td>
                                <a class="btn btn-sm btn-success"
                                    href="download_backup.php?file=<?= urlencode($file['nama']); ?>">
                                    Unduh
                                </a>
                                <form method="post" class="d-inline form-hapus-backup">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="nama" value="<?= esc($file['nama']); ?>">
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus-backup">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    $(function () {
        $(document).on('click', '.btn-hapus-backup', function () {
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Hapus Backup?',
                text: 'File backup yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
