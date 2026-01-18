<?php
function redirect_to_pendaftar(): void
{
    echo '<script>window.location.href="' . base_url('admin/index.php?page=pendaftar') . '";</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'ubah_status' && isset($_POST['id'], $_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'] === 'diterima' ? 'diterima' : ($_POST['status'] === 'ditolak' ? 'ditolak' : 'proses');
        $stmt = $mysqli->prepare('UPDATE pendaftar SET status_daftar=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            $stmt->close();
            flash('success', 'Status pendaftar berhasil diperbarui.');
            log_activity('update_pendaftar_status', 'Ubah status pendaftar ID ' . $id . ' menjadi ' . $status);
        } else {
            flash('error', 'Gagal memperbarui status pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if ($aksi === 'hapus' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $mysqli->prepare('DELETE FROM pendaftar WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            flash('success', 'Data pendaftar berhasil dihapus.');
            log_activity('delete_pendaftar', 'Hapus pendaftar ID ' . $id);
        } else {
            flash('error', 'Gagal menghapus data pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if ($aksi === 'hapus_terpilih' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = array_map('intval', $_POST['ids']);
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $sql = "DELETE FROM pendaftar WHERE id IN ($placeholders)";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $stmt->close();
                flash('success', 'Data terpilih berhasil dihapus.');
                log_activity('delete_pendaftar_bulk', 'Hapus pendaftar bulk jumlah ' . count($ids));
            } else {
                flash('error', 'Gagal menghapus data terpilih.');
            }
        }
        redirect_to_pendaftar();
    }
}

$pendaftar = [];
if ($result = $mysqli->query('SELECT * FROM pendaftar ORDER BY created_at DESC')) {
    while ($row = $result->fetch_assoc()) {
        $pendaftar[] = $row;
    }
    $result->free();
}
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Pendaftar</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pendaftar</h6>
            <div class="mt-2 mt-sm-0">
                <a href="laporan_pendaftar.php" target="_blank" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> Cetak Rekap
                </a>
                <button type="button" class="btn btn-sm btn-danger" id="btnHapusTerpilih">
                    <i class="fas fa-trash-alt"></i> Hapus Terpilih
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formPendaftar" method="post">
                <input type="hidden" name="aksi" id="aksiGlobal" value="">
                <input type="hidden" name="id" id="idGlobal" value="">
                <input type="hidden" name="status" id="statusGlobal" value="">
                <div class="table-responsive">
                    <table class="table table-bordered datatable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>No</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Pendaftar</th>
                                <th>Jenis Kelamin</th>
                                <th>NIK</th>
                                <th>KK</th>
                                <th>KIP</th>
                                <th>PKH</th>
                                <th>Status Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($pendaftar as $row): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="<?= (int)$row['id']; ?>"
                                        class="check-item">
                                </td>
                                <td><?= $no++; ?></td>
                                <td><?= esc($row['no_pendaftaran']); ?></td>
                                <td><?= esc($row['nama_lengkap']); ?></td>
                                <td><?= esc($row['jenis_kelamin']); ?></td>
                                <td><?= esc($row['nik']); ?></td>
                                <td><?= esc($row['kk']); ?></td>
                                <td><?= esc($row['kip']); ?></td>
                                <td><?= esc($row['pkh']); ?></td>
                                <td>
                                    <?php if ($row['status_daftar'] === 'diterima'): ?>
                                    <span class="badge badge-success">Diterima</span>
                                    <?php elseif ($row['status_daftar'] === 'ditolak'): ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Proses</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="cetak_kartu.php?id=<?= (int)$row['id']; ?>" target="_blank"
                                        class="btn btn-sm btn-info mb-1">
                                        Kartu
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success btn-status mb-1"
                                        data-id="<?= (int)$row['id']; ?>" data-status="diterima">
                                        Terima
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning btn-status mb-1"
                                        data-id="<?= (int)$row['id']; ?>" data-status="ditolak">
                                        Tolak
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger btn-hapus mb-1"
                                        data-id="<?= (int)$row['id']; ?>">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- handlers dipindah ke footer agar berjalan setelah jQuery & plugin dimuat -->
