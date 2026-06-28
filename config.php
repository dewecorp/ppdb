<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

// APP_VERSION dibaca dari database, hanya berubah saat update berhasil
function app_version(): string {
    return get_option('app_version', '00000000000000');
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ppdb_2026';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');
$mysqli->query("SET time_zone = '+07:00'");

function base_url(string $path = ''): string
{
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // str_ireplace (case-insensitive) supaya aman di Windows
    // di mana DOCUMENT_ROOT bisa D:\... sedangkan __DIR__ d:\...
    $doc_root    = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $project_dir = str_replace('\\', '/', __DIR__);
    $base_path   = str_ireplace($doc_root, '', $project_dir);
    $base_path   = '/' . ltrim($base_path, '/');
    $base_path   = rtrim($base_path, '/');

    $base = $protocol . "://" . $host . $base_path;

    if ($path === '') {
        return $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}

// Activity logs bootstrap: ensure table and purge entries older than 24 hours
@$mysqli->query('CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    message TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at),
    INDEX (action),
    INDEX (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
@$mysqli->query('DELETE FROM activity_logs WHERE created_at < (NOW() - INTERVAL 1 DAY)');
// Ensure schema adjustments: drop obsolete columns if exist
if ($chk = @$mysqli->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pendaftar' AND COLUMN_NAME = 'akte'")) {
    @$chk->execute();
    @$chk->bind_result($cnt);
    @$chk->fetch();
    @$chk->close();
    if ((int)$cnt > 0) {
        @$mysqli->query("ALTER TABLE pendaftar DROP COLUMN akte");
    }
}

// User roles bootstrap. Existing users become admin so older installations keep working.
if ($chk = @$mysqli->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'")) {
    @$chk->execute();
    @$chk->bind_result($cntRole);
    @$chk->fetch();
    @$chk->close();
    if ((int)$cntRole === 0) {
        @$mysqli->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER password");
    }
}

function esc($value): string
{
    if ($value === null) {
        return '';
    }
    if (is_bool($value)) {
        return $value ? '1' : '0';
    }
    if (is_scalar($value)) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    if (is_object($value) && method_exists($value, '__toString')) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
    return '';
}

function format_tanggal(?string $date): string
{
    $date = trim((string)$date);
    if ($date === '' || $date === '0000-00-00') {
        return '';
    }

    $dateOnly = substr($date, 0, 10);
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $dateOnly);
    if ($parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d') === $dateOnly) {
        return $parsed->format('d-m-Y');
    }

    $timestamp = strtotime($date);
    return $timestamp !== false ? date('d-m-Y', $timestamp) : $date;
}

function user_initials(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return 'U';
    }

    $parts = preg_split('/\s+/', $name);
    $initials = '';
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        if (preg_match('/./u', $part, $match)) {
            $initials .= strtoupper($match[0]);
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }

    return $initials !== '' ? $initials : 'U';
}

function get_option(string $name, string $default = ''): string
{
    global $mysqli;

    $stmt = $mysqli->prepare('SELECT nilai FROM pengaturan WHERE nama = ? LIMIT 1');
    if (!$stmt) {
        return $default;
    }
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->bind_result($nilai);
    if ($stmt->fetch()) {
        $stmt->close();
        return (string)$nilai;
    }
    $stmt->close();
    return $default;
}

function set_option(string $name, string $value): bool
{
    global $mysqli;

    $stmt = $mysqli->prepare('INSERT INTO pengaturan (nama, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('ss', $name, $value);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message === null) {
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }

    $_SESSION['flash'][$key] = $message;
    return null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function log_activity(string $action, string $message = '', ?int $user_id = null): void
{
    global $mysqli;
    $uid = $user_id ?? (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
    $stmt = $mysqli->prepare('INSERT INTO activity_logs (user_id, action, message) VALUES (?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('iss', $uid, $action, $message);
        @$stmt->execute();
        $stmt->close();
    }
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . base_url('admin/login'));
        exit;
    }
}

function current_user_role(): string
{
    $user = current_user();
    $role = isset($user['role']) ? (string)$user['role'] : 'admin';
    return $role === 'panitia' ? 'panitia' : 'admin';
}

function is_admin(): bool
{
    return current_user_role() === 'admin';
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        flash('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        header('Location: ' . base_url('admin/dashboard'));
        exit;
    }
}

function current_user(): ?array
{
    global $mysqli;
    if (!is_logged_in()) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $stmt = $mysqli->prepare('SELECT id, username, foto, role FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cachedUser = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $cachedUser;
}

function generate_no_pendaftaran(): string
{
    global $mysqli;

    $ta = get_option('tahun_ajaran', '');
    if (preg_match('/^\s*(\d{4})\s*\/\s*\d{4}\s*$/', $ta, $m)) {
        $year = $m[1];
    } else {
        $year = date('Y');
    }
    $prefix = 'PPDB' . $year;

    $optionKey = 'sequence_ppdb_' . $year;
    $lastSeq = (int)get_option($optionKey, '0');
    if ($lastSeq > 0) {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM pendaftar WHERE no_pendaftaran LIKE CONCAT(?, "%")');
        if ($stmt) {
            $stmt->bind_param('s', $prefix);
            $stmt->execute();
            $stmt->bind_result($cntYear);
            $stmt->fetch();
            $stmt->close();
            if ((int)$cntYear === 0) {
                $lastSeq = 0;
                set_option($optionKey, '0');
            }
        }
    }

    $nextNumber = $lastSeq + 1;
    set_option($optionKey, (string)$nextNumber);

    return $prefix . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
}

function count_pendaftar(?string $status = null): int
{
    global $mysqli;

    if ($status === null) {
        $sql = 'SELECT COUNT(*) FROM pendaftar';
        $stmt = $mysqli->prepare($sql);
    } else {
        $sql = 'SELECT COUNT(*) FROM pendaftar WHERE status_daftar = ?';
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $status);
        }
    }

    if (!$stmt) {
        return 0;
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return (int)$count;
}

function reset_no_pendaftaran(): bool
{
    $ta = get_option('tahun_ajaran', '');
    if (preg_match('/^\s*(\d{4})\s*\/\s*\d{4}\s*$/', $ta, $m)) {
        $year = $m[1];
    } else {
        $year = date('Y');
    }
    return set_option('sequence_ppdb_' . $year, '0');
}

function get_ppdb_status(): string
{
    $manual = get_option('status_pendaftaran', 'tutup');
    $start = get_option('pendaftaran_start_at', '');
    $end = get_option('pendaftaran_end_at', '') !== '' ? get_option('pendaftaran_end_at', '') : get_option('pendaftaran_open_until', '');
    if ($start === '' && $end === '') {
        return $manual;
    }
    $now = time();
    $tsStart = $start !== '' ? strtotime($start) : false;
    $tsEnd = $end !== '' ? strtotime($end) : false;
    if ($tsStart !== false && $now < $tsStart) {
        if ($manual !== 'tutup') set_option('status_pendaftaran', 'tutup');
        return 'tutup';
    }
    if ($tsEnd !== false && $now > $tsEnd) {
        if ($manual !== 'tutup') set_option('status_pendaftaran', 'tutup');
        return 'tutup';
    }
    if ($manual !== 'buka') set_option('status_pendaftaran', 'buka');
    return 'buka';
}
function madrasah_info(): array
{
    global $mysqli;
    $info = [
        'nama' => 'Madrasah',
        'email' => '',
        'hp_panitia' => '',
        'hp_kepala' => ''
    ];
    if ($res = $mysqli->query('SELECT nama, email, hp_panitia, hp_kepala FROM madrasah LIMIT 1')) {
        if ($row = $res->fetch_assoc()) {
            $info = $row;
        }
        $res->free();
    }
    return $info;
}

function send_email(string $to, string $subject, string $message): bool
{
    $host = get_option('smtp_host');
    
    // Jika SMTP Host kosong, gunakan mail() bawaan
    if (empty($host)) {
        $info = madrasah_info();
        $fromName = trim((string)$info['nama']) !== '' ? (string)$info['nama'] : 'Panitia PPDB';
        $fromEmail = trim((string)$info['email']) !== '' ? (string)$info['email'] : 'no-reply@localhost';
        $headers = [];
        $headers[] = 'From: ' . $fromName . ' <' . $fromEmail . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headersStr = implode("\r\n", $headers);
        return @mail($to, $subject, $message, $headersStr);
    }

    // SMTP Implementation
    $port = get_option('smtp_port', '465');
    $user = get_option('smtp_user');
    $pass = get_option('smtp_pass');
    $secure = get_option('smtp_secure', 'ssl');
    $fromEmail = get_option('email_from');
    $info = madrasah_info();
    $fromName = $info['nama'];

    if (empty($fromEmail)) $fromEmail = $user;

    $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $protocol = ($secure === 'ssl' || $port == 465) ? 'ssl://' : '';
    $socket = @stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if (!$socket) {
        log_activity('smtp_error', "Connect failed: $errstr ($errno)");
        return false;
    }

    $read = function() use ($socket) {
        $s = '';
        while($str = fgets($socket, 515)) {
            $s .= $str;
            if(substr($str, 3, 1) == " ") break;
        }
        return $s;
    };
    $write = function($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };

    $read(); // banner
    $write('EHLO ' . $_SERVER['SERVER_NAME']);
    $read();

    if ($secure === 'tls' && $port != 465) {
        $write('STARTTLS');
        if (substr($read(), 0, 3) != '220') return false;
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $write('EHLO ' . $_SERVER['SERVER_NAME']);
        $read();
    }

    if ($user && $pass) {
        $write('AUTH LOGIN');
        $read();
        $write(base64_encode($user));
        $read();
        $write(base64_encode($pass));
        if (substr($read(), 0, 3) != '235') return false;
    }

    $write("MAIL FROM: <$fromEmail>");
    $read();
    $write("RCPT TO: <$to>");
    $read();
    $write('DATA');
    $read();

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Date: " . date('r') . "\r\n";
    $headers .= "From: $fromName <$fromEmail>\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";

    $write($headers . "\r\n" . $message . "\r\n.");
    $result = substr($read(), 0, 3) == '250';

    $write('QUIT');
    fclose($socket);

    return $result;
}

function normalize_phone(string $hp): string
{
    $digits = preg_replace('/\D+/', '', $hp);
    if ($digits === '') {
        return '';
    }
    // Fonnte menerima format 08xxx (countryCode default 62)
    // atau 628xxx. Kembalikan format apa adanya selama valid.
    if (strpos($digits, '62') === 0) {
        return $digits;
    }
    if (strpos($digits, '0') === 0) {
        return $digits; // biarkan 08xxx, Fonnte auto-convert
    }
    return $digits;
}

function send_whatsapp(string $to, string $message): bool
{
    $token = get_option('wa_token', '');
    if ($token === '') {
        log_activity('whatsapp_error', 'Token Fonnte belum diisi. Simpan token di Pengaturan.');
        return false;
    }
    $to = normalize_phone($to);
    if ($to === '') {
        log_activity('whatsapp_error', 'Nomor tujuan WA kosong atau tidak valid.');
        return false;
    }
    $url = 'https://api.fonnte.com/send';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => http_build_query([
            'target'      => $to,
            'message'     => $message,
            'countryCode' => '62',
        ]),
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . $token,  // Fonnte: tanpa "Bearer"
        ],
    ]);
    $resp     = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    $respData    = json_decode($resp, true);
    $apiSuccess  = isset($respData['status']) && $respData['status'] === true;
    $apiReason   = isset($respData['reason']) ? $respData['reason'] : '';

    if ($httpCode < 200 || $httpCode >= 300 || !$apiSuccess) {
        $reasonLower = strtolower((string)$apiReason);
        if ($to === '' || strpos($reasonLower, 'target') !== false || strpos($reasonLower, 'invalid') !== false) {
            $messageLog = 'WhatsApp tidak terkirim karena nomor tujuan tidak valid atau belum terisi.';
        } elseif ($token === '') {
            $messageLog = 'WhatsApp tidak terkirim karena token WA belum diatur.';
        } elseif ($curlErr !== '') {
            $messageLog = 'WhatsApp tidak terkirim karena koneksi ke layanan WA bermasalah.';
        } else {
            $messageLog = 'WhatsApp tidak terkirim. Periksa nomor tujuan, token WA, dan status perangkat WA.';
        }
        log_activity('whatsapp_error', $messageLog);
        return false;
    }

    log_activity('whatsapp_sent', 'WhatsApp berhasil terkirim ke nomor tujuan.');
    return true;
}
