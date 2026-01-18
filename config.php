<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    $year = date('Y');
    $prefix = 'PPDB' . $year;

    $stmt = $mysqli->prepare('SELECT no_pendaftaran FROM pendaftar WHERE no_pendaftaran LIKE CONCAT(?, "%") ORDER BY id DESC LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $prefix);
        $stmt->execute();
        $stmt->bind_result($lastNo);
        if ($stmt->fetch()) {
            $stmt->close();
            $lastNumber = (int)substr($lastNo, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $stmt->close();
            $nextNumber = 1;
        }
    } else {
        $nextNumber = 1;
    }

    return $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
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

