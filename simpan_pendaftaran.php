<?php
require_once dirname(__FILE__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url());
    exit;
}

if (!csrf_verify()) {
    flash('error', 'Sesi form tidak valid. Silakan buka ulang formulir pendaftaran.');
    header('Location: ' . base_url());
    exit;
}

$status_pendaftaran = get_ppdb_status();
if ($status_pendaftaran !== 'buka') {
    flash('error', 'Pendaftaran sedang ditutup.');
    header('Location: ' . base_url());
    exit;
}

function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

$nama_lengkap = post('nama_lengkap');
$nik = post('nik');
$kk = post('kk');
$jenis_kelamin = post('jenis_kelamin');
$tempat_lahir = post('tempat_lahir');
$tanggal_lahir = post('tanggal_lahir');
$alamat = post('alamat');
$status_keluarga = post('status_keluarga');
$anak_ke = post('anak_ke');
$jumlah_saudara = post('jumlah_saudara');
$asal_tk = post('asal_tk');
$nama_ayah = post('nama_ayah');
$nama_ibu = post('nama_ibu');
$pekerjaan_ayah = post('pekerjaan_ayah');
$pekerjaan_ibu = post('pekerjaan_ibu');
$nama_wali = post('nama_wali');
$pekerjaan_wali = post('pekerjaan_wali');
$email = post('email');
$hp = post('hp');
$kip = isset($_POST['kip']) ? 'Ya' : 'Tidak';
$pkh = isset($_POST['pkh']) ? 'Ya' : 'Tidak';

function validate_name($name) {
    // Only allow letters (including Indonesian accented), spaces, apostrophes, dots, commas, and hyphens
    return preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\'.,-]*$/u', $name) === 1;
}

if ($nama_lengkap === '' || $nik === '' || $kk === '' || $jenis_kelamin === '' || $tempat_lahir === '' || $tanggal_lahir === '' || $alamat === '' || $status_keluarga === '' || $anak_ke === '' || $jumlah_saudara === '' || $asal_tk === '' || $nama_ayah === '' || $nama_ibu === '' || $pekerjaan_ayah === '' || $pekerjaan_ibu === '' || $nama_wali === '' || $pekerjaan_wali === '' || $hp === '') {
    flash('error', 'Mohon lengkapi seluruh isian wajib.');
    header('Location: ' . base_url());
    exit;
}

if (!validate_name($nama_lengkap)) {
    flash('error', 'Nama lengkap hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.');
    header('Location: ' . base_url());
    exit;
}
if (!validate_name($nama_ayah)) {
    flash('error', 'Nama ayah hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.');
    header('Location: ' . base_url());
    exit;
}
if (!validate_name($nama_ibu)) {
    flash('error', 'Nama ibu hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.');
    header('Location: ' . base_url());
    exit;
}
if (!validate_name($nama_wali)) {
    flash('error', 'Nama wali hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.');
    header('Location: ' . base_url());
    exit;
}

$pendaftarData = [
    'nama_lengkap' => $nama_lengkap,
    'nik' => $nik,
    'kk' => $kk,
    'jenis_kelamin' => $jenis_kelamin,
    'tempat_lahir' => $tempat_lahir,
    'tanggal_lahir' => $tanggal_lahir,
    'alamat' => $alamat,
    'status_keluarga' => $status_keluarga,
    'anak_ke' => $anak_ke,
    'jumlah_saudara' => $jumlah_saudara,
    'asal_tk' => $asal_tk,
    'nama_ayah' => $nama_ayah,
    'nama_ibu' => $nama_ibu,
    'pekerjaan_ayah' => $pekerjaan_ayah,
    'pekerjaan_ibu' => $pekerjaan_ibu,
    'nama_wali' => $nama_wali,
    'pekerjaan_wali' => $pekerjaan_wali,
    'email' => $email,
    'hp' => $hp,
    'kip' => $kip,
    'pkh' => $pkh,
];

$duplicate = pendaftar_duplicate_exists($pendaftarData);
if ($duplicate !== null) {
    flash('error', pendaftar_duplicate_message($duplicate));
    header('Location: ' . base_url());
    exit;
}

$no_pendaftaran = generate_no_pendaftaran();

$stmt = $mysqli->prepare('INSERT INTO pendaftar (
    no_pendaftaran,
    nama_lengkap,
    nik,
    kk,
    jenis_kelamin,
    tempat_lahir,
    tanggal_lahir,
    alamat,
    status_keluarga,
    anak_ke,
    jumlah_saudara,
    asal_tk,
    nama_ayah,
    nama_ibu,
    pekerjaan_ayah,
    pekerjaan_ibu,
    nama_wali,
    pekerjaan_wali,
    email,
    hp,
    kip,
    pkh,
    status_daftar,
    created_at
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"proses", NOW())');

if ($stmt === false) {
    flash('error', 'Terjadi kesalahan saat menyimpan data.');
    header('Location: ' . base_url());
    exit;
}

$stmt->bind_param(
    'sssssssssiisssssssssss',
    $no_pendaftaran,
    $nama_lengkap,
    $nik,
    $kk,
    $jenis_kelamin,
    $tempat_lahir,
    $tanggal_lahir,
    $alamat,
    $status_keluarga,
    $anak_ke,
    $jumlah_saudara,
    $asal_tk,
    $nama_ayah,
    $nama_ibu,
    $pekerjaan_ayah,
    $pekerjaan_ibu,
    $nama_wali,
    $pekerjaan_wali,
    $email,
    $hp,
    $kip,
    $pkh
);

if ($stmt->execute()) {
    $insert_id = $mysqli->insert_id;
    $access_token = pendaftar_access_token($insert_id, $no_pendaftaran);
    
    // Insert Notification
    $notif_title = "Pendaftaran Baru";
    $notif_content = json_encode([
        'nama' => $nama_lengkap,
        'no_pendaftaran' => $no_pendaftaran,
        'waktu' => date('Y-m-d H:i:s')
    ]);
    
    $created_at = date('Y-m-d H:i:s');
    $stmt_notif = $mysqli->prepare('INSERT INTO notifications (type, title, content, created_at) VALUES ("registration", ?, ?, ?)');
    if ($stmt_notif) {
        $stmt_notif->bind_param('sss', $notif_title, $notif_content, $created_at);
        $stmt_notif->execute();
        $stmt_notif->close();
    }

    // Log Activity
    log_activity('pendaftaran_baru', 'Pendaftaran baru atas nama ' . $nama_lengkap . ' (' . $no_pendaftaran . ')');

    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Pendaftaran Berhasil | <?= htmlspecialchars($no_pendaftaran, ENT_QUOTES, 'UTF-8'); ?></title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Pendaftaran Berhasil',
            text: 'Nomor pendaftaran Anda: <?= addslashes($no_pendaftaran); ?>',
            confirmButtonText: 'Cetak Kartu'
        }).then(function () {
            var url = '<?= addslashes(base_url('cetak_kartu?id=' . $insert_id . '&token=' . $access_token)); ?>';
            window.open(url, '_blank');
            setTimeout(function () {
                window.location.href = '<?= addslashes(base_url()); ?>';
            }, 500);
        });
    </script>
    </body>
    </html>
    <?php
    exit;
} else {
    $stmt->close();
    flash('error', 'Gagal menyimpan data pendaftaran.');
    header('Location: ' . base_url());
    exit;
}
