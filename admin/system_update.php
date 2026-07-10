<?php
require_once dirname(dirname(__FILE__)) . '/config.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/update_helpers.php';

require_admin();

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/dashboard'));
    exit;
}

require_valid_csrf(base_url('admin/dashboard'));

$aksi = isset($_POST['aksi']) ? (string)$_POST['aksi'] : '';

if ($aksi === 'activate') {
    updater_open_minutes(15);
    flash('success', 'Update sistem sudah diaktifkan. Jalankan hanya saat maintenance.');
    log_activity('system_update_open', 'Membuka updater sistem dari dropdown admin');
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard')));
    exit;
}

if ($aksi === 'install') {
    if (!updater_is_open()) {
        flash('error', 'Update sistem belum diaktifkan. Aktifkan update sistem terlebih dahulu.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard')));
        exit;
    }

    $updateError = null;
    $release = updater_latest_release($updateError);
    if ($release === null) {
        flash('error', $updateError ?: 'Gagal menyiapkan update dari branch GitHub.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard')));
        exit;
    }

    if (updater_install($release, $updateError)) {
        flash('success', 'Update sistem berhasil ke versi ' . $release['version'] . '. Updater sudah dinonaktifkan otomatis.');
    } else {
        flash('error', $updateError ?: 'Update sistem gagal.');
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard')));
    exit;
}

flash('error', 'Aksi update sistem tidak valid.');
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard')));
exit;
