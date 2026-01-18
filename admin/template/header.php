<?php
require_once __DIR__ . '/../../config.php';
require_login();

$madrasah_nama = 'MI SULTAN FATTAH SUKOSONO';
$madrasah_logo = '';

if ($result = $mysqli->query('SELECT nama, logo FROM madrasah LIMIT 1')) {
    if ($row = $result->fetch_assoc()) {
        $madrasah_nama = $row['nama'];
        $madrasah_logo = isset($row['logo']) ? (string)$row['logo'] : '';
    }
    $result->free();
}

$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php
        $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        $titles = [
            'dashboard' => 'Dashboard',
            'madrasah' => 'Data Madrasah',
            'pendaftar' => 'Data Pendaftar',
            'pengaturan' => 'Pengaturan PPDB',
            'pengguna' => 'Pengguna',
            'backup' => 'Backup & Restore',
        ];
        $page_title = isset($titles[$current_page]) ? $titles[$current_page] : 'Dashboard';
    ?>
    <title><?= esc($page_title); ?> - <?= esc($madrasah_nama); ?></title>
    <link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <style>
        .sidebar-brand-text {
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 0.9rem;
            line-height: 1.2;
            text-align: left;
            margin-left: 0 !important;
        }

        .sidebar-app-name {
            font-size: 0.95rem;
            letter-spacing: 0.12em;
        }

        .sidebar-school-name {
            font-size: 0.75rem;
            letter-spacing: 0.06em;
        }

        .sidebar .nav-item .nav-link span {
            font-size: 1.05rem !important;
            font-weight: 600 !important;
        }

        .topbar-fixed .nav-link {
            font-size: 1.05rem !important;
            font-weight: 600 !important;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 14rem;
            overflow-y: auto;
        }

        #content-wrapper {
            margin-left: 14rem;
            min-height: 100vh;
        }

        .topbar-fixed {
            position: fixed;
            top: 0;
            left: 14rem;
            right: 0;
            z-index: 1030;
        }

        #content {
            padding-top: 5.25rem;
        }

        .topbar-user-name {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #374151 !important;
        }

        .img-profile {
            border-radius: 0 !important;
        }

        @media (max-width: 768px) {
            .topbar-fixed {
                left: 0;
            }
            #content-wrapper {
                margin-left: 0;
            }
            .sidebar {
                left: -14rem;
            }
        }

        .timeline {
            position: relative;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .timeline::before {
            content: "";
            position: absolute;
            top: 0.25rem;
            bottom: 0.25rem;
            left: 31px;
            width: 2px;
            background: linear-gradient(to bottom, #4e73df, #1cc88a);
        }

        .time-label {
            position: relative;
            margin: 0 0 15px 0;
        }

        .time-label span {
            border-radius: 4px;
            background-color: #4e73df;
            color: #fff;
            display: inline-block;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .timeline > div {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline > div > i {
            position: absolute;
            left: 15px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            text-align: center;
            line-height: 32px;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(78, 115, 223, 0.25);
        }

        .timeline-item {
            margin-left: 60px;
            margin-right: 15px;
            margin-top: 0;
            background-color: #f8f9fc;
            border-radius: 0.35rem;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.05);
            padding: 10px 12px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-header {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .timeline-header a {
            color: #4e73df;
        }

        .timeline-body {
            font-size: 0.85rem;
            margin-top: 4px;
            color: #4b5563;
        }

        .timeline-footer {
            margin-top: 6px;
        }

        .timeline .time {
            float: right;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .activity-scroll {
            max-height: 320px;
            overflow-y: auto;
        }

        footer.sticky-footer {
            padding: 1rem 0 !important;
            min-height: 64px !important;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <nav class="navbar navbar-expand navbar-light bg-white topbar topbar-fixed shadow">

                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="d-none d-sm-flex align-items-center mr-auto">
                        <span id="adminClock" class="text-gray-700 small font-weight-bold"></span>
                    </div>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span
                                    class="mr-2 d-none d-lg-inline topbar-user-name"><?= esc($user['username'] ?? 'Admin'); ?></span>
                                <img class="img-profile"
                                    src="<?= esc(isset($user['foto']) && $user['foto'] !== '' ? base_url('uploads/' . $user['foto']) : 'https://via.placeholder.com/60'); ?>">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#" id="btnLogout">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
