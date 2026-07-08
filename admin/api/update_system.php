<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/bootstrap.php';
require_admin();

header('Content-Type: application/json');
http_response_code(410);

echo json_encode([
    'success' => false,
    'message' => 'Update sistem lewat web sudah dinonaktifkan permanen demi keamanan. Update aplikasi dilakukan manual dari paket release yang bersih.'
]);
exit;
