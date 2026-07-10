<?php
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/admin/bootstrap.php';

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'alamat' => 'Alamat madrasah belum diatur',
    'email' => '-',
    'website' => '-',
    'hp_kepala' => '-',
    'hp_panitia' => '-',
    'logo' => ''
];

if ($result = $mysqli->query('SELECT * FROM madrasah LIMIT 1')) {
    if ($row = $result->fetch_assoc()) {
        $madrasah = $row;
    }
    $result->free();
}

$hp_umum = trim((string)($madrasah['hp_panitia'] ?? ''));
if ($hp_umum === '' || $hp_umum === '-') {
    $hp_umum = trim((string)($madrasah['hp_kepala'] ?? ''));
}
$wa_number = preg_replace('/\D+/', '', $hp_umum) ?? '';
if ($wa_number !== '' && strpos($wa_number, '0') === 0) {
    $wa_number = '62' . substr($wa_number, 1);
}
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
    <title>Data Pendaftar - PPDB Online <?= esc($madrasah['nama']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .footer {
            margin-top: auto;
            background: #064e3b;
            color: rgba(255, 255, 255, 0.92);
            padding: 0.85rem 1rem;
            font-size: 0.875rem;
        }
        .footer .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
            line-height: 1.35;
        }
        .footer .app-version {
            color: rgba(255,255,255,0.52);
            font-size: 0.75rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
        }
        .data-pendaftar-shell {
            width: 100%;
            max-width: none;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .data-pendaftar-shell .card {
            width: 100% !important;
        }

        /* Tetap satu tabel utuh -> header & body selalu lurus */
        .data-pendaftar-table-wrap {
            max-height: none;
            overflow-x: auto;
            overflow-y: visible;
        }

        #dataTable {
            font-size: 0.78rem;
            table-layout: auto;
            width: max-content;
            min-width: 100%;
            margin-bottom: 0;
        }

        #dataTable thead th {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
        }

        #dataTable tbody td {
            vertical-align: middle;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
            line-height: 1.25;
            white-space: nowrap;
        }

        #dataTable th:nth-child(7),
        #dataTable td:nth-child(7) {
            max-width: 260px;
            min-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #dataTable td:last-child {
            min-width: 76px;
            white-space: nowrap;
        }

        #dataTable_wrapper .dataTables_length,
        #dataTable_wrapper .dataTables_filter {
            margin-bottom: 0.5rem;
        }

        #dataTable_wrapper .dataTables_length label,
        #dataTable_wrapper .dataTables_filter label {
            margin-bottom: 0;
        }

        #dataTable_wrapper .dataTables_filter {
            text-align: right;
        }

        @media (max-width: 767.98px) {
            #dataTable_wrapper .dataTables_length,
            #dataTable_wrapper .dataTables_filter {
                text-align: left;
            }
            .footer {
                font-size: 0.82rem;
                padding: 0.8rem 0.75rem;
            }
        }
        @media (min-width: 992px) {
            .data-pendaftar-shell {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
    </style>
</head>

<body id="page-top">

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color:#005f4f;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= esc(base_url()); ?>">
                <?php if (!empty($madrasah['logo'])): ?>
                <img src="<?= esc(base_url('uploads/' . $madrasah['logo'])); ?>" alt="Logo" class="navbar-logo mr-2">
                <?php endif; ?>
                <div class="d-flex flex-column">
                    <span class="font-weight-bold" style="line-height: 1.1; font-size: 1.1rem;">PPDB ONLINE</span>
                    <span style="line-height: 1.1; font-size: 0.8rem;"><?= esc($madrasah['nama']); ?></span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('#alur')); ?>">Alur</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('#informasi')); ?>">Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('#syarat')); ?>">Syarat</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('#fasilitas')); ?>">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('#kontak')); ?>">Kontak</a></li>
                    <li class="nav-item active"><a class="nav-link" href="<?= esc(base_url('data_pendaftar')); ?>">Data Pendaftar</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('admin/login')); ?>" target="_blank" rel="noopener" title="Login Admin"><i class="fas fa-cog"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid data-pendaftar-shell" style="margin-top: 24px; margin-bottom: 30px;">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Data Pendaftar</h1>
        </div>

        <div class="row mb-4 align-items-stretch">
            <div class="col-lg-7 mb-3 mb-lg-0">
                <form action="" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control form-control-lg bg-white border-0 small" placeholder="Cari No Pendaftaran atau Nama..." aria-label="Search" aria-describedby="basic-addon2" value="<?= isset($_GET['q']) ? esc($_GET['q']) : '' ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i> Cari
                            </button>
                            <?php if(isset($_GET['q']) && $_GET['q'] !== ''): ?>
                                <a href="<?= esc(base_url('data_pendaftar')); ?>" class="btn btn-secondary">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                <small class="text-muted mt-2 d-block">* Data publik hanya menampilkan informasi utama untuk menjaga privasi pendaftar.</small>
            </div>
            <div class="col-lg-5">
                <div class="alert alert-warning mb-0 h-100">
                    <div class="font-weight-bold mb-1">Perlu koreksi data?</div>
                    <div>Untuk keamanan, perubahan data hanya dilakukan oleh admin/panitia. Hubungi panitia dengan menyebutkan nomor pendaftaran dan data yang perlu diperbaiki.</div>
                    <?php if ($wa_number !== ''): ?>
                        <a class="btn btn-sm btn-success mt-2" target="_blank" rel="noopener" href="https://wa.me/<?= esc($wa_number); ?>?text=<?= rawurlencode('Assalamu alaikum, saya ingin mengajukan koreksi data PPDB. Nomor pendaftaran: '); ?>">
                            <i class="fab fa-whatsapp"></i> Hubungi Panitia
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pendaftar</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive data-pendaftar-table-wrap">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Pendaftaran</th>
                                <th>Nama Pendaftar</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
                            $rows = [];

                            if ($q !== '') {
                                $like = '%' . $q . '%';
                                $stmt = $mysqli->prepare('SELECT no_pendaftaran, nama_lengkap, jenis_kelamin, tanggal_lahir, status_daftar, created_at FROM pendaftar WHERE no_pendaftaran LIKE ? OR nama_lengkap LIKE ? ORDER BY created_at DESC');
                                if ($stmt) {
                                    $stmt->bind_param('ss', $like, $like);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $rows[] = $row;
                                    }
                                    $stmt->close();
                                }
                            } else {
                                $stmt = $mysqli->prepare('SELECT no_pendaftaran, nama_lengkap, jenis_kelamin, tanggal_lahir, status_daftar, created_at FROM pendaftar ORDER BY created_at DESC');
                                if ($stmt) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $rows[] = $row;
                                    }
                                    $stmt->close();
                                }
                            }

                            foreach ($rows as $row) {
                                    $statusClass = 'secondary';
                                    $statusText = 'Proses';
                                    if ($row['status_daftar'] == 'diterima') {
                                        $statusClass = 'success';
                                        $statusText = 'Diterima';
                                    } elseif ($row['status_daftar'] == 'ditolak') {
                                        $statusClass = 'danger';
                                        $statusText = 'Ditolak';
                                    }
                                    
                                    echo '<tr>';
                                    echo '<td>' . $no++ . '</td>';
                                    echo '<td>' . esc($row['no_pendaftaran']) . '</td>';
                                    echo '<td>' . esc($row['nama_lengkap']) . '</td>';
                                    echo '<td>' . esc($row['jenis_kelamin']) . '</td>';
                                    echo '<td>' . esc(format_tanggal($row['tanggal_lahir'])) . '</td>';
                                    echo '<td><span class="badge badge-' . $statusClass . '">' . $statusText . '</span></td>';
                                    echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer text-center">
        <div class="container footer-content">
            <span>Sistem Penerimaan Peserta Didik Baru <?= esc($madrasah['nama']); ?> @ <?= date('Y'); ?></span>
            <span class="app-version">v<?= esc(app_version()); ?></span>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
    <!-- Page level plugins -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            var dataPendaftarTable = $('#dataTable').DataTable({
                autoWidth: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
                order: [],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json",
                    emptyTable: 'Belum ada data pendaftar.'
                }
            });

            dataPendaftarTable.columns.adjust();
            $(window).on('resize', function () {
                dataPendaftarTable.columns.adjust();
            });
        });
    </script>

</body>
</html>
