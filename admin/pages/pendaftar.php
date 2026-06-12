<?php
function redirect_to_pendaftar(): void
{
    echo '<script>window.location.href="' . base_url('admin/pendaftar') . '";</script>';
    exit;
}

$total_pendaftar = count_pendaftar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'reset_total') {
        $okDelete = @$mysqli->query('DELETE FROM pendaftar');
        $okReset = reset_no_pendaftaran();
        if ($okDelete) {
            if ($okReset) {
                flash('success', 'Semua data pendaftar dihapus dan nomor pendaftaran direset.');
            } else {
                flash('success', 'Semua data pendaftar dihapus. Nomor pendaftaran gagal direset.');
            }
            log_activity('reset_total', 'Hapus semua pendaftar & reset nomor');
        } else {
            flash('error', 'Gagal menghapus semua data pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if ($aksi === 'ubah_status' && isset($_POST['id'], $_POST['status'])) {
        $id = (int)$_POST['id'];
        $status = $_POST['status'] === 'diterima' ? 'diterima' : ($_POST['status'] === 'ditolak' ? 'ditolak' : 'proses');
        $stmt = $mysqli->prepare('UPDATE pendaftar SET status_daftar=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            $stmt->close();
            $rowEmail = null;
            if ($res2 = $mysqli->prepare('SELECT nama_lengkap, no_pendaftaran, email, hp FROM pendaftar WHERE id=? LIMIT 1')) {
                $res2->bind_param('i', $id);
                $res2->execute();
                $r = $res2->get_result();
                $rowEmail = $r->fetch_assoc() ?: null;
                $res2->close();
            }
            if ($rowEmail && !empty($rowEmail['email'])) {
                $info = madrasah_info();
                $subject = 'Informasi Status PPDB - ' . (string)$info['nama'];
                $statusText = $status === 'diterima' ? 'DITERIMA' : ($status === 'ditolak' ? 'DITOLAK' : 'DALAM PROSES');
                $lines = [];
                $lines[] = 'Assalamu\'alaikum ' . $rowEmail['nama_lengkap'] . ',';
                $lines[] = 'Nomor Pendaftaran: ' . $rowEmail['no_pendaftaran'];
                $lines[] = 'Status Pendaftaran: ' . $statusText;
                $lines[] = 'Informasi detail dapat dilihat di halaman PPDB: ' . base_url();
                $message = implode("\r\n", $lines);
                if (send_email($rowEmail['email'], $subject, $message)) {
                    $flash = 'Status diperbarui dan email notifikasi terkirim.';
                } else {
                    $flash = 'Status diperbarui. Email gagal dikirim.';
                }
                if (isset($rowEmail['hp']) ? $rowEmail['hp'] !== '' : false) {
                    if (send_whatsapp((string)$rowEmail['hp'], $message)) {
                        $flash .= ' WhatsApp terkirim.';
                    } else {
                        $flash .= ' WhatsApp gagal.';
                    }
                }
                flash('success', $flash);
            } else {
                flash('success', 'Status pendaftar berhasil diperbarui.');
            }
            log_activity('update_pendaftar_status', 'Ubah status pendaftar ID ' . $id . ' menjadi ' . $status);
        } else {
            flash('error', 'Gagal memperbarui status pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if ($aksi === 'kirim_email' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $row = null;
        if ($res = $mysqli->prepare('SELECT nama_lengkap, no_pendaftaran, email, status_daftar FROM pendaftar WHERE id = ? LIMIT 1')) {
            $res->bind_param('i', $id);
            $res->execute();
            $result = $res->get_result();
            $row = $result->fetch_assoc() ?: null;
            $res->close();
        }
        if ($row && !empty($row['email'])) {
            $info = madrasah_info();
            $subject = 'Informasi Status PPDB - ' . (string)$info['nama'];
            $statusText = $row['status_daftar'] === 'diterima' ? 'DITERIMA' : ($row['status_daftar'] === 'ditolak' ? 'DITOLAK' : 'DALAM PROSES');
            $bodyLines = [];
            $bodyLines[] = 'Assalamu\'alaikum ' . $row['nama_lengkap'] . ',';
            $bodyLines[] = 'Nomor Pendaftaran: ' . $row['no_pendaftaran'];
            $bodyLines[] = 'Status Pendaftaran: ' . $statusText;
            $bodyLines[] = 'Informasi detail dapat dilihat di halaman PPDB: ' . base_url();
            if (!empty($info['hp_panitia']) || !empty($info['hp_kepala'])) {
                $bodyLines[] = 'Kontak: ' . (!empty($info['hp_panitia']) ? $info['hp_panitia'] : $info['hp_kepala']);
            }
            $bodyLines[] = 'Terima kasih.';
            $message = implode("\r\n", $bodyLines);
            if (send_email($row['email'], $subject, $message)) {
                flash('success', 'Notifikasi email berhasil dikirim.');
                log_activity('send_email_pendaftar', 'Kirim email pendaftar ID ' . $id);
            } else {
                flash('error', 'Gagal mengirim email. Pastikan konfigurasi email server tersedia.');
            }
        } else {
            flash('error', 'Email pendaftar tidak tersedia.');
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
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Pendaftar
                <span class="badge badge-pill badge-primary ml-2">Total: <?= (int)$total_pendaftar; ?></span>
            </h6>
            <div class="mt-2 mt-sm-0">
                <a href="cetak_semua_kartu.php" target="_blank" class="btn btn-sm btn-info">
                    <i class="fas fa-id-card"></i> Cetak Semua Kartu
                </a>
                <a href="laporan_pendaftar.php" target="_blank" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> Cetak Rekap
                </a>
                <button type="button" class="btn btn-sm btn-danger" id="btnHapusTerpilih">
                    <i class="fas fa-trash-alt"></i> Hapus Terpilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnResetTotal">
                    <i class="fas fa-exclamation-triangle"></i> Reset Total
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="formPendaftar" method="post">
                <input type="hidden" name="aksi" id="aksiGlobal" value="">
                <input type="hidden" name="id" id="idGlobal" value="">
                <input type="hidden" name="status" id="statusGlobal" value="">
                <div class="table-responsive">
                    <table id="tablePendaftar" class="table table-bordered datatable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>No</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Pendaftar</th>
                                <th>Jenis Kelamin</th>
                                <th>Tempat, Tgl Lahir</th>
                                <th>NIK</th>
                                <th>KK</th>
                                <th>Alamat</th>
                                <th>Status Keluarga</th>
                                <th>Anak Ke/Jml Saudara</th>
                                <th>Asal TK/RA</th>
                                <th>Nama Ayah</th>
                                <th>Pekerjaan Ayah</th>
                                <th>Nama Ibu</th>
                                <th>Pekerjaan Ibu</th>
                                <th>Nama Wali</th>
                                <th>Pekerjaan Wali</th>
                                <th>Email</th>
                                <th>No HP</th>
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
                                <td><?= esc($row['tempat_lahir']); ?>, <?= esc($row['tanggal_lahir']); ?></td>
                                <td><?= esc($row['nik']); ?></td>
                                <td><?= esc($row['kk']); ?></td>
                                <td><?= esc($row['alamat']); ?></td>
                                <td><?= esc($row['status_keluarga']); ?></td>
                                <td><?= esc((string)$row['anak_ke']); ?> / <?= esc((string)$row['jumlah_saudara']); ?></td>
                                <td><?= esc($row['asal_tk']); ?></td>
                                <td><?= esc($row['nama_ayah']); ?></td>
                                <td><?= esc($row['pekerjaan_ayah']); ?></td>
                                <td><?= esc($row['nama_ibu']); ?></td>
                                <td><?= esc($row['pekerjaan_ibu']); ?></td>
                                <td><?= esc($row['nama_wali']); ?></td>
                                <td><?= esc($row['pekerjaan_wali']); ?></td>
                                <td><?= esc($row['email']); ?></td>
                                <td><?= esc($row['hp']); ?></td>
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
                                    <button type="button" class="btn btn-sm btn-primary btn-email mb-1"
                                        data-id="<?= (int)$row['id']; ?>">
                                        Email
                                    </button>
                                    <?php
                                        $hp = isset($row['hp']) ? preg_replace('/\D+/', '', (string)$row['hp']) : '';
                                        // Ensure Indonesian country code
                                        if (substr($hp, 0, 1) === '0') {
                                            $hp = '62' . substr($hp, 1);
                                        }
                                        $info = madrasah_info();
                                        $statusText = $row['status_daftar'] === 'diterima' ? 'DITERIMA' : ($row['status_daftar'] === 'ditolak' ? 'DITOLAK' : 'DALAM PROSES');
                                        $waText = rawurlencode("Assalamu'alaikum " . $row['nama_lengkap'] . "\nNomor: " . $row['no_pendaftaran'] . "\nStatus: " . $statusText . "\nInfo: " . base_url());
                                    ?>
                                    <?php if (!empty($hp)): ?>
                                    <a href="https://api.whatsapp.com/send?phone=<?= esc($hp); ?>&text=<?= $waText; ?>" target="_blank" class="btn btn-sm btn-success mb-1">
                                        WhatsApp
                                    </a>
                                    <?php endif; ?>
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
