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
];

if ($res = $mysqli->query('SELECT nama, alamat FROM madrasah LIMIT 1')) {
    if ($row = $res->fetch_assoc()) {
        $madrasah = $row;
    }
    $res->free();
}

$total = count_pendaftar();
$totalDiterima = count_pendaftar('diterima');
$totalDitolak = count_pendaftar('ditolak');
$totalProses = count_pendaftar('proses');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Rekap Pendaftar</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        @page {
            size: 330mm 215mm;
            margin: 12mm;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header h2 {
            margin: 2px 0 0 0;
            font-size: 16px;
        }

        .header p {
            margin: 2px 0;
        }

        .info {
            margin-bottom: 10px;
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

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="header">
        <h1>REKAP PENDAFTAR PESERTA DIDIK BARU</h1>
        <h2><?= esc($madrasah['nama']); ?></h2>
        <p><?= esc($madrasah['alamat']); ?></p>
        <p>Tahun Pelajaran <?= date('Y'); ?>/<?= date('Y') + 1; ?></p>
    </div>

    <div class="info">
        <span>Total Pendaftar: <?= $total; ?></span>
        <span>Diterima: <?= $totalDiterima; ?></span>
        <span>Ditolak: <?= $totalDitolak; ?></span>
        <span>Proses: <?= $totalProses; ?></span>
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
</body>

</html>
