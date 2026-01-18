<?php
require __DIR__ . '/config.php';

if (!isset($_GET['id'])) {
    echo 'Data tidak ditemukan.';
    exit;
}

$id = (int)$_GET['id'];

$stmt = $mysqli->prepare('SELECT * FROM pendaftar WHERE id = ? LIMIT 1');
if (!$stmt) {
    echo 'Data tidak ditemukan.';
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

if (!$data) {
    echo 'Data tidak ditemukan.';
    exit;
}

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => '',
];

if ($res = $mysqli->query('SELECT nama, alamat FROM madrasah LIMIT 1')) {
    if ($row = $res->fetch_assoc()) {
        $madrasah = $row;
    }
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Pendaftaran <?= esc($data['no_pendaftaran']); ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 20px; background-color: #f3f4f6; }
        .kartu-wrapper { max-width: 800px; margin: 0 auto; background-color: #ffffff; border: 1px solid #d1d5db; padding: 24px; }
        .kartu-header { text-align: center; margin-bottom: 16px; }
        .kartu-header h1 { font-size: 20px; margin: 0; }
        .kartu-header h2 { font-size: 18px; margin: 4px 0 0 0; }
        .kartu-header p { margin: 4px 0; font-size: 12px; }
        .kartu-no { text-align: center; margin: 16px 0; font-size: 16px; font-weight: bold; padding: 8px; border: 1px dashed #4b5563; }
        .section-title { font-weight: bold; margin-top: 16px; margin-bottom: 8px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        td { padding: 4px 6px; vertical-align: top; }
        .label { width: 30%; }
        .value { width: 70%; }
        .ttd-wrapper { margin-top: 24px; display: flex; justify-content: flex-end; font-size: 13px; }
        .ttd { text-align: center; width: 260px; }
        .ttd-space { height: 60px; }
        @media print { body { background-color: #ffffff; padding: 0; } .kartu-wrapper { border: none; margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="kartu-wrapper">
        <div class="kartu-header">
            <h1>FORMULIR PENDAFTARAN PESERTA DIDIK BARU</h1>
            <h2><?= esc($madrasah['nama']); ?></h2>
            <p><?= esc($madrasah['alamat']); ?></p>
            <p>Tahun Pelajaran <?= date('Y'); ?>/<?= date('Y') + 1; ?></p>
        </div>

        <div class="kartu-no">Nomor Pendaftaran: <?= esc($data['no_pendaftaran']); ?></div>

        <div class="section-title">A. Identitas Calon Peserta Didik</div>
        <table>
            <tr><td class="label">Nama Lengkap</td><td class="value"><?= esc($data['nama_lengkap']); ?></td></tr>
            <tr><td class="label">NIK</td><td class="value"><?= esc($data['nik']); ?></td></tr>
            <tr><td class="label">No KK</td><td class="value"><?= esc($data['kk']); ?></td></tr>
            <tr><td class="label">Jenis Kelamin</td><td class="value"><?= esc($data['jenis_kelamin']); ?></td></tr>
            <tr><td class="label">Tempat, Tanggal Lahir</td><td class="value"><?= esc($data['tempat_lahir']); ?>, <?= esc($data['tanggal_lahir']); ?></td></tr>
            <tr><td class="label">Alamat</td><td class="value"><?= esc($data['alamat']); ?></td></tr>
            <tr><td class="label">Status Dalam Keluarga</td><td class="value"><?= esc($data['status_keluarga']); ?></td></tr>
            <tr><td class="label">Anak Ke / Jumlah Saudara</td><td class="value"><?= esc((string)$data['anak_ke']); ?> dari <?= esc((string)$data['jumlah_saudara']); ?> bersaudara</td></tr>
            <tr><td class="label">Asal TK/RA</td><td class="value"><?= esc($data['asal_tk']); ?></td></tr>
        </table>

        <div class="section-title">B. Identitas Orang Tua / Wali</div>
        <table>
            <tr><td class="label">Nama Ayah</td><td class="value"><?= esc($data['nama_ayah']); ?></td></tr>
            <tr><td class="label">Pekerjaan Ayah</td><td class="value"><?= esc($data['pekerjaan_ayah']); ?></td></tr>
            <tr><td class="label">Nama Ibu</td><td class="value"><?= esc($data['nama_ibu']); ?></td></tr>
            <tr><td class="label">Pekerjaan Ibu</td><td class="value"><?= esc($data['pekerjaan_ibu']); ?></td></tr>
            <tr><td class="label">Nama Wali</td><td class="value"><?= esc($data['nama_wali']); ?></td></tr>
            <tr><td class="label">Pekerjaan Wali</td><td class="value"><?= esc($data['pekerjaan_wali']); ?></td></tr>
            <tr><td class="label">Email</td><td class="value"><?= esc($data['email']); ?></td></tr>
            <tr><td class="label">No HP/WA</td><td class="value"><?= esc($data['hp']); ?></td></tr>
        </table>

        <div class="section-title">C. Informasi Program Bantuan</div>
        <table>
            <tr><td class="label">Memiliki KIP</td><td class="value"><?= esc($data['kip']); ?></td></tr>
            <tr><td class="label">Peserta PKH</td><td class="value"><?= esc($data['pkh']); ?></td></tr>
        </table>

        <div class="ttd-wrapper">
            <div class="ttd">
                <div><?= esc($madrasah['alamat']); ?>, <?= date('d-m-Y'); ?></div>
                <div class="ttd-space"></div>
                <div>(.............................................)</div>
            </div>
        </div>
    </div>
</body>
</html>
