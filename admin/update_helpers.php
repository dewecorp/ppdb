<?php

function updater_repo_url(): string
{
    return 'https://github.com/dewecorp/ppdb';
}

function updater_repo_api_url(): string
{
    return 'https://api.github.com/repos/dewecorp/ppdb/releases/latest';
}

function updater_allowed_host(string $url): bool
{
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    return in_array($host, [
        'github.com',
        'raw.githubusercontent.com',
        'api.github.com',
        'objects.githubusercontent.com',
        'codeload.github.com',
    ], true);
}

function updater_http_get(string $url, ?string &$error = null): ?string
{
    $error = null;
    if (!filter_var($url, FILTER_VALIDATE_URL) || !updater_allowed_host($url)) {
        $error = 'URL harus berasal dari GitHub.';
        return null;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'PPDB-Updater/' . app_version(),
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            $error = $curlError !== '' ? $curlError : 'Gagal mengunduh data dari GitHub.';
            return null;
        }

        return (string)$body;
    }

    $context = stream_context_create([
        'http' => [
            'timeout' => 60,
            'header' => "User-Agent: PPDB-Updater/" . app_version() . "\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        $error = 'Gagal mengunduh data dari GitHub.';
        return null;
    }

    return (string)$body;
}

function updater_latest_release(?string &$error = null): ?array
{
    $body = updater_http_get(updater_repo_api_url(), $error);
    if ($body === null) {
        return null;
    }

    $release = json_decode($body, true);
    if (!is_array($release)) {
        $error = 'Respons GitHub Release bukan JSON valid.';
        return null;
    }

    $version = trim((string)($release['tag_name'] ?? ''));
    $zipUrl = trim((string)($release['zipball_url'] ?? ''));

    if ($version === '' || $zipUrl === '') {
        $error = 'Repo GitHub belum memiliki release terbaru yang valid.';
        return null;
    }
    if (!filter_var($zipUrl, FILTER_VALIDATE_URL) || !updater_allowed_host($zipUrl)) {
        $error = 'URL paket update harus berasal dari GitHub.';
        return null;
    }

    return [
        'version' => $version,
        'zip_url' => $zipUrl,
        'release_url' => trim((string)($release['html_url'] ?? updater_repo_url())),
        'notes' => trim((string)($release['body'] ?? '')),
    ];
}

function updater_is_open(): bool
{
    if (get_option('updater_dropdown_enabled', '0') !== '1') {
        return false;
    }
    return time() < (int)get_option('updater_enabled_until', '0');
}

function updater_open_minutes(int $minutes = 15): void
{
    $minutes = max(1, min(60, $minutes));
    set_option('updater_dropdown_enabled', '1');
    set_option('updater_enabled_until', (string)(time() + ($minutes * 60)));
    set_option('updater_nonce', bin2hex(random_bytes(16)));
}

function updater_close(): void
{
    set_option('updater_dropdown_enabled', '0');
    set_option('updater_enabled_until', '0');
    set_option('updater_nonce', '');
}

function updater_current_password_valid(string $password): bool
{
    global $mysqli;
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid <= 0 || $password === '') {
        return false;
    }

    $stmt = $mysqli->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $stmt->bind_result($hash);
    $found = $stmt->fetch();
    $stmt->close();

    return $found && password_verify($password, (string)$hash);
}

function updater_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path) && !is_link($path)) {
            updater_rrmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function updater_copy_tree(string $source, string $target, array $skipTop = []): void
{
    if (!is_dir($source)) {
        return;
    }
    if (!is_dir($target)) {
        mkdir($target, 0755, true);
    }

    $items = scandir($source);
    if ($items === false) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || in_array($item, $skipTop, true)) {
            continue;
        }
        $src = $source . DIRECTORY_SEPARATOR . $item;
        $dst = $target . DIRECTORY_SEPARATOR . $item;
        if (is_dir($src) && !is_link($src)) {
            updater_copy_tree($src, $dst);
        } elseif (is_file($src)) {
            copy($src, $dst);
            @chmod($dst, 0644);
        }
    }
}

function updater_package_root(string $extractDir): string
{
    $items = array_values(array_filter(scandir($extractDir) ?: [], function ($item) {
        return $item !== '.' && $item !== '..';
    }));

    if (count($items) === 1 && is_dir($extractDir . DIRECTORY_SEPARATOR . $items[0])) {
        return $extractDir . DIRECTORY_SEPARATOR . $items[0];
    }

    return $extractDir;
}

function updater_validate_package(string $root, ?string &$error = null): bool
{
    if (!is_file($root . DIRECTORY_SEPARATOR . 'index.php') || !is_dir($root . DIRECTORY_SEPARATOR . 'admin')) {
        $error = 'Struktur paket update tidak dikenali.';
        return false;
    }

    $blocked = ['.git', '.env', 'uploads', 'backups', '_release'];
    foreach ($blocked as $name) {
        if (file_exists($root . DIRECTORY_SEPARATOR . $name)) {
            $error = 'Paket update memuat item yang tidak boleh ditimpa: ' . $name;
            return false;
        }
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if (preg_match('#/(uploads|backups|_release)/#i', $path)) {
            $error = 'Paket update memuat folder terlarang.';
            return false;
        }
        if (preg_match('/\.(phtml|phar|php[0-9])$/i', $path)) {
            $error = 'Paket update memuat tipe script yang tidak diizinkan.';
            return false;
        }
    }

    return true;
}

function updater_install(array $release, ?string &$error = null): bool
{
    if (!class_exists('ZipArchive')) {
        $error = 'Ekstensi PHP ZipArchive belum aktif di hosting.';
        return false;
    }

    $workDir = ppdb_private_path('updater-work');
    updater_rrmdir($workDir);
    mkdir($workDir, 0755, true);

    $zipBody = updater_http_get($release['zip_url'], $error);
    if ($zipBody === null) {
        return false;
    }

    $zipPath = $workDir . DIRECTORY_SEPARATOR . 'release.zip';
    file_put_contents($zipPath, $zipBody);

    $extractDir = $workDir . DIRECTORY_SEPARATOR . 'extract';
    mkdir($extractDir, 0755, true);
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        $error = 'Paket ZIP update tidak dapat dibuka.';
        updater_rrmdir($workDir);
        return false;
    }
    $zip->extractTo($extractDir);
    $zip->close();

    $packageRoot = updater_package_root($extractDir);
    if (!updater_validate_package($packageRoot, $error)) {
        updater_rrmdir($workDir);
        return false;
    }

    $appRoot = dirname(__DIR__);
    $backupDir = ppdb_private_path('updater-backups' . DIRECTORY_SEPARATOR . 'backup-' . date('Ymd-His'));
    mkdir($backupDir, 0755, true);
    updater_copy_tree($appRoot, $backupDir, ['uploads', 'backups', '_release', '.git']);

    updater_copy_tree($packageRoot, $appRoot, ['config.php', 'uploads', 'backups', '_release', 'tools', '.git']);
    set_option('app_version', $release['version']);
    updater_close();
    updater_rrmdir($workDir);
    log_activity('system_update', 'Update sistem ke versi ' . $release['version']);

    return true;
}
