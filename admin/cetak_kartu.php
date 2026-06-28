<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_once __DIR__ . '/bootstrap.php';
require_login();

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

log_activity('print_card', 'Cetak kartu pendaftar ' . $data['nama_lengkap'] . ' (' . $data['no_pendaftaran'] . ')');

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => '',
    'logo' => '',
    'nama_panitia' => '',
    'nama_kepala' => '',
];

if ($res = $mysqli->query('SELECT nama, alamat, logo, nama_panitia, nama_kepala FROM madrasah LIMIT 1')) {
    if ($row = $res->fetch_assoc()) {
        $madrasah = $row;
    }
    $res->free();
}

$namaKetua = trim((string)($madrasah['nama_panitia'] ?? '')) !== '' ? trim((string)$madrasah['nama_panitia']) : '—';
$namaKepalaMdr = trim((string)($madrasah['nama_kepala'] ?? '')) !== '' ? trim((string)$madrasah['nama_kepala']) : '—';
$tglCetak = date('d-m-Y');
$qrKetuaText = 'Tanda Tangan Digital | Ketua Panitia PPDB | ' . $namaKetua . ' | ' . ($madrasah['nama'] ?? '') . ' | ' . $tglCetak;
$qrKepalaText = 'Tanda Tangan Digital | Kepala Madrasah | ' . $namaKepalaMdr . ' | ' . ($madrasah['nama'] ?? '') . ' | ' . $tglCetak;
$qrKetuaUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . rawurlencode($qrKetuaText);
$qrKepalaUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . rawurlencode($qrKepalaText);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Kartu Pendaftaran <?= esc($data['no_pendaftaran']); ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
        }

        .kartu-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            padding: 24px;
        }

        .kartu-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .kartu-logo {
            width: 72px;
            height: 72px;
            border: none;
            border-radius: 6px;
            object-fit: contain;
            background: #fff;
        }

        .kartu-header-text {
            flex: 1;
            text-align: center;
        }

        .kartu-header h1 {
            font-size: 20px;
            margin: 0;
        }

        .kartu-header h2 {
            font-size: 18px;
            margin: 4px 0 0 0;
        }

        .kartu-header .kartu-tahun-ajaran {
            margin: 8px 0 4px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .kartu-no {
            text-align: center;
            margin: 16px 0;
            font-size: 16px;
            font-weight: bold;
            padding: 8px;
            border: 1px dashed #4b5563;
        }

        .section-title {
            font-weight: bold;
            margin-top: 16px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            table-layout: fixed;
        }

        .section-title + table {
            width: calc(100% - 18px);
            margin-left: 18px;
        }

        td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .label { width: 30%; }
        .sep { width: 2%; text-align: center; white-space: nowrap; }
        .value { width: 50%; }
        .ttd-col { width: 18%; text-align: center; }

        .ttd-space {
            height: 60px;
        }

        .ttd-signature-area {
            margin-top: 80px;
        }

        .ttd-sign-meta-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 20px;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .ttd-place-ketua {
            white-space: nowrap;
            text-align: center;
            margin: 0;
        }

        .ttd-row-print {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .ttd-box-print {
            flex: 1;
            text-align: center;
            font-size: 12px;
            max-width: 48%;
        }

        .ttd-box-print img.ttd-qr {
            width: 100px;
            height: 100px;
            display: block;
            margin: 0 auto;
        }

        .ttd-jabatan {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 8px;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .kartu-wrapper {
                border: none;
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="kartu-wrapper">
        <div class="kartu-header">
            <?php if (!empty($madrasah['logo'])): ?>
                <img class="kartu-logo" src="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>" alt="Logo">
            <?php endif; ?>
            <div class="kartu-header-text">
                <h1>FORMULIR PENDAFTARAN PESERTA DIDIK BARU</h1>
                <h2><?= esc(strtoupper($madrasah['nama'])); ?></h2>
                <p class="kartu-tahun-ajaran">Tahun Ajaran <?= date('Y'); ?>/<?= date('Y') + 1; ?></p>
            </div>
        </div>

        <div class="kartu-no">
            Nomor Pendaftaran: <?= esc($data['no_pendaftaran']); ?>
        </div>

        <div class="section-title">A. Identitas Calon Peserta Didik</div>
        <table>
            <tr><td class="label">Nama Lengkap</td><td class="sep">:</td><td class="value"><?= esc($data['nama_lengkap']); ?></td></tr>
            <tr><td class="label">NIK</td><td class="sep">:</td><td class="value"><?= esc($data['nik']); ?></td></tr>
            <tr><td class="label">No KK</td><td class="sep">:</td><td class="value"><?= esc($data['kk']); ?></td></tr>
            <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td class="value"><?= esc($data['jenis_kelamin']); ?></td></tr>
            <tr><td class="label">Tempat, Tanggal Lahir</td><td class="sep">:</td><td class="value"><?= esc($data['tempat_lahir']); ?>, <?= esc(format_tanggal($data['tanggal_lahir'])); ?></td></tr>
            <tr><td class="label">Alamat</td><td class="sep">:</td><td class="value"><?= esc($data['alamat']); ?></td></tr>
            <tr><td class="label">Status Dalam Keluarga</td><td class="sep">:</td><td class="value"><?= esc($data['status_keluarga']); ?></td></tr>
            <tr><td class="label">Anak Ke / Jumlah Saudara</td><td class="sep">:</td><td class="value"><?= esc((string)$data['anak_ke']); ?> dari <?= esc((string)$data['jumlah_saudara']); ?> bersaudara</td></tr>
            <tr><td class="label">Asal TK/RA</td><td class="sep">:</td><td class="value"><?= esc($data['asal_tk']); ?></td></tr>
        </table>

        <div class="section-title">B. Identitas Orang Tua / Wali</div>
        <table>
            <tr><td class="label">Nama Ayah</td><td class="sep">:</td><td class="value"><?= esc($data['nama_ayah']); ?></td></tr>
            <tr><td class="label">Pekerjaan Ayah</td><td class="sep">:</td><td class="value"><?= esc($data['pekerjaan_ayah']); ?></td></tr>
            <tr><td class="label">Nama Ibu</td><td class="sep">:</td><td class="value"><?= esc($data['nama_ibu']); ?></td></tr>
            <tr><td class="label">Pekerjaan Ibu</td><td class="sep">:</td><td class="value"><?= esc($data['pekerjaan_ibu']); ?></td></tr>
            <tr><td class="label">Nama Wali</td><td class="sep">:</td><td class="value"><?= esc($data['nama_wali']); ?></td></tr>
            <tr><td class="label">Pekerjaan Wali</td><td class="sep">:</td><td class="value"><?= esc($data['pekerjaan_wali']); ?></td></tr>
            <tr><td class="label">Email</td><td class="sep">:</td><td class="value"><?= esc($data['email']); ?></td></tr>
            <tr><td class="label">No HP/WA</td><td class="sep">:</td><td class="value"><?= esc($data['hp']); ?></td></tr>
        </table>

        <div class="section-title">C. Informasi Program Bantuan</div>
        <table>
            <tr><td class="label">Memiliki KIP</td><td class="sep">:</td><td class="value"><?= esc($data['kip']); ?></td></tr>
            <tr><td class="label">Peserta PKH</td><td class="sep">:</td><td class="value"><?= esc($data['pkh']); ?></td></tr>
        </table>
        <div class="ttd-signature-area">
            <div class="ttd-sign-meta-row">
                <div aria-hidden="true"></div>
                <div class="ttd-place-ketua">Jepara, <?= esc($tglCetak); ?></div>
            </div>
            <div class="ttd-row-print">
                <div class="ttd-box-print">
                    <div class="ttd-jabatan">Kepala Madrasah</div>
                    <img class="ttd-qr" src="<?= esc($qrKepalaUrl); ?>" alt="QR Kepala Madrasah">
                    <div class="ttd-nama"><?= esc($namaKepalaMdr); ?></div>
                </div>
                <div class="ttd-box-print">
                    <div class="ttd-jabatan">Ketua Panitia</div>
                    <img class="ttd-qr" src="<?= esc($qrKetuaUrl); ?>" alt="QR Ketua Panitia">
                    <div class="ttd-nama"><?= esc($namaKetua); ?></div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
