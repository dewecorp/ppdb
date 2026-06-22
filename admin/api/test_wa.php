<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$token = get_option('wa_token', '');

if ($token === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Token Fonnte belum diisi. Simpan pengaturan terlebih dahulu.'
    ]);
    exit;
}

// Kirim pesan tes ke nomor panitia (madrasah) jika ada, atau ke nomor yang dikirim via POST
$testTo = isset($_POST['test_number']) ? trim($_POST['test_number']) : '';
if ($testTo === '') {
    $info = madrasah_info();
    $testTo = !empty($info['hp_panitia']) ? $info['hp_panitia'] : (!empty($info['hp_kepala']) ? $info['hp_kepala'] : '');
}
if ($testTo === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Tidak ada nomor tujuan tes. Isi nomor HP panitia di data Madrasah atau masukkan nomor di bawah.'
    ]);
    exit;
}

$testMessage = "Assalamu'alaikum,\nIni adalah pesan uji coba dari sistem PPDB.\nJika Anda menerima pesan ini, integrasi WhatsApp berhasil.\nWaktu kirim: " . date('d-m-Y H:i:s');

$ok = send_whatsapp($testTo, $testMessage);

if ($ok) {
    echo json_encode([
        'success' => true,
        'message' => 'Pesan uji coba berhasil dikirim ke ' . normalize_phone($testTo) . '.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim pesan uji coba. Cek Activity Log untuk detail error.'
    ]);
}
