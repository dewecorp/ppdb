<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../bootstrap.php';
$updateHelperPath = __DIR__ . '/../update_helpers.php';
if (is_file($updateHelperPath)) {
    require_once $updateHelperPath;
}
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
$is_admin_user = is_admin();
$updater_available = $is_admin_user && function_exists('updater_is_open');
$updater_is_open = $updater_available && updater_is_open();

// Cleanup old notifications
$mysqli->query("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");

// Fetch notifications (last 24 hours)
$notifications = [];
$unread_count = 0;
$notif_sql = "SELECT * FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY created_at DESC";
if ($notif_res = $mysqli->query($notif_sql)) {
    while ($notif_row = $notif_res->fetch_assoc()) {
        $notifications[] = $notif_row;
        if ($notif_row['is_read'] == 0) {
            $unread_count++;
        }
    }
    $notif_res->free();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php if (!empty($madrasah_logo)): ?>
    <link rel="icon" type="image/png" href="<?= esc(base_url('uploads/' . $madrasah_logo)); ?>">
    <link rel="shortcut icon" href="<?= esc(base_url('uploads/' . $madrasah_logo)); ?>">
    <?php endif; ?>
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
    <title><?= esc($page_title); ?> | PPDB Online</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
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
            font-size: 0.9rem !important;
            font-weight: 600 !important;
        }

        .sidebar.bg-gradient-primary {
            background-color: #047857 !important;
            background-image: linear-gradient(180deg, #047857 10%, #065f46 100%) !important;
        }

        .sidebar .sidebar-brand {
            background: rgba(6, 78, 59, 0.28);
        }

        .sidebar .sidebar-heading,
        .sidebar .nav-item .nav-link,
        .sidebar .nav-item .nav-link i,
        .sidebar .nav-item .nav-link span {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        .sidebar .nav-item.active .nav-link,
        .sidebar .nav-item .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar .sidebar-divider {
            border-top-color: rgba(255, 255, 255, 0.18) !important;
        }

        .topbar-fixed .nav-link {
            font-size: 1.05rem !important;
            font-weight: 600 !important;
        }

        .topbar-fixed {
            background-color: #047857 !important;
            background-image: linear-gradient(90deg, #065f46, #047857) !important;
        }

        .topbar-fixed .nav-link,
        .topbar-fixed .nav-link i,
        .topbar-fixed #adminClock,
        .topbar-user-name {
            color: #ffffff !important;
        }

        .topbar-fixed .topbar-divider {
            border-right-color: rgba(255, 255, 255, 0.25) !important;
        }

        #sidebarToggleTop {
            color: #ffffff !important;
        }

        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 14rem;
            overflow-y: auto;
            z-index: 1040;
        }

        .sidebar.toggled {
            width: 6.5rem !important;
            overflow-x: hidden;
        }

        #content-wrapper {
            margin-left: 14rem;
            width: calc(100% - 14rem);
            min-height: 100vh;
            min-width: 0;
            transition: margin-left 0.2s ease, width 0.2s ease;
        }

        .topbar-fixed {
            position: fixed;
            top: 0;
            left: 14rem;
            right: 0;
            z-index: 1030;
            transition: left 0.2s ease;
        }

        body.sidebar-toggled #content-wrapper {
            margin-left: 6.5rem;
            width: calc(100% - 6.5rem);
        }

        body.sidebar-toggled .topbar-fixed {
            left: 6.5rem;
        }

        body.sidebar-toggled .sidebar .nav-item .nav-link {
            width: 6.5rem;
            padding-left: 0.45rem;
            padding-right: 0.45rem;
            text-align: center;
        }

        body.sidebar-toggled .sidebar .nav-item .nav-link i {
            font-size: 0.92rem;
        }

        body.sidebar-toggled .sidebar .nav-item .nav-link span,
        body.sidebar-toggled .sidebar .sidebar-heading {
            font-size: 0.64rem !important;
            line-height: 1.15;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        body.sidebar-toggled .sidebar #sidebarToggle {
            width: 2.25rem;
            height: 2.25rem;
        }

        #content {
            padding-top: 5.25rem;
        }

        .topbar-user-name {
            font-size: 1.15rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
        }

        .img-profile {
            border-radius: 0 !important;
        }

        .avatar-initial {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            color: #ffffff;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .avatar-initial-sm {
            width: 40px;
            height: 40px;
            font-size: 0.85rem;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 1035;
            display: none;
        }

        .wysihtml5-wrapper {
            width: 100%;
        }

        .wysihtml5-editor {
            min-height: 220px;
            max-height: 420px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .wysihtml5-toolbar .btn {
            padding: 0.25rem 0.5rem;
        }

        @media (max-width: 768px) {
            .topbar-fixed {
                left: 0;
            }
            #content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .sidebar {
                left: 0;
                width: 14rem !important;
                transition: left 0.3s ease;
            }
            body.sidebar-toggled #content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            body.sidebar-toggled .topbar-fixed {
                left: 0;
            }
            body.sidebar-toggled .sidebar,
            .sidebar.toggled {
                left: -14rem;
            }
            .sidebar-overlay {
                display: none;
            }
            body.sidebar-open .sidebar-overlay {
                display: block;
            }
        }

        /* Notification Customization - Direct to override cache */
        .dropdown-menu-right {
            right: 0 !important;
            left: auto !important;
        }

        .dropdown-list {
            width: 340px !important;
            max-width: 90vw !important;
            box-sizing: border-box !important;
        }

        .notif-scroll {
            box-sizing: border-box !important;
        }

        .dropdown-list .dropdown-item {
            white-space: normal !important;
            padding: 0.5rem 1rem !important;
            box-sizing: border-box !important;
        }

        .notif-text-wrapper {
            min-width: 0 !important;
            flex: 1 !important;
            box-sizing: border-box !important;
        }

        .notif-text {
            display: block !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            white-space: normal !important;
            line-height: 1.5 !important;
            box-sizing: border-box !important;
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

        .swal2-styled,
        .swal2-styled:focus,
        .swal2-styled:active {
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <?php include __DIR__ . '/sidebar.php'; ?>
        <div class="sidebar-overlay d-md-none"></div>

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
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <!-- Counter - Alerts -->
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge badge-danger badge-counter"><?= $unread_count > 9 ? '9+' : $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                    Notifikasi Pendaftaran
                                    <button id="markAllReadBtn" class="btn btn-sm btn-primary ml-auto" style="font-size: 0.75rem;">Tandai semua dibaca</button>
                                </h6>
                                <div class="notif-scroll" style="max-height: 300px; overflow-y: auto;">
                                <?php if (empty($notifications)): ?>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-secondary">
                                                <i class="fas fa-info text-white"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold">Tidak ada notifikasi baru</span>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <?php foreach ($notifications as $notif): 
                                        $data = json_decode($notif['content'], true);
                                        $is_unread = $notif['is_read'] == 0;
                                        $font_weight = $is_unread ? 'font-weight-bold' : '';
                                        $nama = isset($data['nama']) ? $data['nama'] : $notif['content'];
                                        $no_reg = isset($data['no_pendaftaran']) ? $data['no_pendaftaran'] : '-';
                                        $waktu = isset($data['waktu']) ? $data['waktu'] : $notif['created_at'];
                                    ?>
                                    <a class="dropdown-item d-flex align-items-center notif-item" href="<?= base_url('admin/pendaftar'); ?>" data-id="<?= $notif['id']; ?>">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-primary">
                                                <i class="fas fa-file-alt text-white"></i>
                                            </div>
                                        </div>
                                        <div class="notif-text-wrapper">
                                            <div class="small text-gray-500"><?= date('d F Y, H:i', strtotime($waktu)); ?></div>
                                            <span class="<?= $font_weight; ?> notif-text">
                                                Pendaftaran baru atas nama <?= esc($nama); ?> dengan nomor pendaftaran <?= esc($no_reg); ?>
                                            </span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </div>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span
                                    class="mr-2 d-none d-lg-inline topbar-user-name"><?= esc($user['username'] ?? 'Admin'); ?></span>
                                <?php if (isset($user['foto']) && $user['foto'] !== ''): ?>
                                <img class="img-profile" src="<?= esc(base_url('uploads/' . $user['foto'])); ?>" alt="Foto profil">
                                <?php else: ?>
                                <span class="avatar-initial" aria-label="Avatar <?= esc($user['username'] ?? 'User'); ?>">
                                    <?= esc(user_initials($user['username'] ?? 'User')); ?>
                                </span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <?php if ($is_admin_user): ?>
                                <a class="dropdown-item <?= (!$updater_available || $updater_is_open) ? 'text-gray-400 disabled' : ''; ?>" href="#" id="btnActivateUpdate" <?= (!$updater_available || $updater_is_open) ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                    <i class="fas fa-toggle-<?= $updater_is_open ? 'on text-warning' : 'off text-gray-400'; ?> fa-sm fa-fw mr-2"></i>
                                    <?= $updater_is_open ? 'Update Sistem Aktif' : 'Aktifkan Update Sistem'; ?>
                                </a>
                                <a class="dropdown-item <?= $updater_is_open ? '' : 'disabled text-gray-400'; ?>" href="#" id="btnSystemUpdate" <?= $updater_is_open ? '' : 'aria-disabled="true" tabindex="-1"'; ?>>
                                    <i class="fas fa-sync-alt fa-sm fa-fw mr-2 <?= $updater_is_open ? 'text-primary' : 'text-gray-400'; ?>"></i>
                                    Update Sistem
                                </a>
                                <div class="dropdown-divider"></div>
                                <?php endif; ?>
                                <a class="dropdown-item" href="#" id="btnLogout">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
