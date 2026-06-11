<?php
require_once dirname(dirname(__FILE__)) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    log_activity('logout', 'Logout', $uid);
    session_destroy();
}

header('Location: ' . base_url('admin/login'));
exit;
