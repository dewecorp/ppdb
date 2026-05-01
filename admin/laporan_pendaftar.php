<?php
require __DIR__ . '/../config.php';
require_login();

$status = isset($_GET['status']) ? $_GET['status'] : '';
$allowedStatus = ['proses', 'diterima', 'ditolak'];
if (!in_array($status, $allowedStatus, true)) {
    $status = '';
}

$whereSql = '';
if ($status !== '') {
    $whereSql = 'WHERE status_daftar = ?';
}

$sql = 'SELECT * FROM pendaftar ' . $whereSql . ' ORDER BY created_at ASC';

if ($whereSql === '') {
    $stmt = $mysqli->prepare($sql);
} else {
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('s', $status);
    }
}

$pendaftar = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendaftar[] = $row;
    }
    $stmt->close();
}

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => '',
    'logo' => '',
    'hp_kepala' => '',
    'hp_panitia' => '',
    'nama_panitia' => '',
];

if ($res = $mysqli->query('SELECT nama, alamat, logo, hp_kepala, hp_panitia, nama_panitia FROM madrasah LIMIT 1')) {
    if ($row = $res->fetch_assoc()) {
        $madrasah = $row;
    }
    $res->free();
}

$tanggalCetak = date('d-m-Y');
$tahunAjaran = get_option('tahun_ajaran', date('Y') . '/' . (date('Y') + 1));
$namaPanitia = trim((string)$madrasah['nama_panitia']) !== '' ? (string)$madrasah['nama_panitia'] : 'Panitia PPDB';
$lokasiCetak = 'Jepara';
$qrPanitiaText = 'Validasi TTD Panitia PPDB | ' . (string)$madrasah['nama'] . ' | ' . $tanggalCetak . ' | Panitia: ' . $namaPanitia . ' | HP: ' . (string)$madrasah['hp_panitia'];
$qrPanitiaUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=95x95&data=' . rawurlencode($qrPanitiaText);

$total = count_pendaftar();
$totalDiterima = count_pendaftar('diterima');
$totalDitolak = count_pendaftar('ditolak');
$totalProses = count_pendaftar('proses');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Rekap Pendaftar - TA <?= esc($tahunAjaran); ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 18px;
            font-size: 12px;
        }
        @page {
            size: 330mm 215mm;
            margin: 12mm;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header-logo {
            width: 70px;
            text-align: center;
            margin-right: 12px;
        }

        .header-logo img {
            max-width: 64px;
            max-height: 64px;
            object-fit: contain;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
        }

        .header h2 {
            margin: 2px 0 0 0;
            font-size: 17px;
        }

        .header p {
            margin: 2px 0;
        }

        .info {
            margin-bottom: 10px;
            font-size: 11px;
        }

        .info span {
            margin-right: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 3px 4px;
            text-align: left;
        }

        th {
            font-size: 11px;
        }

        td {
            font-size: 11px;
        }

        .action-print {
            text-align: right;
            margin-bottom: 8px;
        }

        .action-print button {
            border: 1px solid #000;
            background: #fff;
            padding: 5px 10px;
            cursor: pointer;
        }

        .ttd-wrapper {
            margin-top: 56px;
            display: flex;
            justify-content: flex-end;
        }

        .ttd-col {
            width: 31%;
            text-align: center;
            font-size: 11px;
        }

        .ttd-date {
            margin-bottom: 14px;
        }

        .ttd-role {
            margin-bottom: 8px;
        }

        .ttd-qr {
            margin: 6px 0;
            min-height: 95px;
        }

        .ttd-qr img {
            width: 95px;
            height: 95px;
            object-fit: contain;
            border: 1px solid #000;
            padding: 2px;
        }

        .ttd-name {
            margin-top: 4px;
            font-weight: bold;
            text-decoration: underline;
        }

        @media print {
            body {
                padding: 0;
            }

            .action-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="action-print">
        <button type="button" onclick="window.print()">Print / Simpan PDF</button>
    </div>
    <div class="header">
        <div class="header-logo">
            <?php if (!empty($madrasah['logo'])): ?>
                <img src="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>" alt="Logo Madrasah">
            <?php endif; ?>
        </div>
        <div class="header-text">
            <h1>REKAP PENDAFTAR PESERTA DIDIK BARU</h1>
            <h2><?= esc($madrasah['nama']); ?></h2>
            <p><?= esc($madrasah['alamat']); ?></p>
            <p>Tahun Pelajaran <?= esc($tahunAjaran); ?></p>
        </div>
    </div>

    <div class="info">
        <span>Total Pendaftar: <?= $total; ?></span>
        <span>Diterima: <?= $totalDiterima; ?></span>
        <span>Ditolak: <?= $totalDitolak; ?></span>
        <span>Proses: <?= $totalProses; ?></span>
        <span>Tanggal Cetak: <?= esc($tanggalCetak); ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Pendaftaran</th>
                <th>Nama Pendaftar</th>
                <th>Jenis Kelamin</th>
                <th>NIK</th>
                <th>KK</th>
                <th>Alamat</th>
                <th>KIP</th>
                <th>PKH</th>
                <th>Status Daftar</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($pendaftar as $row): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= esc($row['no_pendaftaran']); ?></td>
                <td><?= esc($row['nama_lengkap']); ?></td>
                <td><?= esc($row['jenis_kelamin']); ?></td>
                <td><?= esc($row['nik']); ?></td>
                <td><?= esc($row['kk']); ?></td>
                <td><?= esc($row['alamat']); ?></td>
                <td><?= esc($row['kip']); ?></td>
                <td><?= esc($row['pkh']); ?></td>
                <td><?= esc($row['status_daftar']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="ttd-wrapper">
        <div class="ttd-col">
            <div class="ttd-date"><?= esc($lokasiCetak); ?>, <?= esc($tanggalCetak); ?></div>
            <div class="ttd-role">Panitia PPDB</div>
            <div class="ttd-qr">
                <img src="<?= esc($qrPanitiaUrl); ?>" alt="QR Tanda Tangan Panitia PPDB">
            </div>
            <div class="ttd-name"><?= esc($namaPanitia); ?></div>
        </div>
    </div>
    <script>
        (function () {
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 300);
            });
        })();
    </script>
</body>

</html>
