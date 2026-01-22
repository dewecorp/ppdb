<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ppdb_2026';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    die('Koneksi database gagal: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

function base_url(string $path = ''): string
{
    $base = rtrim('http://localhost/ppdb_2026', '/');
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
        header('Location: ' . base_url('admin/login.php'));
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

    $stmt = $mysqli->prepare('SELECT id, username, foto FROM users WHERE id = ? LIMIT 1');
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

function normalize_phone(string $hp): string
{
    $digits = preg_replace('/\D+/', '', $hp);
    if ($digits === '') {
        return '';
    }
    if (strpos($digits, '62') === 0) {
        return $digits;
    }
    if (strpos($digits, '0') === 0) {
        return '62' . substr($digits, 1);
    }
    return $digits;
}

function send_whatsapp(string $to, string $message): bool
{
    $token = get_option('wa_token', '');
    $phoneId = get_option('wa_phone_id', '');
    if ($token === '' || $phoneId === '') {
        return false;
    }
    $to = normalize_phone($to);
    if ($to === '') {
        return false;
    }
    $url = 'https://graph.facebook.com/v17.0/' . $phoneId . '/messages';
    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $message
        ]
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $resp = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode >= 200 && $httpCode < 300;
}
