<?php
require __DIR__ . '/config.php';

$status_pendaftaran = get_ppdb_status();
$info_pendaftaran = get_option('info_pendaftaran', 'Informasi PPDB belum diatur.');
$syarat_pendaftaran = get_option('syarat_pendaftaran', '');
$fasilitas_siswa = get_option('fasilitas_siswa', '');
$alur_pendaftaran = get_option('alur_pendaftaran', '');
$header_background = get_option('header_background', '');
$default_tahun = date('Y') . '/' . (date('Y') + 1);
$tahun_ajaran = get_option('tahun_ajaran', $default_tahun);

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => 'Alamat madrasah belum diatur',
    'email' => '-',
    'website' => '-',
    'hp_kepala' => '-',
    'hp_panitia' => '-',
];

if ($result = $mysqli->query('SELECT * FROM madrasah LIMIT 1')) {
    if ($row = $result->fetch_assoc()) {
        $madrasah = $row;
    }
    $result->free();
}

$success_message = flash('success');
$error_message = flash('error');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (!empty($madrasah['logo'])): ?>
    <link rel="icon" type="image/png" href="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>">
    <link rel="shortcut icon" href="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>">
    <?php endif; ?>
    <title>PPDB Online <?= esc($madrasah['nama']); ?></title>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .hero {
            position: relative;
            min-height: 80vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            opacity: 0.5;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
        }

        .hero-status {
            margin-top: 1.5rem;
        }

        .section {
            padding: 4rem 0;
        }

        .section-title {
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2rem;
        }

        .section-info {
            background-color: #1f6b6f;
            color: #f9fafb;
        }

        .section-info .section-title {
            color: #f9fafb;
        }

        .section-info .card {
            background-color: transparent;
            border: 0;
        }

        .info-text {
            column-count: 2;
            column-gap: 3rem;
            font-size: 1.1rem;
            line-height: 1.7;
            letter-spacing: 0.02em;
            text-align: justify;
        }

        #alur .section-title {
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.03em;
            color: #000;
        }

        #alur .card-body {
            font-family: 'Poppins', sans-serif;
            font-size: 1.05rem;
            line-height: 1.8;
            color: #000;
        }

        .alur-timeline {
            position: relative;
            padding-left: 1.75rem;
            margin-top: 0.5rem;
        }

        .alur-timeline::before {
            content: "";
            position: absolute;
            top: 0.25rem;
            bottom: 0.25rem;
            left: 0.4rem;
            width: 3px;
            background: linear-gradient(to bottom, #2563eb, #10b981);
            border-radius: 999px;
        }

        .alur-timeline ol,
        .alur-timeline ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .alur-timeline li {
            position: relative;
            margin-bottom: 1.25rem;
            padding-left: 1.75rem;
        }

        .alur-timeline li::before {
            content: "";
            position: absolute;
            left: -0.1rem;
            top: 0.2rem;
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 999px;
            border: 3px solid #ffffff;
            background: linear-gradient(135deg, #2563eb, #10b981);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }

        .alur-timeline li:last-child {
            margin-bottom: 0;
        }

        .alur-timeline strong {
            display: block;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #000;
        }

        #syarat .card-body {
            font-size: 1rem;
            color: #000;
        }

        .syarat-modern ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .syarat-modern li {
            position: relative;
            margin-bottom: 0.85rem;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border-radius: 0.85rem;
            background: #f9fafb;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
        }

        .syarat-modern li::before {
            content: "";
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0.95rem;
            height: 0.95rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #f97316, #facc15);
            box-shadow: 0 0 0 4px rgba(248, 196, 113, 0.4);
        }

        .syarat-modern li span {
            display: block;
        }

        .fasilitas-modern ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .section-fasilitas {
            background: linear-gradient(135deg, #0f766e, #115e59);
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .section-fasilitas::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .section-fasilitas .section-title {
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .fasilitas-modern li {
            position: relative;
            padding: 1.5rem 1.5rem 1.5rem 3.5rem;
            background: #ffffff;
            color: #000; /* Dark text for visibility on white background */
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .fasilitas-modern li:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #3b82f6;
        }

        .fasilitas-modern li::before {
            content: "\f00c"; /* FontAwesome check icon */
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 1rem;
            top: 1.5rem;
            width: 2rem;
            height: 2rem;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }

        @media (max-width: 768px) {
            .info-text {
                column-count: 1;
            }

            .syarat-modern li {
                padding-left: 2.5rem;
            }
            
            .fasilitas-modern ul {
                grid-template-columns: 1fr;
            }
        }

        .footer {
            background: linear-gradient(135deg, #062c21, #064e3b);
            color: #e5e7eb;
            padding: 1rem 0;
            font-size: 0.875rem;
        }

        .navbar-brand span {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1.05rem;
            letter-spacing: 0.08em;
        }

        .navbar-nav .nav-link {
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #ffffff !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .navbar-nav .nav-link:hover {
            color: #fbbf24 !important;
        }

        .navbar-logo {
            height: 32px;
            margin-right: 10px;
            filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.9));
        }

        .section-kontak {
            background: linear-gradient(135deg, #1d4ed8, #0ea5e9);
        }

        .contact-icons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.98rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .contact-item-icon {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 999px;
            background-color: rgba(15, 23, 42, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contact-item-icon i {
            font-size: 1.1rem;
        }

        .contact-item a {
            color: #e5e7eb;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-item a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color:#005f4f;">
        <div class="container">
            <a class="navbar-brand" href="#beranda">
                <?php if (!empty($madrasah['logo'])): ?>
                <img src="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>" alt="Logo" class="navbar-logo">
                <?php endif; ?>
                <span>PPDB ONLINE <?= esc($madrasah['nama']); ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="#alur">Alur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#informasi">Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="#syarat">Syarat</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item"><a class="nav-link" href="data_pendaftar.php">Data Pendaftar</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('admin/login.php')); ?>" target="_blank" rel="noopener">Login
                        Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="beranda" class="hero"
        style="background: url('<?= esc($header_background !== '' ? base_url('uploads/' . $header_background) : 'assets/img/undraw_posting_photo.svg'); ?>') center center/cover no-repeat;">
        <div class="container hero-inner">
            <h1 class="display-4 font-weight-bold mb-2">SELAMAT DATANG</h1>
            <h2 class="h4 mb-3" style="text-transform: uppercase;">PPDB ONLINE <?= esc($madrasah['nama']); ?></h2>
            <p class="lead mb-4">Tahun Ajaran <?= esc($tahun_ajaran); ?></p>
            <div class="hero-status">
                <?php if ($status_pendaftaran === 'buka') : ?>
                <button class="btn btn-lg btn-danger mb-3 px-4">
                    PENDAFTARAN PPDB ONLINE DIBUKA
                </button>
                <?php else : ?>
                <button class="btn btn-lg btn-danger mb-3 px-4">
                    PENDAFTARAN PPDB ONLINE DITUTUP
                </button>
                <?php endif; ?>
            </div>
            <div>
                <button class="btn btn-lg btn-warning shadow-sm" data-toggle="modal" data-target="#modalDaftar"
                    <?php if ($status_pendaftaran !== 'buka') : ?>disabled<?php endif; ?>>
                    Daftar Sekarang
                </button>
            </div>
        </div>
    </header>

    <section id="alur" class="section" style="background-color:#e5e7eb;">
        <div class="container">
            <h3 class="section-title text-center">Alur PPDB Online</h3>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <?php if (trim($alur_pendaftaran) !== '') : ?>
                            <div class="alur-timeline">
                                <?= $alur_pendaftaran; ?>
                            </div>
                            <?php else : ?>
                            <p>Alur pendaftaran belum diatur oleh admin.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="informasi" class="section section-info">
        <div class="container">
            <h3 class="section-title text-center">Informasi PPDB Online</h3>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-body info-text">
                            <?= $info_pendaftaran; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="syarat" class="section">
        <div class="container">
            <h3 class="section-title text-center">Syarat Pendaftaran</h3>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <?php if (trim($syarat_pendaftaran) !== '') : ?>
                            <div class="syarat-modern">
                                <?= $syarat_pendaftaran; ?>
                            </div>
                            <?php else : ?>
                            <p>Syarat pendaftaran belum diatur oleh admin.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="fasilitas" class="section section-fasilitas">
        <div class="container" style="position: relative; z-index: 1;">
            <h3 class="section-title text-center">Fasilitas Siswa Baru</h3>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (trim($fasilitas_siswa) !== '') : ?>
                    <div class="fasilitas-modern">
                        <?= $fasilitas_siswa; ?>
                    </div>
                    <?php else : ?>
                    <div class="text-center text-white-50">
                        <p>Fasilitas siswa baru belum diatur oleh admin.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="kontak" class="section section-kontak text-white">
        <div class="container">
            <h3 class="section-title text-center">KONTAK KAMI</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <p class="mb-1 font-weight-bold"><?= esc($madrasah['nama']); ?></p>
                    <p class="mb-3"><?= esc($madrasah['alamat']); ?></p>
                    <div class="contact-icons">
                        <?php $hp_umum = $madrasah['hp_panitia'] !== '-' && $madrasah['hp_panitia'] !== '' ? $madrasah['hp_panitia'] : $madrasah['hp_kepala']; ?>
                        <?php if (!empty($madrasah['email']) && $madrasah['email'] !== '-'): ?>
                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <a href="mailto:<?= esc($madrasah['email']); ?>"><?= esc($madrasah['email']); ?></a>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($madrasah['website']) && $madrasah['website'] !== '-'): ?>
                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <a href="<?= esc(strpos($madrasah['website'], 'http') === 0 ? $madrasah['website'] : 'http://' . $madrasah['website']); ?>" target="_blank" rel="noopener">
                                <?= esc($madrasah['website']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($hp_umum) && $hp_umum !== '-'): ?>
                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <a href="tel:<?= esc($hp_umum); ?>"><?= esc($hp_umum); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer text-center">
        <div class="container">
            <span>Sistem Penerimaan Peserta Didik Baru <?= esc($madrasah['nama']); ?> @
                <?= date('Y'); ?></span>
        </div>
    </footer>

    <div class="modal fade" id="modalDaftar" tabindex="-1" role="dialog" aria-labelledby="modalDaftarLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="modalDaftarLabel">Formulir Pendaftaran Peserta Didik Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="simpan_pendaftaran.php" method="post">
                    <div class="modal-body">
                        <h6 class="font-weight-bold text-primary mb-3">A. Identitas Calon Peserta Didik</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="nama_lengkap" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>NIK *</label>
                                <input type="text" name="nik" class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label>No Kartu Keluarga *</label>
                                <input type="text" name="kk" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Jenis Kelamin *</label>
                                <select name="jenis_kelamin" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Tempat Lahir *</label>
                                <input type="text" name="tempat_lahir" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Tanggal Lahir *</label>
                                <input type="date" name="tanggal_lahir" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap *</label>
                            <textarea name="alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Status Dalam Keluarga</label>
                                <input type="text" name="status_keluarga" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Anak Ke</label>
                                <input type="number" name="anak_ke" class="form-control" min="1">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Jumlah Saudara</label>
                                <input type="number" name="jumlah_saudara" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Asal TK / RA</label>
                            <input type="text" name="asal_tk" class="form-control">
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">B. Identitas Orang Tua / Wali</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Nama Ayah *</label>
                                <input type="text" name="nama_ayah" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nama Ibu *</label>
                                <input type="text" name="nama_ibu" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Nama Wali</label>
                                <input type="text" name="nama_wali" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Pekerjaan Wali</label>
                                <input type="text" name="pekerjaan_wali" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Email Aktif</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="form-group col-md-4">
                                <label>No HP / WA *</label>
                                <input type="text" name="hp" class="form-control" required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">C. Informasi Program Bantuan</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kip" value="Ya"
                                        id="kipCheck">
                                    <label class="form-check-label" for="kipCheck">
                                        Memiliki KIP
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pkh" value="Ya"
                                        id="pkhCheck">
                                    <label class="form-check-label" for="pkhCheck">
                                        Terdaftar Program PKH
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            var successMessage = <?= json_encode($success_message); ?>;
            var errorMessage = <?= json_encode($error_message); ?>;

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: successMessage,
                    timer: 4000,
                    showConfirmButton: false
                });
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: errorMessage,
                    timer: 4000,
                    showConfirmButton: false
                });
            }
        });
    </script>
</body>

</html>
