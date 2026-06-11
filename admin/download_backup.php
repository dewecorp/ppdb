<?php
require_once dirname(dirname(__FILE__)) . '/config.php';

if (!is_logged_in()) {
    header('Location: ' . base_url('admin/login.php'));
    exit;
}

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('File tidak ditemukan.');
}

$file = basename($_GET['file']);
$path = __DIR__ . '/../backups/' . $file;

if (!is_file($path)) {
    http_response_code(404);
    exit('File tidak ditemukan.');
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;

