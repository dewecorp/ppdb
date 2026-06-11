<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_login();

$pendaftar = [];
$stmt = $mysqli->prepare('SELECT * FROM pendaftar ORDER BY no_pendaftaran ASC');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendaftar[] = $row;
    }
    $stmt->close();
}

if (empty($pendaftar)) {
    $urlPendaftar = esc(base_url('admin/index.php?page=pendaftar'));
    $urlDasbor = esc(base_url('admin/index.php'));
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cetak Semua Kartu — Belum Ada Data</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,600,700" rel="stylesheet">
    <style>
        :root { --primary: #4e73df; --primary-dark: #224abe; --text: #5a5c69; --muted: #858796; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Nunito, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(165deg, #f8f9fc 0%, #e3e6f0 45%, #dde1e9 100%);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .shell { width: 100%; max-width: 520px; }
        .card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 2rem rgba(78, 115, 223, 0.12), 0 0.15rem 0.5rem rgba(0,0,0,.08);
            overflow: hidden;
        }
        .card-top {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .card-top .icon-circle {
            width: 4.25rem;
            height: 4.25rem;
            margin: 0 auto 1rem;
            background: rgba(255,255,255,.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .card-top h1 { margin: 0; font-size: 1.35rem; font-weight: 700; letter-spacing: .02em; }
        .card-top p { margin: 0.5rem 0 0; font-size: 0.92rem; opacity: .95; line-height: 1.45; }
        .card-body { padding: 1.5rem 1.5rem 1.35rem; }
        .card-body .lead {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--text);
            margin: 0 0 1rem;
        }
        .tips {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            padding: 1rem 1rem 1rem 2.65rem;
            margin: 0 0 1.25rem;
            font-size: 0.875rem;
            color: var(--muted);
            position: relative;
        }
        .tips i.fa-lightbulb {
            position: absolute;
            left: 0.9rem;
            top: 1rem;
            color: #f6c23e;
        }
        .tips ul { margin: 0.35rem 0 0; padding-left: 1.1rem; }
        .tips li { margin-bottom: 0.35rem; }
        .btn-row { display: flex; flex-wrap: wrap; gap: 0.65rem; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.55rem 1.1rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.35rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: transform .08s ease, box-shadow .15s ease;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(78,115,223,.35);
        }
        .btn-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-outline {
            background: #fff;
            color: var(--text);
            border: 1px solid #d1d3e2;
        }
        .btn-outline:hover { background: #f8f9fc; color: var(--text); }
        .footnote { text-align: center; font-size: 0.78rem; color: #b7b9cc; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <div class="card-top">
                <div class="icon-circle"><i class="fas fa-print" aria-hidden="true"></i></div>
                <h1>Belum ada data untuk dicetak</h1>
                <p>Kartu formulir pendaftaran membutuhkan minimal satu pendaftar di database.</p>
            </div>
            <div class="card-body">
                <p class="lead">
                    Saat ini tabel pendaftar kosong, sehingga <strong>Cetak Semua Kartu</strong> tidak bisa dijalankan.
                    Setelah ada pendaftar, Anda dapat membuka kembali halaman ini untuk mencetak sekaligus.
                </p>
                <div class="tips">
                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                    <strong style="color: var(--text);">Yang bisa Anda lakukan:</strong>
                    <ul>
                        <li>Buka <strong>Data Pendaftar</strong> untuk melihat daftar atau memantau pendaftaran baru.</li>
                        <li>Pastikan calon peserta sudah menyelesai proses daftar di laman publik PPDB (jika sudah dibuka).</li>
                    </ul>
                </div>
                <div class="btn-row">
                    <a class="btn btn-primary" href="<?= $urlPendaftar; ?>"><i class="fas fa-users" aria-hidden="true"></i> Ke Data Pendaftar</a>
                    <a class="btn btn-outline" href="<?= $urlDasbor; ?>"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard</a>
                </div>
            </div>
        </div>
        <p class="footnote">PPDB Online — Cetak Semua Kartu</p>
    </div>
</body>
</html>
    <?php
    exit;
}

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
    <title>Cetak Semua Kartu Pendaftaran</title>
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
            margin-bottom: 20px;
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

        .kartu-header-text h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .kartu-header-text h2 {
            margin: 4px 0;
            font-size: 16px;
            font-weight: bold;
            color: #374151;
        }

        .kartu-header-text .kartu-tahun-ajaran {
            margin: 8px 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .kartu-no {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            background-color: #e5e7eb;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            margin-top: 16px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 4px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        td {
            padding: 4px 0;
            vertical-align: top;
        }

        td.label {
            width: 35%;
            color: #374151;
        }

        td.sep {
            width: 2%;
            text-align: center;
        }

        td.value {
            width: 63%;
            font-weight: 500;
            color: #111827;
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

        .ttd-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .ttd-col {
            flex: 1;
            text-align: center;
            font-size: 12px;
            max-width: 48%;
        }

        .ttd-col img.ttd-qr {
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
            white-space: nowrap;
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
                page-break-after: always;
            }
            
            .kartu-wrapper:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <?php foreach ($pendaftar as $data): ?>
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
            <tr><td class="label">Tempat, Tanggal Lahir</td><td class="sep">:</td><td class="value"><?= esc($data['tempat_lahir']); ?>, <?= esc($data['tanggal_lahir']); ?></td></tr>
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
            <div class="ttd-wrapper">
                <div class="ttd-col">
                    <div class="ttd-jabatan">Kepala Madrasah</div>
                    <img class="ttd-qr" src="<?= esc($qrKepalaUrl); ?>" alt="QR Kepala Madrasah">
                    <div class="ttd-nama"><?= esc($namaKepalaMdr); ?></div>
                </div>
                <div class="ttd-col">
                    <div class="ttd-jabatan">Ketua Panitia</div>
                    <img class="ttd-qr" src="<?= esc($qrKetuaUrl); ?>" alt="QR Ketua Panitia">
                    <div class="ttd-nama"><?= esc($namaKetua); ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</body>

</html>
