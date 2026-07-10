<?php
require_admin();
require_once dirname(__DIR__) . '/update_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf(base_url('admin/pengaturan'));
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'update_save_config') {
        $manifestUrl = trim((string)($_POST['update_manifest_url'] ?? ''));
        if ($manifestUrl === '' || (filter_var($manifestUrl, FILTER_VALIDATE_URL) && updater_allowed_host($manifestUrl))) {
            set_option('update_manifest_url', $manifestUrl);
            flash('success', 'Konfigurasi update sistem disimpan.');
        } else {
            flash('error', 'URL manifest harus URL GitHub yang valid.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }

    if ($aksi === 'update_open') {
        $password = (string)($_POST['admin_password'] ?? '');
        $minutes = (int)($_POST['update_minutes'] ?? 15);
        if (updater_current_password_valid($password)) {
            updater_open_minutes($minutes);
            flash('success', 'Updater sistem dibuka sementara. Tutup kembali setelah perawatan selesai.');
            log_activity('system_update_open', 'Membuka updater sistem');
        } else {
            flash('error', 'Password admin tidak valid.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }

    if ($aksi === 'update_close') {
        updater_close();
        flash('success', 'Updater sistem ditutup.');
        log_activity('system_update_close', 'Menutup updater sistem');
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }

    if ($aksi === 'update_check') {
        if (!updater_is_open()) {
            flash('error', 'Updater masih tertutup. Buka mode perawatan terlebih dahulu.');
        } else {
            $updateError = null;
            $manifest = updater_manifest($updateError);
            if ($manifest === null) {
                flash('error', $updateError ?: 'Gagal membaca manifest update.');
            } else {
                flash('success', 'Rilis tersedia: versi ' . $manifest['version'] . '. SHA256: ' . $manifest['sha256']);
            }
        }
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }

    if ($aksi === 'update_install') {
        $password = (string)($_POST['admin_password'] ?? '');
        if (!updater_is_open()) {
            flash('error', 'Updater masih tertutup. Buka mode perawatan terlebih dahulu.');
        } elseif (!updater_current_password_valid($password)) {
            flash('error', 'Password admin tidak valid.');
        } else {
            $updateError = null;
            $manifest = updater_manifest($updateError);
            if ($manifest === null) {
                flash('error', $updateError ?: 'Gagal membaca manifest update.');
            } elseif (updater_install($manifest, $updateError)) {
                flash('success', 'Update sistem berhasil ke versi ' . $manifest['version'] . '. Updater sudah ditutup otomatis.');
            } else {
                flash('error', $updateError ?: 'Update sistem gagal.');
            }
        }
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }

    if ($aksi === 'reset_no') {
        if (reset_no_pendaftaran()) {
            flash('success', 'Nomor pendaftaran tahun berjalan berhasil direset ke 001.');
            log_activity('reset_no', 'Reset penomoran pendaftaran');
        } else {
            flash('error', 'Gagal mereset nomor pendaftaran.');
        }
        echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
        exit;
    }
    if ($aksi === 'reset_jadwal') {
        set_option('pendaftaran_start_at', '');
        set_option('pendaftaran_end_at', '');
        set_option('pendaftaran_open_until', '');
        flash('success', 'Jadwal pendaftaran berhasil direset.');
        log_activity('reset_jadwal', 'Reset jadwal pendaftaran');
        echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengaturan')) . '";</script>';
        exit;
    }
    $status_pendaftaran = isset($_POST['status_pendaftaran']) && $_POST['status_pendaftaran'] === 'buka' ? 'buka' : 'tutup';
    $info_pendaftaran = isset($_POST['info_pendaftaran']) ? $_POST['info_pendaftaran'] : '';
    $syarat_pendaftaran = isset($_POST['syarat_pendaftaran']) ? $_POST['syarat_pendaftaran'] : '';
    $fasilitas_siswa = isset($_POST['fasilitas_siswa']) ? $_POST['fasilitas_siswa'] : '';
    $alur_pendaftaran = isset($_POST['alur_pendaftaran']) ? $_POST['alur_pendaftaran'] : '';
    $tahun_ajaran = isset($_POST['tahun_ajaran']) ? trim($_POST['tahun_ajaran']) : '';
    $pendaftaran_start_at = isset($_POST['pendaftaran_start_at']) ? trim($_POST['pendaftaran_start_at']) : '';
    $pendaftaran_end_at = isset($_POST['pendaftaran_end_at']) ? trim($_POST['pendaftaran_end_at']) : '';

    // Jika dipaksa BUKA, pastikan jadwal tidak menghalangi (reset jadwal jika kadaluarsa/belum mulai)
    if ($status_pendaftaran === 'buka') {
        $tsNow = time();
        if ($pendaftaran_start_at !== '' && strtotime($pendaftaran_start_at) > $tsNow) {
            $pendaftaran_start_at = ''; 
        }
        if ($pendaftaran_end_at !== '' && strtotime($pendaftaran_end_at) < $tsNow) {
            $pendaftaran_end_at = '';
        }
    }

    set_option('status_pendaftaran', $status_pendaftaran);
    set_option('info_pendaftaran', $info_pendaftaran);
    set_option('syarat_pendaftaran', $syarat_pendaftaran);
    set_option('fasilitas_siswa', $fasilitas_siswa);
    set_option('alur_pendaftaran', $alur_pendaftaran);
    if ($tahun_ajaran !== '') {
        set_option('tahun_ajaran', $tahun_ajaran);
    }
    if ($pendaftaran_start_at !== '') {
        $dtStart = str_replace('T', ' ', $pendaftaran_start_at) . ':00';
        set_option('pendaftaran_start_at', $dtStart);
    } else {
        set_option('pendaftaran_start_at', '');
    }
    if ($pendaftaran_end_at !== '') {
        $dtEnd = str_replace('T', ' ', $pendaftaran_end_at) . ':00';
        set_option('pendaftaran_end_at', $dtEnd);
    } else {
        set_option('pendaftaran_end_at', '');
    }

    if (isset($_FILES['header_background']) && $_FILES['header_background']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads';
        $uploadError = null;
        $newName = secure_uploaded_image($_FILES['header_background'], 'header', $uploadDir, $uploadError);
        if ($newName === null) {
            flash('error', $uploadError ?: 'Background header tidak valid.');
            echo '<script>window.location.href="' . esc(base_url('admin/index.php?page=pengaturan')) . '";</script>';
            exit;
        }

        $oldBg = basename(get_option('header_background', ''));
        if ($oldBg !== '' && file_exists($uploadDir . '/' . $oldBg)) {
            unlink($uploadDir . '/' . $oldBg);
        }
        set_option('header_background', $newName);
    }

    flash('success', 'Pengaturan berhasil disimpan.');
    log_activity('update_settings', 'Perbarui pengaturan sistem');
    echo '<script>window.location.href="' . esc(base_url('admin/pengaturan')) . '";</script>';
    exit;
}

// Pastikan status sinkron dengan jadwal (otomatis tutup jika expired)
get_ppdb_status();

$status_pendaftaran = get_option('status_pendaftaran', 'tutup');
$info_pendaftaran = get_option('info_pendaftaran', '');
$syarat_pendaftaran = get_option('syarat_pendaftaran', '');
$fasilitas_siswa = get_option('fasilitas_siswa', '');
$alur_pendaftaran = get_option('alur_pendaftaran', '');
$header_background = get_option('header_background', '');
$default_tahun = date('Y') . '/' . (date('Y') + 1);
$tahun_ajaran = get_option('tahun_ajaran', $default_tahun);
$_start_raw = get_option('pendaftaran_start_at', '');
$_end_raw = get_option('pendaftaran_end_at', '');
$_start_val = $_start_raw !== '' ? date('Y-m-d\TH:i', strtotime($_start_raw)) : '';
$_end_val = $_end_raw !== '' ? date('Y-m-d\TH:i', strtotime($_end_raw)) : '';
$update_manifest_url = get_option('update_manifest_url', '');
$updater_is_open = updater_is_open();
$updater_enabled_until = (int)get_option('updater_enabled_until', '0');
$updater_until_label = $updater_enabled_until > 0 ? date('d-m-Y H:i:s', $updater_enabled_until) . ' WIB' : '-';
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengaturan PPDB</h1>
    </div>

    <form action="<?= base_url('admin/pengaturan'); ?>" method="POST" enctype="multipart/form-data">
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
                                class="form-control" rows="10"><?= $info_pendaftaran; ?></textarea>
                            <small class="text-muted">Gunakan editor untuk menulis informasi PPDB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Background Header</h6>
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
                                class="form-control" rows="8"><?= $alur_pendaftaran; ?></textarea>
                            <small class="text-muted">Tuliskan alur pendaftaran atau upload gambar alur.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Syarat Pendaftaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Syarat Pendaftaran (Timeline)</label>
                            <textarea name="syarat_pendaftaran" id="syarat_pendaftaran"
                                class="form-control" rows="10"><?= $syarat_pendaftaran; ?></textarea>
                            <small class="text-muted">Tuliskan syarat dalam bentuk urutan/timeline.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Fasilitas Siswa Baru</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Daftar Fasilitas</label>
                            <textarea name="fasilitas_siswa" id="fasilitas_siswa"
                                class="form-control" rows="10"><?= $fasilitas_siswa; ?></textarea>
                            <small class="text-muted">Tuliskan fasilitas yang akan didapatkan siswa baru.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Jadwal Pendaftaran</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="datetime-local" name="pendaftaran_start_at" class="form-control" value="<?= esc($_start_val); ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="datetime-local" name="pendaftaran_end_at" class="form-control" value="<?= esc($_end_val); ?>">
                        </div>
                        <small class="text-muted">Status pendaftaran otomatis mengikuti rentang waktu ini.</small>
                        <div class="mt-3">
                            <button type="submit" name="aksi" value="reset_jadwal" class="btn btn-sm btn-danger">Reset Jadwal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mb-4">Simpan Pengaturan</button>
    </form>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Perawatan Sistem</h6>
            <span class="badge badge-<?= $updater_is_open ? 'warning' : 'secondary'; ?>">
                <?= $updater_is_open ? 'Updater terbuka sampai ' . esc($updater_until_label) : 'Updater tertutup'; ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <form method="post" class="mb-3">
                        <input type="hidden" name="aksi" value="update_save_config">
                        <div class="form-group">
                            <label>URL Manifest GitHub</label>
                            <input type="url" name="update_manifest_url" class="form-control" value="<?= esc($update_manifest_url); ?>" placeholder="https://raw.githubusercontent.com/user/repo/main/update.json">
                            <small class="text-muted">Manifest wajib berisi version, zip_url, dan sha256.</small>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan Manifest</button>
                    </form>

                    <form method="post" class="mb-3">
                        <input type="hidden" name="aksi" value="update_open">
                        <div class="form-group">
                            <label>Password Admin</label>
                            <input type="password" name="admin_password" class="form-control" autocomplete="current-password" required>
                        </div>
                        <div class="form-group">
                            <label>Durasi Maintenance</label>
                            <select name="update_minutes" class="form-control">
                                <option value="15">15 menit</option>
                                <option value="30">30 menit</option>
                                <option value="60">60 menit</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-warning">Buka Updater</button>
                    </form>

                    <?php if ($updater_is_open): ?>
                    <form method="post">
                        <input type="hidden" name="aksi" value="update_close">
                        <button type="submit" class="btn btn-sm btn-secondary">Tutup Updater Sekarang</button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="alert alert-light border">
                        <div class="font-weight-bold mb-2">Format manifest:</div>
                        <pre class="mb-0" style="white-space:pre-wrap;font-size:0.78rem;">{
  "version": "20260710120000",
  "zip_url": "https://github.com/user/repo/releases/download/v1.0.0/ppdb.zip",
  "sha256": "hash_sha256_zip_64_karakter",
  "notes": "Ringkasan perubahan"
}</pre>
                    </div>

                    <form method="post" class="mb-3">
                        <input type="hidden" name="aksi" value="update_check">
                        <button type="submit" class="btn btn-sm btn-info" <?= $updater_is_open ? '' : 'disabled'; ?>>Cek Rilis</button>
                    </form>

                    <form method="post">
                        <input type="hidden" name="aksi" value="update_install">
                        <div class="form-group">
                            <label>Konfirmasi Password Admin</label>
                            <input type="password" name="admin_password" class="form-control" autocomplete="current-password" <?= $updater_is_open ? 'required' : 'disabled'; ?>>
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger" <?= $updater_is_open ? '' : 'disabled'; ?>>Install Update Terverifikasi</button>
                        <small class="text-muted d-block mt-2">Sistem akan backup file terlebih dahulu, memverifikasi SHA256, lalu menutup updater otomatis setelah berhasil.</small>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    window.initEditors = function () {
        if (!window.jQuery) return;

        (function ($) {
            if (!$.fn.wysihtml5) {
                $.fn.wysihtml5 = function () {
                    return this.each(function () {
                        var $textarea = $(this);
                        var initial = $textarea.val() || '';
                        var $wrapper = $('<div class="wysihtml5-wrapper mb-2"></div>');
                        var $toolbar = $('<div class="btn-toolbar mb-2 wysihtml5-toolbar" role="toolbar"></div>');

                        var groups = [
                            [
                                { cmd: 'bold', icon: 'fa-bold', title: 'Tebal' },
                                { cmd: 'italic', icon: 'fa-italic', title: 'Miring' },
                                { cmd: 'underline', icon: 'fa-underline', title: 'Garis bawah' }
                            ],
                            [
                                { cmd: 'insertUnorderedList', icon: 'fa-list-ul', title: 'Bullet' },
                                { cmd: 'insertOrderedList', icon: 'fa-list-ol', title: 'Numbered' }
                            ],
                            [
                                { cmd: 'justifyLeft', icon: 'fa-align-left', title: 'Rata kiri' },
                                { cmd: 'justifyCenter', icon: 'fa-align-center', title: 'Rata tengah' },
                                { cmd: 'justifyRight', icon: 'fa-align-right', title: 'Rata kanan' }
                            ],
                            [
                                { cmd: 'createLink', icon: 'fa-link', title: 'Tambah link' },
                                { cmd: 'removeFormat', icon: 'fa-eraser', title: 'Bersihkan format' }
                            ]
                        ];

                        $.each(groups, function (_, groupButtons) {
                            var $group = $('<div class="btn-group mr-2" role="group"></div>');
                            $.each(groupButtons, function (_, b) {
                                var $btn = $('<button type="button" class="btn btn-sm btn-light border"></button>');
                                $btn.attr('data-command', b.cmd);
                                $btn.attr('title', b.title);
                                $btn.append($('<i class="fas ' + b.icon + '"></i>'));
                                $group.append($btn);
                            });
                            $toolbar.append($group);
                        });

                        var $editor = $('<div class="wysihtml5-editor form-control" contenteditable="true"></div>');
                        $editor.html(initial);

                        $textarea.after($wrapper);
                        $wrapper.append($toolbar).append($editor);
                        $textarea.hide();

                        $toolbar.on('click', 'button[data-command]', function (e) {
                            e.preventDefault();
                            var cmd = $(this).attr('data-command');
                            $editor.focus();
                            if (cmd === 'createLink') {
                                var url = window.prompt('Masukkan URL', 'https://');
                                if (url) {
                                    if (!/^https?:\/\//i.test(url)) {
                                        url = 'http://' + url;
                                    }
                                    document.execCommand('createLink', false, url);
                                }
                                return;
                            }
                            document.execCommand(cmd, false, null);
                        });

                        var sync = function () {
                            $textarea.val($editor.html());
                        };

                        $editor.on('keyup blur input', sync);
                        $textarea.closest('form').on('submit', sync);
                    });
                };
            }

            $('#info_pendaftaran,#alur_pendaftaran,#syarat_pendaftaran,#fasilitas_siswa').wysihtml5();
        })(jQuery);
    };
</script>
