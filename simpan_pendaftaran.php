<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url());
    exit;
}

$status_pendaftaran = get_option('status_pendaftaran', 'tutup');
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

if ($nama_lengkap === '' || $nik === '' || $kk === '' || $jenis_kelamin === '' || $tempat_lahir === '' || $tanggal_lahir === '' || $alamat === '' || $nama_ayah === '' || $nama_ibu === '' || $hp === '') {
    flash('error', 'Mohon lengkapi seluruh isian wajib.');
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
) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, "proses", NOW())');

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
    flash('success', 'Pendaftaran berhasil dikirim. Nomor pendaftaran Anda: ' . $no_pendaftaran);
} else {
    flash('error', 'Gagal menyimpan data pendaftaran.');
}

$stmt->close();

header('Location: ' . base_url());
exit;

