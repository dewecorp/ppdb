<?php
require_once dirname(dirname(__FILE__)) . '/config.php';

if (!is_logged_in()) {
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

require_admin();

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('File tidak ditemukan.');
}

$file = basename($_GET['file']);
if (!preg_match('/^backup-\d{8}-\d{6}\.sql$/', $file)) {
    http_response_code(400);
    exit('Nama file backup tidak valid.');
}
$path = __DIR__ . '/../backups/' . $file;

if (!is_file($path)) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

log_activity('download_backup', 'Unduh file backup ' . $file . ' (' . number_format(filesize($path) / 1024, 2) . ' KB)');

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
