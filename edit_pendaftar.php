<?php
require_once dirname(__FILE__) . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pendaftar = null;

// Handle Update
function validate_name($name) {
    // Only allow letters (including Indonesian accented), spaces, apostrophes, dots, commas, and hyphens
    return preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\'.,-]*$/u', $name) === 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $nama = $_POST['nama_lengkap'] ?? '';
    $nik = $_POST['nik'] ?? '';
    $kk = $_POST['kk'] ?? '';
    $jk = $_POST['jk'] ?? '';
    $hp = $_POST['hp'] ?? '';
    $email = $_POST['email'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    if ($nama === '') {
        $error = 'Mohon lengkapi seluruh isian wajib.';
    } elseif (!validate_name($nama)) {
        $error = 'Nama lengkap hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung.';
    }

    if (!isset($error)) {
        $stmt = $mysqli->prepare("UPDATE pendaftar SET nama_lengkap=?, nik=?, kk=?, jenis_kelamin=?, hp=?, email=?, alamat=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("sssssssi", $nama, $nik, $kk, $jk, $hp, $email, $alamat, $id);
            if ($stmt->execute()) {
                echo "<!DOCTYPE html>
                <html>
                <head>
                    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil diperbarui!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.href='data_pendaftar';
                        });
                    </script>
                </body>
                </html>";
                exit;
            } else {
                $error = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Gagal menyiapkan query.";
        }
    }
}

// Fetch Data
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM pendaftar WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendaftar = $result->fetch_assoc();
        $stmt->close();
    }
}

if (!$pendaftar) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='data_pendaftar';</script>";
    exit;
}

$madrasah = [
    'nama' => 'MI SULTAN FATTAH SUKOSONO',
    'logo' => ''
];
if ($result = $mysqli->query('SELECT nama, logo FROM madrasah LIMIT 1')) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Edit Pendaftar - PPDB Online</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">
    <style>
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
    </style>
</head>
<body class="bg-light">
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
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center font-weight-light my-2">Edit Data Pendaftar</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= esc($error); ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="POST">
                            <input type="hidden" name="id" value="<?= esc($pendaftar['id']); ?>">
                            
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" 
                                       pattern="^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s'.,-]*$" 
                                       title="Nama hanya boleh mengandung huruf, spasi, apostrof, titik, koma, dan tanda hubung" 
                                       value="<?= esc($pendaftar['nama_lengkap']); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                <label>NIK</label>
                                <input type="text" name="nik" class="form-control" inputmode="numeric" pattern="[0-9]*" value="<?= esc($pendaftar['nik']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>KK</label>
                                <input type="text" name="kk" class="form-control" inputmode="numeric" pattern="[0-9]*" value="<?= esc($pendaftar['kk']); ?>" required>
                            </div>
                        </div>
                            
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jk" class="form-control" required>
                                    <option value="Laki-laki" <?= $pendaftar['jenis_kelamin'] == 'Laki-laki' || $pendaftar['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?= $pendaftar['jenis_kelamin'] == 'Perempuan' || $pendaftar['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>No. HP / WhatsApp</label>
                                    <input type="text" name="hp" class="form-control" inputmode="numeric" pattern="[0-9]*" value="<?= esc($pendaftar['hp']); ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= esc($pendaftar['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3"><?= esc($pendaftar['alamat']); ?></textarea>
                            </div>
                            
                            <div class="form-group d-flex justify-content-between">
                                <a href="<?= esc(base_url('data_pendaftar')); ?>" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer text-center">
        <div class="container">
            <span>Sistem Penerimaan Peserta Didik Baru <?= esc($madrasah['nama']); ?> @
                <?= date('Y'); ?></span>
            <span style="color: rgba(255,255,255,0.4); font-size: 0.75rem; margin-left: 0.5rem;">v<?= app_version(); ?></span>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
    <script>
        $(function () {
            // Prevent non-numeric input for NIK, KK, HP
            $('input[name="nik"], input[name="kk"], input[name="hp"]').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            }).on('paste', function(e) {
                e.preventDefault();
                var text = (e.originalEvent || e).clipboardData.getData('text/plain');
                this.value = text.replace(/[^0-9]/g, '');
            });

            // Prevent special characters and icons in name fields
            $('input[name="nama_lengkap"]').on('input', function() {
                this.value = this.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s'.,-]/g, '');
            });
        });
    </script>
</body>
</html>
