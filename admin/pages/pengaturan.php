<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';
    if ($aksi === 'reset_no') {
        if (reset_no_pendaftaran()) {
            flash('success', 'Nomor pendaftaran tahun berjalan berhasil direset ke 001.');
            log_activity('reset_no', 'Reset penomoran pendaftaran');
        } else {
            flash('error', 'Gagal mereset nomor pendaftaran.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengaturan')) . '";</script>';
        exit;
    }
    $status_pendaftaran = isset($_POST['status_pendaftaran']) && $_POST['status_pendaftaran'] === 'buka' ? 'buka' : 'tutup';
    $info_pendaftaran = isset($_POST['info_pendaftaran']) ? $_POST['info_pendaftaran'] : '';
    $syarat_pendaftaran = isset($_POST['syarat_pendaftaran']) ? $_POST['syarat_pendaftaran'] : '';
    $alur_pendaftaran = isset($_POST['alur_pendaftaran']) ? $_POST['alur_pendaftaran'] : '';
    $tahun_ajaran = isset($_POST['tahun_ajaran']) ? trim($_POST['tahun_ajaran']) : '';

    set_option('status_pendaftaran', $status_pendaftaran);
    set_option('info_pendaftaran', $info_pendaftaran);
    set_option('syarat_pendaftaran', $syarat_pendaftaran);
    set_option('alur_pendaftaran', $alur_pendaftaran);
    if ($tahun_ajaran !== '') {
        set_option('tahun_ajaran', $tahun_ajaran);
    }

    if (isset($_FILES['header_background']) && $_FILES['header_background']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['header_background']['tmp_name'];
        $name = $_FILES['header_background']['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array($ext, $allowed, true)) {
            $uploadDir = __DIR__ . '/../../uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newName = 'header-' . date('YmdHis') . '.' . $ext;
            $dest = $uploadDir . '/' . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                set_option('header_background', $newName);
            }
        }
    }

    flash('success', 'Pengaturan berhasil disimpan.');
    echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengaturan')) . '";</script>';
    exit;
}

$status_pendaftaran = get_option('status_pendaftaran', 'tutup');
$info_pendaftaran = get_option('info_pendaftaran', '');
$syarat_pendaftaran = get_option('syarat_pendaftaran', '');
$alur_pendaftaran = get_option('alur_pendaftaran', '');
$header_background = get_option('header_background', '');
$default_tahun = date('Y') . '/' . (date('Y') + 1);
$tahun_ajaran = get_option('tahun_ajaran', $default_tahun);
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengaturan PPDB</h1>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Informasi Pendaftaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Status Pendaftaran</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="statusBuka" name="status_pendaftaran" value="buka"
                                    class="custom-control-input" <?= $status_pendaftaran === 'buka' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="statusBuka">Dibuka</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="statusTutup" name="status_pendaftaran" value="tutup"
                                    class="custom-control-input" <?= $status_pendaftaran !== 'buka' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="statusTutup">Ditutup</label>
                            </div>
                            <div class="mt-3">
                                <button type="submit" name="aksi" value="reset_no" class="btn btn-danger">Reset Nomor Pendaftaran</button>
                                <small class="text-muted d-block mt-1">Mereset urutan nomor, tahun aktif tetap.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control"
                                value="<?= esc($tahun_ajaran); ?>" placeholder="contoh: 2025/2026">
                            <small class="text-muted">Gunakan format YYYY/YYYY.</small>
                        </div>
                        <div class="form-group">
                            <label>Informasi Pendaftaran</label>
                            <textarea name="info_pendaftaran" id="info_pendaftaran"
                                class="form-control" rows="10"><?= esc($info_pendaftaran); ?></textarea>
                            <small class="text-muted">Gunakan editor untuk menulis informasi PPDB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Background Header & Syarat Pendaftaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Upload Background Header</label>
                            <input type="file" name="header_background" class="form-control-file">
                            <small class="text-muted">Format: JPG/PNG.</small>
                            <?php if ($header_background !== ''): ?>
                            <div class="mt-2">
                                <img src="<?= esc(base_url('uploads/' . $header_background)); ?>" alt="Header"
                                    class="img-fluid rounded">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Alur PPDB Online</label>
                            <textarea name="alur_pendaftaran" id="alur_pendaftaran"
                                class="form-control" rows="8"><?= esc($alur_pendaftaran); ?></textarea>
                            <small class="text-muted">Tuliskan alur pendaftaran atau upload gambar alur.</small>
                        </div>
                        <div class="form-group">
                            <label>Syarat Pendaftaran (Timeline)</label>
                            <textarea name="syarat_pendaftaran" id="syarat_pendaftaran"
                                class="form-control" rows="10"><?= esc($syarat_pendaftaran); ?></textarea>
                            <small class="text-muted">Tuliskan syarat dalam bentuk urutan/timeline.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-4">Simpan Pengaturan</button>
    </form>

</div>

<script>
    window.initEditors = function () {};
</script>

