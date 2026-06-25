<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_login();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = ['dashboard', 'madrasah', 'pendaftar', 'pengaturan', 'pengguna', 'backup'];

if (!is_admin()) {
    $allowed_pages = ['dashboard', 'madrasah', 'pendaftar', 'backup'];
}

if (!in_array($page, $allowed_pages, true)) {
    $page = 'dashboard';
}

include __DIR__ . '/template/header.php';

$page_file = __DIR__ . '/pages/' . $page . '.php';
if (file_exists($page_file)) {
    include $page_file;
} else {
    echo '<div class="container-fluid"><h1 class="h4 text-gray-800">Halaman tidak ditemukan.</h1></div>';
}

include __DIR__ . '/template/footer.php';
