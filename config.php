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

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postedToken = $_POST['csrf_token'] ?? '';

    return is_string($sessionToken)
        && is_string($postedToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $postedToken);
}

function require_valid_csrf(?string $redirect = null): void
{
    if (csrf_verify()) {
        return;
    }

    flash('error', 'Sesi form tidak valid. Silakan ulangi tindakan Anda.');
    header('Location: ' . ($redirect ?: base_url('admin/dashboard')));
    exit;
}

function app_secret(): string
{
    $secret = get_option('app_secret', '');
    if ($secret === '') {
        $secret = bin2hex(random_bytes(32));
        set_option('app_secret', $secret);
    }

    return $secret;
}

function pendaftar_access_token($id, $noPendaftaran): string
{
    return hash_hmac('sha256', (int)$id . '|' . (string)$noPendaftaran, app_secret());
}

function pendaftar_token_valid($id, $noPendaftaran, $token): bool
{
    $token = (string)$token;
    return $token !== '' && hash_equals(pendaftar_access_token($id, $noPendaftaran), $token);
}

function mask_sensitive($value, int $visibleStart = 3, int $visibleEnd = 2): string
{
    $value = trim((string)$value);
    $length = strlen($value);
    if ($length === 0) {
        return '';
    }
    if ($length <= $visibleStart + $visibleEnd) {
        return str_repeat('*', $length);
    }

    return substr($value, 0, $visibleStart) . str_repeat('*', $length - $visibleStart - $visibleEnd) . substr($value, -$visibleEnd);
}

function ppdb_private_path(string $path = ''): string
{
    $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ppdb_2026_private';
    if (!is_dir($base)) {
        @mkdir($base, 0755, true);
    }
    $denyFile = $base . DIRECTORY_SEPARATOR . '.htaccess';
    if (!is_file($denyFile)) {
        @file_put_contents($denyFile, "Require all denied\nDeny from all\n");
    }

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
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

function secure_uploaded_image(array $file, string $prefix, string $uploadDir, ?string &$error = null): ?string
{
    $error = null;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload file gagal.';
        return null;
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        $error = 'File upload tidak valid.';
        return null;
    }

    $maxSize = 2 * 1024 * 1024;
    if ((int)($file['size'] ?? 0) > $maxSize) {
        $error = 'Ukuran gambar maksimal 2 MB.';
        return null;
    }

    $imageInfo = @getimagesize($tmp);
    if ($imageInfo === false || empty($imageInfo['mime'])) {
        $error = 'File harus berupa gambar JPG atau PNG yang valid.';
        return null;
    }

    $mime = (string)$imageInfo['mime'];
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    if (!isset($allowed[$mime])) {
        $error = 'Format gambar hanya boleh JPG atau PNG.';
        return null;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $tmp) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($detected !== false && $detected !== $mime) {
            $error = 'Isi file gambar tidak sesuai.';
            return null;
        }
    }

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $error = 'Folder upload tidak dapat dibuat.';
        return null;
    }

    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '-', $prefix);
    $random = bin2hex(random_bytes(8));
    $fileName = $safePrefix . '-' . date('YmdHis') . '-' . $random . '.' . $allowed[$mime];
    $dest = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmp, $dest)) {
        $error = 'Gagal menyimpan file upload.';
        return null;
    }

    @chmod($dest, 0644);
    return $fileName;
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

function pendaftar_duplicate_fields(): array
{
    return [
        'nama_lengkap',
        'nik',
        'kk',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'status_keluarga',
        'anak_ke',
        'jumlah_saudara',
        'asal_tk',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'nama_wali',
        'pekerjaan_wali',
        'email',
        'hp',
        'kip',
        'pkh',
    ];
}

function normalize_pendaftar_duplicate_value(string $field, $value): string
{
    $value = trim((string)$value);

    if (in_array($field, ['nik', 'kk', 'hp'], true)) {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    if (in_array($field, ['anak_ke', 'jumlah_saudara'], true)) {
        return $value === '' ? '0' : (string)(int)$value;
    }

    if (in_array($field, ['kip', 'pkh'], true)) {
        return strtolower($value) === 'ya' ? 'ya' : 'tidak';
    }

    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function pendaftar_duplicate_sql_expression(string $field): string
{
    if (in_array($field, ['nik', 'kk', 'hp'], true)) {
        $expr = "TRIM(COALESCE(`$field`, ''))";
        foreach ([' ', '-', '.', '+', '/', '(', ')'] as $char) {
            $expr = "REPLACE($expr, '$char', '')";
        }

        return $expr;
    }

    if (in_array($field, ['anak_ke', 'jumlah_saudara'], true)) {
        return "COALESCE(NULLIF(TRIM(CAST(`$field` AS CHAR)), ''), '0')";
    }

    return "LOWER(TRIM(COALESCE(`$field`, '')))";
}

function pendaftar_duplicate_exists(array $data, ?int $excludeId = null, ?array $fields = null): ?array
{
    global $mysqli;

    $fields = $fields ?? pendaftar_duplicate_fields();
    $allowed = array_flip(pendaftar_duplicate_fields());
    $where = [];
    $params = [];

    foreach ($fields as $field) {
        if (!isset($allowed[$field])) {
            continue;
        }
        $where[] = pendaftar_duplicate_sql_expression($field) . ' = ?';
        $params[] = normalize_pendaftar_duplicate_value($field, $data[$field] ?? '');
    }

    if (empty($where)) {
        return null;
    }

    $sql = 'SELECT id, no_pendaftaran, nama_lengkap FROM pendaftar WHERE ' . implode(' AND ', $where);
    if ($excludeId !== null && $excludeId > 0) {
        $sql .= ' AND id <> ?';
        $params[] = $excludeId;
    }
    $sql .= ' LIMIT 1';

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $types = str_repeat('s', count($params));
    if ($excludeId !== null && $excludeId > 0) {
        $types = substr($types, 0, -1) . 'i';
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? ($result->fetch_assoc() ?: null) : null;
    $stmt->close();

    return $row;
}

function pendaftar_duplicate_message(array $duplicate): string
{
    $no = trim((string)($duplicate['no_pendaftaran'] ?? ''));
    if ($no !== '') {
        return 'Data pendaftar yang sama persis sudah ada dengan nomor pendaftaran ' . $no . '. Mohon periksa kembali agar tidak terjadi data ganda.';
    }

    return 'Data pendaftar yang sama persis sudah ada. Mohon periksa kembali agar tidak terjadi data ganda.';
}
