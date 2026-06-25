<?php
$madrasah_readonly = !is_admin();

// Pastikan kolom terbaru tersedia (logo, nama_panitia, nama_kepala)
if ($chk = $mysqli->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'madrasah' AND COLUMN_NAME IN ('logo','nama_panitia','nama_kepala')")) {
    $chk->execute();
    $res = $chk->get_result();
    $existing = [];
    while ($r = $res->fetch_assoc()) {
        $existing[] = $r['COLUMN_NAME'];
    }
    $chk->close();
    if (!in_array('logo', $existing, true)) {
        $mysqli->query("ALTER TABLE madrasah ADD COLUMN logo VARCHAR(255) DEFAULT NULL");
    }
    if (!in_array('nama_panitia', $existing, true)) {
        $mysqli->query("ALTER TABLE madrasah ADD COLUMN nama_panitia VARCHAR(150) DEFAULT NULL");
    }
    if (!in_array('nama_kepala', $existing, true)) {
        $mysqli->query("ALTER TABLE madrasah ADD COLUMN nama_kepala VARCHAR(150) DEFAULT NULL");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($madrasah_readonly) {
        flash('error', 'Data madrasah hanya dapat dilihat oleh panitia.');
        echo '<script>window.location.href="' . esc(base_url('admin/madrasah')) . '";</script>';
        exit;
    }

    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $website = isset($_POST['website']) ? trim($_POST['website']) : '';
    $hp_kepala = isset($_POST['hp_kepala']) ? trim($_POST['hp_kepala']) : '';
    $hp_panitia = isset($_POST['hp_panitia']) ? trim($_POST['hp_panitia']) : '';
    $nama_panitia = isset($_POST['nama_panitia']) ? trim($_POST['nama_panitia']) : '';
    $nama_kepala = isset($_POST['nama_kepala']) ? trim($_POST['nama_kepala']) : '';
    $logoName = null;

    if ($nama === '') {
        flash('error', 'Nama madrasah wajib diisi.');
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=madrasah')) . '";</script>';
        exit;
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['logo']['tmp_name'];
        $name = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed, true)) {
            $uploadDir = __DIR__ . '/../../uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Hapus logo lama jika ada
            $oldLogoQuery = $mysqli->query("SELECT logo FROM madrasah LIMIT 1");
            if ($oldLogoQuery && $oldLogoRow = $oldLogoQuery->fetch_assoc()) {
                $oldLogo = $oldLogoRow['logo'];
                if (!empty($oldLogo) && file_exists($uploadDir . '/' . $oldLogo)) {
                    unlink($uploadDir . '/' . $oldLogo);
                }
            }

            $logoName = 'logo-' . date('YmdHis') . '.' . $ext;
            if (!move_uploaded_file($tmp, $uploadDir . '/' . $logoName)) {
                $logoName = null;
            }
        }
    }

    $result = $mysqli->query('SELECT * FROM madrasah LIMIT 1');
    if ($result && $row = $result->fetch_assoc()) {
        $id = (int)$row['id'];
        if ($logoName === null) {
            $logoName = $row['logo'] ?? null;
        }
        $stmt = $mysqli->prepare('UPDATE madrasah SET nama=?, alamat=?, email=?, website=?, hp_kepala=?, hp_panitia=?, nama_panitia=?, nama_kepala=?, logo=? WHERE id=?');
        if ($stmt) {
            $stmt->bind_param('sssssssssi', $nama, $alamat, $email, $website, $hp_kepala, $hp_panitia, $nama_panitia, $nama_kepala, $logoName, $id);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        $stmt = $mysqli->prepare('INSERT INTO madrasah (nama, alamat, email, website, hp_kepala, hp_panitia, nama_panitia, nama_kepala, logo) VALUES (?,?,?,?,?,?,?,?,?)');
        if ($stmt) {
            $stmt->bind_param('sssssssss', $nama, $alamat, $email, $website, $hp_kepala, $hp_panitia, $nama_panitia, $nama_kepala, $logoName);
            $stmt->execute();
            $stmt->close();
        }
    }
    if ($result) {
        $result->free();
    }

    flash('success', 'Data madrasah berhasil disimpan.');
    log_activity('update_madrasah', 'Perbarui data madrasah');
    echo '<script>window.location.href="' . esc(base_url('admin/madrasah')) . '";</script>';
    exit;
}

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => '',
    'email' => '',
    'website' => '',
    'hp_kepala' => '',
    'hp_panitia' => '',
    'nama_panitia' => '',
    'nama_kepala' => '',
    'logo' => '',
];

if ($result = $mysqli->query('SELECT * FROM madrasah LIMIT 1')) {
    if ($row = $result->fetch_assoc()) {
        $madrasah = $row;
    }
    $result->free();
}
?>
<style>
    .madrasah-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .madrasah-info-col {
        min-width: 0;
    }

    .madrasah-info-item {
        padding: 0.95rem 1rem;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        background: #fff;
        min-height: 76px;
    }

    .madrasah-info-label {
        color: #858796;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    .madrasah-info-value {
        color: #3a3b45;
        font-size: 0.95rem;
        overflow-wrap: anywhere;
    }

    .madrasah-logo-box {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border: 1px dashed #d1d3e2;
        border-radius: 0.35rem;
        background: #f8f9fc;
    }

    .madrasah-logo-box img {
        max-height: 120px;
        max-width: 220px;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .madrasah-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Madrasah</h1>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Informasi Madrasah</h6>
                    </div>
                    <?php if (!$madrasah_readonly): ?>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalMadrasah">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="madrasah-info-grid">
                        <div class="madrasah-info-col">
                            <div class="madrasah-info-item mb-3">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-school mr-1"></i> Nama Madrasah
                                </div>
                                <div class="madrasah-info-value font-weight-bold">
                                    <?= esc($madrasah['nama']); ?>
                                </div>
                            </div>

                            <div class="madrasah-info-item mb-3">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Alamat
                                </div>
                                <div class="madrasah-info-value">
                                    <?= esc($madrasah['alamat']); ?>
                                </div>
                            </div>

                            <div class="madrasah-info-item mb-3">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-envelope mr-1"></i> Email
                                </div>
                                <div class="madrasah-info-value">
                                    <?= esc($madrasah['email']); ?>
                                </div>
                            </div>

                            <div class="madrasah-info-item">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-globe mr-1"></i> Website
                                </div>
                                <div class="madrasah-info-value">
                                    <?= esc($madrasah['website']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="madrasah-info-col">
                            <div class="madrasah-info-item mb-3">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-image mr-1"></i> Logo Madrasah
                                </div>
                                <div class="madrasah-logo-box">
                                    <?php if (!empty($madrasah['logo'])): ?>
                                    <img src="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>" alt="Logo Madrasah">
                                    <?php else: ?>
                                    <span class="text-muted small mb-0">Belum ada logo</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="madrasah-info-item mb-3">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-user-tie mr-1"></i> Kepala Madrasah
                                </div>
                                <div class="madrasah-info-value font-weight-bold">
                                    <?= esc($madrasah['nama_kepala'] ?? ''); ?>
                                </div>
                                <div class="madrasah-info-value text-muted mt-1">
                                    HP: <?= esc($madrasah['hp_kepala']); ?>
                                </div>
                            </div>

                            <div class="madrasah-info-item">
                                <div class="madrasah-info-label">
                                    <i class="fas fa-users mr-1"></i> Panitia PPDB
                                </div>
                                <div class="madrasah-info-value font-weight-bold">
                                    <?= esc($madrasah['nama_panitia']); ?>
                                </div>
                                <div class="madrasah-info-value text-muted mt-1">
                                    HP: <?= esc($madrasah['hp_panitia']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php if (!$madrasah_readonly): ?>
<div class="modal fade" id="modalMadrasah" tabindex="-1" role="dialog" aria-labelledby="modalMadrasahLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalMadrasahLabel">Edit Data Madrasah</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Madrasah *</label>
                        <input type="text" name="nama" class="form-control" required
                            value="<?= esc($madrasah['nama']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Logo Madrasah</label>
                        <div class="mb-2">
                            <img id="previewLogo"
                                src="<?= !empty($madrasah['logo']) ? esc(base_url('uploads/' . $madrasah['logo'])) : '' ?>"
                                alt="Pratinjau logo"
                                style="max-height:60px; <?= !empty($madrasah['logo']) ? '' : 'display:none;'; ?>">
                        </div>
                        <input type="file" name="logo" class="form-control-file" accept="image/jpeg,image/png,.jpg,.jpeg,.png">
                        <small class="text-muted d-block">Format: JPG/PNG</small>
                    </div>
                    <div class="form-group">
                        <label>Alamat Madrasah</label>
                        <textarea name="alamat" class="form-control"
                            rows="2"><?= esc($madrasah['alamat']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Email Madrasah</label>
                        <input type="email" name="email" class="form-control" value="<?= esc($madrasah['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Website Madrasah</label>
                        <input type="text" name="website" class="form-control"
                            value="<?= esc($madrasah['website']); ?>">
                    </div>
                    <div class="form-group">
                        <label>HP Kepala</label>
                        <input type="text" name="hp_kepala" class="form-control"
                            value="<?= esc($madrasah['hp_kepala']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Kepala Madrasah</label>
                        <input type="text" name="nama_kepala" class="form-control"
                            value="<?= esc($madrasah['nama_kepala'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>HP Panitia</label>
                        <input type="text" name="hp_panitia" class="form-control"
                            value="<?= esc($madrasah['hp_panitia']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Nama Panitia</label>
                        <input type="text" name="nama_panitia" class="form-control"
                            value="<?= esc($madrasah['nama_panitia']); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.querySelector('input[name="logo"]');
    var img = document.getElementById('previewLogo');
    if (input && img) {
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    img.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>
<?php endif; ?>
