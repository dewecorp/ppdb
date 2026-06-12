<?php
if (!function_exists('time_ago_id')) {
    function time_ago_id(string $datetime): string
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

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

// Data Grafik Pendaftaran (Semua Waktu)
$chart_data_assoc = [];

$query_chart = "SELECT DATE(created_at) as tgl, COUNT(*) as jumlah 
          FROM pendaftar 
          GROUP BY DATE(created_at) 
          ORDER BY DATE(created_at) ASC";

if ($result_chart = $mysqli->query($query_chart)) {
    while ($row_chart = $result_chart->fetch_assoc()) {
        $tgl_formatted = date('d M Y', strtotime($row_chart['tgl']));
        if (!isset($chart_data_assoc[$tgl_formatted])) {
            $chart_data_assoc[$tgl_formatted] = 0;
        }
        $chart_data_assoc[$tgl_formatted] += $row_chart['jumlah'];
    }
    $result_chart->free();
}

$chart_labels = array_keys($chart_data_assoc);
$chart_data = array_values($chart_data_assoc);

// Data Pendaftar Terbaru
$recent_registrants = [];
if ($res_recent = $mysqli->query("SELECT nama_lengkap, no_pendaftaran, status_daftar, created_at FROM pendaftar ORDER BY created_at DESC LIMIT 20")) {
    while ($row_recent = $res_recent->fetch_assoc()) {
        $recent_registrants[] = $row_recent;
    }
    $res_recent->free();
}
?>
<style>
    .activity-scroll, .recent-registrants-scroll {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
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
        
        <!-- Area Chart -->
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Grafik Pendaftaran</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myAreaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendaftar Terbaru -->
        <div class="col-xl-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Pendaftar Terbaru (Total: <?= $total; ?>)</h6>
                    <a href="index.php?page=pendaftar" class="btn btn-sm btn-primary shadow-sm">Lihat Semua</a>
                </div>
                <div class="card-body recent-registrants-scroll">
                    <?php if (empty($recent_registrants)): ?>
                        <p class="text-center text-muted my-3">Belum ada pendaftar.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th>Nama</th>
                                        <th>No. Daftar</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($recent_registrants as $reg): ?>
                                    <tr>
                                        <td><?= $i++; ?></td>
                                        <td><?= esc($reg['nama_lengkap']); ?></td>
                                        <td><?= esc($reg['no_pendaftaran']); ?></td>
                                        <td>
                                            <?php if ($reg['status_daftar'] == 'diterima'): ?>
                                                <span class="badge badge-success">Diterima</span>
                                            <?php elseif ($reg['status_daftar'] == 'ditolak'): ?>
                                                <span class="badge badge-danger">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Proses</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/y', strtotime($reg['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

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
                                } elseif ($log['action'] === 'pendaftaran_baru' || $log['action'] === 'submit_pendaftaran') {
                                    $icon = 'fas fa-user-plus';
                                    $bg = 'bg-success';
                                    $title = 'Pendaftaran Baru';
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

<!-- Page level plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

<script>
// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

function number_format(number, decimals, dec_point, thousands_sep) {
  // *     example: number_format(1234.56, 2, ',', ' ');
  // *     return: '1 234,56'
  number = (number + '').replace(',', '').replace(' ', '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
}

// Area Chart Example
var ctx = document.getElementById("myAreaChart");
var myAreaChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($chart_labels); ?>,
    datasets: [{
      label: "Pendaftar",
      lineTension: 0.3,
      backgroundColor: "rgba(78, 115, 223, 0.05)",
      borderColor: "rgba(78, 115, 223, 1)",
      pointRadius: 3,
      pointBackgroundColor: "rgba(78, 115, 223, 1)",
      pointBorderColor: "rgba(78, 115, 223, 1)",
      pointHoverRadius: 3,
      pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
      pointHoverBorderColor: "rgba(78, 115, 223, 1)",
      pointHitRadius: 10,
      pointBorderWidth: 2,
      data: <?= json_encode($chart_data); ?>,
    }],
  },
  options: {
    maintainAspectRatio: false,
    layout: {
      padding: {
        left: 10,
        right: 25,
        top: 25,
        bottom: 0
      }
    },
    scales: {
      xAxes: [{
        time: {
          unit: 'date'
        },
        gridLines: {
          display: false,
          drawBorder: false
        },
        ticks: {
          maxTicksLimit: 7
        }
      }],
      yAxes: [{
        ticks: {
          precision: 0,
          stepSize: 1,
          maxTicksLimit: 5,
          padding: 10,
          beginAtZero: true,
          callback: function(value, index, values) {
            if (value === Math.floor(value)) {
              return number_format(value);
            }
            return '';
          }
        },
        gridLines: {
          color: "rgb(234, 236, 244)",
          zeroLineColor: "rgb(234, 236, 244)",
          drawBorder: false,
          borderDash: [2],
          zeroLineBorderDash: [2]
        }
      }],
    },
    legend: {
      display: false
    },
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      titleMarginBottom: 10,
      titleFontColor: '#6e707e',
      titleFontSize: 14,
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      intersect: false,
      mode: 'index',
      caretPadding: 10,
      callbacks: {
        label: function(tooltipItem, chart) {
          var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
          return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
        }
      }
    }
  }
});
</script>
