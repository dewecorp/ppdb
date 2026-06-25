<?php
function redirect_to_pendaftar(): void
{
    echo '<script>window.location.href="' . base_url('admin/pendaftar') . '";</script>';
    exit;
}

function pendaftar_post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function pendaftar_valid_name(string $name): bool
{
    return preg_match('/^[a-zA-Z\x{00C0}-\x{024F}\s\'.,-]*$/u', $name) === 1;
}

function pendaftar_payload(): array
{
    return [
        'nama_lengkap' => pendaftar_post('nama_lengkap'),
        'nik' => pendaftar_post('nik'),
        'kk' => pendaftar_post('kk'),
        'jenis_kelamin' => pendaftar_post('jenis_kelamin'),
        'tempat_lahir' => pendaftar_post('tempat_lahir'),
        'tanggal_lahir' => pendaftar_post('tanggal_lahir'),
        'alamat' => pendaftar_post('alamat'),
        'status_keluarga' => pendaftar_post('status_keluarga'),
        'anak_ke' => pendaftar_post('anak_ke'),
        'jumlah_saudara' => pendaftar_post('jumlah_saudara'),
        'asal_tk' => pendaftar_post('asal_tk'),
        'nama_ayah' => pendaftar_post('nama_ayah'),
        'nama_ibu' => pendaftar_post('nama_ibu'),
        'pekerjaan_ayah' => pendaftar_post('pekerjaan_ayah'),
        'pekerjaan_ibu' => pendaftar_post('pekerjaan_ibu'),
        'nama_wali' => pendaftar_post('nama_wali'),
        'pekerjaan_wali' => pendaftar_post('pekerjaan_wali'),
        'email' => pendaftar_post('email'),
        'hp' => pendaftar_post('hp'),
        'kip' => isset($_POST['kip']) ? 'Ya' : 'Tidak',
        'pkh' => isset($_POST['pkh']) ? 'Ya' : 'Tidak',
    ];
}

function validate_pendaftar_payload(array $data): ?string
{
    $required = ['nama_lengkap', 'nik', 'kk', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'nama_ayah', 'nama_ibu', 'hp'];
    foreach ($required as $field) {
        if ($data[$field] === '') {
            return 'Mohon lengkapi seluruh isian wajib.';
        }
    }

    foreach (['nama_lengkap' => 'Nama lengkap', 'nama_ayah' => 'Nama ayah', 'nama_ibu' => 'Nama ibu', 'nama_wali' => 'Nama wali'] as $field => $label) {
        if ($data[$field] !== '' && !pendaftar_valid_name($data[$field])) {
            return $label . ' hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.';
        }
    }

    return null;
}

$total_pendaftar = count_pendaftar();
$is_admin_user = is_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'tambah') {
        $data = pendaftar_payload();
        $error = validate_pendaftar_payload($data);
        if ($error !== null) {
            flash('error', $error);
            redirect_to_pendaftar();
        }

        $no_pendaftaran = generate_no_pendaftaran();
        $stmt = $mysqli->prepare('INSERT INTO pendaftar (no_pendaftaran, nama_lengkap, nik, kk, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, status_keluarga, anak_ke, jumlah_saudara, asal_tk, nama_ayah, nama_ibu, pekerjaan_ayah, pekerjaan_ibu, nama_wali, pekerjaan_wali, email, hp, kip, pkh, status_daftar, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? ,"proses", NOW())');
        if ($stmt) {
            $stmt->bind_param(
                'sssssssssiisssssssssss',
                $no_pendaftaran,
                $data['nama_lengkap'],
                $data['nik'],
                $data['kk'],
                $data['jenis_kelamin'],
                $data['tempat_lahir'],
                $data['tanggal_lahir'],
                $data['alamat'],
                $data['status_keluarga'],
                $data['anak_ke'],
                $data['jumlah_saudara'],
                $data['asal_tk'],
                $data['nama_ayah'],
                $data['nama_ibu'],
                $data['pekerjaan_ayah'],
                $data['pekerjaan_ibu'],
                $data['nama_wali'],
                $data['pekerjaan_wali'],
                $data['email'],
                $data['hp'],
                $data['kip'],
                $data['pkh']
            );
            if ($stmt->execute()) {
                flash('success', 'Pendaftar berhasil ditambahkan. Nomor pendaftaran: ' . $no_pendaftaran);
                log_activity('create_pendaftar', 'Tambah pendaftar ' . $data['nama_lengkap'] . ' (' . $no_pendaftaran . ')');
            } else {
                flash('error', 'Gagal menambahkan pendaftar.');
            }
            $stmt->close();
        } else {
            flash('error', 'Gagal menyiapkan penyimpanan pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if ($aksi === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $data = pendaftar_payload();
        $error = validate_pendaftar_payload($data);
        if ($error !== null) {
            flash('error', $error);
            redirect_to_pendaftar();
        }

        $currentPendaftar = null;
        if ($currentStmt = $mysqli->prepare('SELECT no_pendaftaran, nama_lengkap FROM pendaftar WHERE id = ? LIMIT 1')) {
            $currentStmt->bind_param('i', $id);
            $currentStmt->execute();
            $currentResult = $currentStmt->get_result();
            $currentPendaftar = $currentResult->fetch_assoc() ?: null;
            $currentStmt->close();
        }

        $stmt = $mysqli->prepare('UPDATE pendaftar SET nama_lengkap=?, nik=?, kk=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, alamat=?, status_keluarga=?, anak_ke=?, jumlah_saudara=?, asal_tk=?, nama_ayah=?, nama_ibu=?, pekerjaan_ayah=?, pekerjaan_ibu=?, nama_wali=?, pekerjaan_wali=?, email=?, hp=?, kip=?, pkh=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param(
                'ssssssssiisssssssssssi',
                $data['nama_lengkap'],
                $data['nik'],
                $data['kk'],
                $data['jenis_kelamin'],
                $data['tempat_lahir'],
                $data['tanggal_lahir'],
                $data['alamat'],
                $data['status_keluarga'],
                $data['anak_ke'],
                $data['jumlah_saudara'],
                $data['asal_tk'],
                $data['nama_ayah'],
                $data['nama_ibu'],
                $data['pekerjaan_ayah'],
                $data['pekerjaan_ibu'],
                $data['nama_wali'],
                $data['pekerjaan_wali'],
                $data['email'],
                $data['hp'],
                $data['kip'],
                $data['pkh'],
                $id
            );
            if ($stmt->execute()) {
                flash('success', 'Data pendaftar berhasil diperbarui.');
                $noPendaftaranLog = $currentPendaftar['no_pendaftaran'] ?? ('ID ' . $id);
                $namaSebelumLog = $currentPendaftar['nama_lengkap'] ?? '-';
                log_activity('update_pendaftar', 'Edit pendaftar ' . $data['nama_lengkap'] . ' (' . $noPendaftaranLog . '), nama sebelumnya: ' . $namaSebelumLog);
            } else {
                flash('error', 'Gagal memperbarui data pendaftar.');
            }
            $stmt->close();
        } else {
            flash('error', 'Gagal menyiapkan pembaruan pendaftar.');
        }
        redirect_to_pendaftar();
    }

    if (!$is_admin_user) {
        flash('error', 'Panitia hanya dapat menambah dan mengedit data pendaftar.');
        redirect_to_pendaftar();
    }

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
                        $flash .= ' WhatsApp tidak terkirim. Pastikan nomor WA pendaftar valid dan token WA sudah benar.';
                    }
                } else {
                    $flash .= ' WhatsApp tidak dikirim karena nomor WA/HP pendaftar belum diisi.';
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
        <button type="button" class="btn btn-primary btn-sm mt-3 mt-sm-0" data-toggle="modal" data-target="#modalTambahPendaftar">
            <i class="fas fa-user-plus"></i> Tambah Pendaftar
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Pendaftar
                <span class="badge badge-pill badge-primary ml-2">Total: <?= (int)$total_pendaftar; ?></span>
            </h6>
            <div class="mt-2 mt-sm-0">
                <a href="export_pendaftar_excel.php" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Ekspor Excel
                </a>
                <a href="cetak_semua_kartu.php" target="_blank" class="btn btn-sm btn-info">
                    <i class="fas fa-id-card"></i> Cetak Semua Kartu
                </a>
                <a href="laporan_pendaftar.php" target="_blank" class="btn btn-sm btn-secondary">
                    <i class="fas fa-print"></i> Cetak Rekap
                </a>
                <?php if ($is_admin_user): ?>
                <button type="button" class="btn btn-sm btn-danger" id="btnHapusTerpilih">
                    <i class="fas fa-trash-alt"></i> Hapus Terpilih
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnResetTotal">
                    <i class="fas fa-exclamation-triangle"></i> Reset Total
                </button>
                <?php endif; ?>
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
                                <?php if ($is_admin_user): ?>
                                <th><input type="checkbox" id="checkAll"></th>
                                <?php endif; ?>
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
                                <?php if ($is_admin_user): ?>
                                <td>
                                    <input type="checkbox" name="ids[]" value="<?= (int)$row['id']; ?>"
                                        class="check-item">
                                </td>
                                <?php endif; ?>
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
                                    <?php $rowJson = htmlspecialchars(json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8'); ?>
                                    <button type="button" class="btn btn-sm btn-warning btn-edit-pendaftar mb-1"
                                        data-pendaftar="<?= $rowJson; ?>" data-toggle="modal" data-target="#modalEditPendaftar">
                                        Edit
                                    </button>
                                    <a href="cetak_kartu.php?id=<?= (int)$row['id']; ?>" target="_blank"
                                        class="btn btn-sm btn-info mb-1">
                                        Kartu
                                    </a>
                                    <?php if ($is_admin_user): ?>
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
                                    <?php endif; ?>
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

<style>
    #tablePendaftar {
        font-size: 0.78rem;
    }

    #tablePendaftar thead th {
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    #tablePendaftar tbody td {
        vertical-align: middle;
    }

    #tablePendaftar th:nth-child(<?= $is_admin_user ? 20 : 19; ?>),
    #tablePendaftar td:nth-child(<?= $is_admin_user ? 20 : 19; ?>) {
        max-width: 150px;
        min-width: 120px;
        width: 140px;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    #tablePendaftar .btn {
        font-size: 0.72rem;
        padding: 0.2rem 0.38rem;
    }
</style>

<?php
function render_pendaftar_modal_fields(): void
{
    ?>
    <h6 class="font-weight-bold text-primary mb-3">A. Identitas Calon Peserta Didik</h6>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_lengkap" class="form-control" pattern="^[A-Za-z\s'.,-]*$" required>
        </div>
        <div class="form-group col-md-3">
            <label>NIK *</label>
            <input type="text" name="nik" class="form-control" inputmode="numeric" pattern="[0-9]*" required>
        </div>
        <div class="form-group col-md-3">
            <label>No Kartu Keluarga *</label>
            <input type="text" name="kk" class="form-control" inputmode="numeric" pattern="[0-9]*" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Jenis Kelamin *</label>
            <select name="jenis_kelamin" class="form-control" required>
                <option value="">-- Pilih --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label>Tempat Lahir *</label>
            <input type="text" name="tempat_lahir" class="form-control" required>
        </div>
        <div class="form-group col-md-4">
            <label>Tanggal Lahir *</label>
            <input type="date" name="tanggal_lahir" class="form-control" required>
        </div>
    </div>
    <div class="form-group">
        <label>Alamat Lengkap *</label>
        <textarea name="alamat" class="form-control" rows="2" required></textarea>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Status Dalam Keluarga</label>
            <input type="text" name="status_keluarga" class="form-control">
        </div>
        <div class="form-group col-md-4">
            <label>Anak Ke</label>
            <input type="number" name="anak_ke" class="form-control" min="1">
        </div>
        <div class="form-group col-md-4">
            <label>Jumlah Saudara</label>
            <input type="number" name="jumlah_saudara" class="form-control" min="0">
        </div>
    </div>
    <div class="form-group">
        <label>Asal TK / RA</label>
        <input type="text" name="asal_tk" class="form-control">
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary mb-3">B. Identitas Orang Tua / Wali</h6>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label>Nama Ayah *</label>
            <input type="text" name="nama_ayah" class="form-control" pattern="^[A-Za-z\s'.,-]*$" required>
        </div>
        <div class="form-group col-md-6">
            <label>Nama Ibu *</label>
            <input type="text" name="nama_ibu" class="form-control" pattern="^[A-Za-z\s'.,-]*$" required>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Pekerjaan Ayah</label>
            <input type="text" name="pekerjaan_ayah" class="form-control">
        </div>
        <div class="form-group col-md-4">
            <label>Pekerjaan Ibu</label>
            <input type="text" name="pekerjaan_ibu" class="form-control">
        </div>
        <div class="form-group col-md-4">
            <label>Nama Wali</label>
            <input type="text" name="nama_wali" class="form-control" pattern="^[A-Za-z\s'.,-]*$">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label>Pekerjaan Wali</label>
            <input type="text" name="pekerjaan_wali" class="form-control">
        </div>
        <div class="form-group col-md-4">
            <label>Email Aktif</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="form-group col-md-4">
            <label>No HP / WA *</label>
            <input type="text" name="hp" class="form-control" inputmode="numeric" pattern="[0-9]*" required>
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary mb-3">C. Informasi Program Bantuan</h6>
    <div class="form-row">
        <div class="form-group col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="kip" value="Ya">
                <label class="form-check-label">Memiliki KIP</label>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pkh" value="Ya">
                <label class="form-check-label">Terdaftar Program PKH</label>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="modal fade" id="modalTambahPendaftar" tabindex="-1" role="dialog" aria-labelledby="modalTambahPendaftarLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahPendaftarLabel">Tambah Pendaftar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <?php render_pendaftar_modal_fields(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pendaftar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditPendaftar" tabindex="-1" role="dialog" aria-labelledby="modalEditPendaftarLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalEditPendaftarLabel">Edit Pendaftar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="formEditPendaftar">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" value="">
                    <?php render_pendaftar_modal_fields(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- handlers dipindah ke footer agar berjalan setelah jQuery & plugin dimuat -->
