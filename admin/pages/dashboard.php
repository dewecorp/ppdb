<?php
if (!function_exists('time_ago_id')) {
    function time_ago_id(string $datetime): string
    {
        $tz = new DateTimeZone('Asia/Jakarta');
        $time = new DateTime($datetime, $tz);
        $now = new DateTime('now', $tz);
        $diff = $now->getTimestamp() - $time->getTimestamp();

        if ($diff < 60) {
            return 'baru saja';
        }

        $minute = 60;
        $hour = 3600;
        $day = 86400;
        $week = 604800;

        if ($diff < $hour) {
            $value = floor($diff / $minute);
            return $value . ' menit yang lalu';
        }
        if ($diff < $day) {
            $value = floor($diff / $hour);
            return $value . ' jam yang lalu';
        }
        if ($diff < $week) {
            $value = floor($diff / $day);
            return $value . ' hari yang lalu';
        }

        $value = floor($diff / $week);
        return $value . ' minggu yang lalu';
    }
}

$total = count_pendaftar();
$diterima = count_pendaftar('diterima');
$ditolak = count_pendaftar('ditolak');
$proses = count_pendaftar('proses');

$activity_logs = [];
if ($result = $mysqli->query('SELECT id, user_id, action, message, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 10')) {
    while ($row = $result->fetch_assoc()) {
        $activity_logs[] = $row;
    }
    $result->free();
}
$activity_count = count($activity_logs);
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <div class="row">

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Jumlah Pendaftar
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Diterima</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $diterima; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Ditolak</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $ditolak; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Proses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $proses; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-xl-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Aktivitas Terbaru
                        <?php if ($activity_count > 0): ?>
                            (<?= $activity_count; ?>)
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="card-body activity-scroll">
                    <?php if (empty($activity_logs)): ?>
                        <p class="mb-0 text-muted">Belum ada aktivitas tercatat dalam 24 jam terakhir.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php
                            $current_date_label = null;
                            foreach ($activity_logs as $log):
                                $timestamp = strtotime($log['created_at']);
                                $date_label = date('d M Y', $timestamp);
                                $time_label = date('H:i', $timestamp);
                                $time_ago = time_ago_id($log['created_at']);

                                $icon = 'fas fa-info';
                                $bg = 'bg-primary';
                                $title = 'Aktivitas Sistem';

                                if ($log['action'] === 'login') {
                                    $icon = 'fas fa-sign-in-alt';
                                    $bg = 'bg-success';
                                    $title = 'Login Admin';
                                } elseif ($log['action'] === 'update_pendaftar_status') {
                                    $icon = 'fas fa-user-check';
                                    $bg = 'bg-info';
                                    $title = 'Perubahan Status Pendaftar';
                                } elseif ($log['action'] === 'delete_pendaftar' || $log['action'] === 'delete_pendaftar_bulk') {
                                    $icon = 'fas fa-user-times';
                                    $bg = 'bg-danger';
                                    $title = 'Penghapusan Data Pendaftar';
                                } elseif ($log['action'] === 'create_user' || $log['action'] === 'update_user') {
                                    $icon = 'fas fa-user-cog';
                                    $bg = 'bg-warning';
                                    $title = 'Pengaturan Pengguna';
                                } elseif ($log['action'] === 'update_madrasah') {
                                    $icon = 'fas fa-school';
                                    $bg = 'bg-primary';
                                    $title = 'Pengaturan Madrasah';
                                } elseif ($log['action'] === 'delete_backup') {
                                    $icon = 'fas fa-database';
                                    $bg = 'bg-danger';
                                    $title = 'Penghapusan Backup';
                                }
                            ?>
                                <?php if ($date_label !== $current_date_label): ?>
                                    <div class="time-label">
                                        <span class="bg-primary"><?= esc($date_label); ?></span>
                                    </div>
                                    <?php $current_date_label = $date_label; ?>
                                <?php endif; ?>
                                <div>
                                    <i class="<?= esc($icon); ?> <?= esc($bg); ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time">
                                            <i class="fas fa-clock"></i>
                                            <?= esc($time_label); ?> (<?= esc($time_ago); ?>)
                                        </span>
                                        <h3 class="timeline-header"><?= esc($title); ?></h3>
                                        <div class="timeline-body">
                                            <?php if (!empty($log['message'])): ?>
                                                <?= esc($log['message']); ?>
                                            <?php else: ?>
                                                Aktivitas tercatat.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
