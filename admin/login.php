<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_once __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    header('Location: ' . base_url('admin/dashboard'));
    exit;
}

$hasUsers = true;
if ($result = $mysqli->query('SELECT COUNT(*) AS jml FROM users')) {
    $row = $result->fetch_assoc();
    $hasUsers = (int)$row['jml'] > 0;
    $result->free();
}

// Get school information
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

$error_message = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('error', 'Sesi login tidak valid. Silakan coba lagi.');
        header('Location: ' . base_url('admin/login'));
        exit;
    }

    if (!$hasUsers) {
        flash('error', 'Belum ada akun admin. Buat akun admin langsung melalui database atau script setup lokal, bukan dari halaman publik.');
        header('Location: ' . base_url('admin/login'));
        exit;
    }

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    $stmt = $mysqli->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                log_activity('login', 'Login oleh ' . $user['username'], (int)$user['id']);
                flash('success', 'Selamat datang, ' . $user['username'] . '! Anda berhasil login.');
                header('Location: ' . base_url('admin/dashboard'));
                exit;
            }
        }
        $stmt->close();
    }

    flash('error', 'Username atau password salah.');
    header('Location: ' . base_url('admin/login'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - PPDB Online</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-xl-5 col-lg-6 col-md-8">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <?php if (!empty($madrasah['logo'])): ?>
                                        <img src="<?= base_url('uploads/' . $madrasah['logo']); ?>" alt="Logo" class="img-fluid mb-3" style="max-height: 80px;">
                                        <?php endif; ?>
                                        <h1 class="h4 text-gray-900 mb-1">Sistem PPDB</h1>
                                        <h6 class="text-gray-700 mb-4"> <?= esc($madrasah['nama']); ?></h6>
                                    </div>
                                     <form class="user" method="post" action="">
                                        <?= csrf_input(); ?>
                                        <div class="form-group">
                                            <input type="text" name="username" class="form-control form-control-user"
                                                placeholder="Username" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password"
                                                class="form-control form-control-user" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="../assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            var errorMessage = <?= json_encode($error_message); ?>;
            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: errorMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        })();
    </script>
</body>

</html>
