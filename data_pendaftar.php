<?php
require 'config.php';

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
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
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
            background: linear-gradient(135deg, #062c21, #064e3b);
            color: #e5e7eb;
            padding: 1rem 0;
            font-size: 0.875rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
        }
    </style>
</head>

<body id="page-top">

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background-color:#005f4f;">
        <div class="container">
            <a class="navbar-brand" href="index">
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
                    <li class="nav-item"><a class="nav-link" href="index#alur">Alur</a></li>
                    <li class="nav-item"><a class="nav-link" href="index#informasi">Info</a></li>
                    <li class="nav-item"><a class="nav-link" href="index#syarat">Syarat</a></li>
                    <li class="nav-item"><a class="nav-link" href="index#fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="index#kontak">Kontak</a></li>
                    <li class="nav-item active"><a class="nav-link" href="data_pendaftar">Data Pendaftar</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('admin/login')); ?>" target="_blank" rel="noopener">Login Admin</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px; margin-bottom: 50px;">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Data Pendaftar</h1>
        </div>

        <div class="row mb-4">
            <div class="col-md-8">
                <form action="" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control bg-light border-0 small" placeholder="Cari Nama Siswa atau Masukkan No Pendaftaran..." aria-label="Search" aria-describedby="basic-addon2" value="<?= isset($_GET['q']) ? esc($_GET['q']) : '' ?>">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i> Cari
                            </button>
                            <?php if(isset($_GET['q']) && $_GET['q'] !== ''): ?>
                                <a href="data_pendaftar" class="btn btn-secondary">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                <small class="text-muted mt-2 d-block">* Masukkan <b>Nomor Pendaftaran</b> secara lengkap dan tepat untuk memunculkan tombol <b>Edit</b>.</small>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pendaftar</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pendaftar</th>
                                <th>Jenis Kelamin</th>
                                <th>NIK</th>
                                <th>KK</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
                            
                            $sql = "SELECT * FROM pendaftar";
                            if (!empty($q)) {
                                $safe_q = $mysqli->real_escape_string($q);
                                $sql .= " WHERE nama_lengkap LIKE '%$safe_q%' OR no_pendaftaran = '$safe_q' OR nik = '$safe_q' OR kk = '$safe_q'";
                            }
                            $sql .= " ORDER BY created_at DESC";

                            if ($result = $mysqli->query($sql)) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = 'secondary';
                                    $statusText = 'Proses';
                                    if ($row['status_daftar'] == 'diterima') {
                                        $statusClass = 'success';
                                        $statusText = 'Diterima';
                                    } elseif ($row['status_daftar'] == 'ditolak') {
                                        $statusClass = 'danger';
                                        $statusText = 'Ditolak';
                                    }
                                    
                                    // Logic tombol edit: hanya muncul jika pencarian persis sama dengan no_pendaftaran
                                    $show_edit = false;
                                    if (!empty($q) && $q === $row['no_pendaftaran']) {
                                        $show_edit = true;
                                    }

                                    echo '<tr>';
                                    echo '<td>' . $no++ . '</td>';
                                    echo '<td>' . esc($row['nama_lengkap']) . '</td>';
                                    echo '<td>' . esc($row['jenis_kelamin']) . '</td>';
                                    echo '<td>' . esc($row['nik']) . '</td>';
                                    echo '<td>' . esc($row['kk']) . '</td>';
                                    echo '<td><span class="badge badge-' . $statusClass . '">' . $statusText . '</span></td>';
                                    echo '<td class="text-center">';
                                    if ($show_edit) {
                                        echo '<a href="edit_pendaftar?id=' . $row['id'] . '" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                              </a>';
                                    } else {
                                        echo '<span class="text-muted" style="font-size:0.8rem;"><i class="fas fa-lock"></i></span>';
                                    }
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                $result->free();
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer text-center">
        <div class="container">
            <span>Sistem Penerimaan Peserta Didik Baru <?= esc($madrasah['nama']); ?> @
                <?= date('Y'); ?></span>
        </div>
    </footer>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
    <!-- Page level plugins -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
                }
            });
        });
    </script>

</body>
</html>
