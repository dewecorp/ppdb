<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/bootstrap.php';
require_admin();

header('Content-Type: application/json');

// Block update di local environment (Laragon, XAMPP, dll)
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$isLocal = (
    strpos($host, 'localhost') !== false ||
    strpos($host, '127.0.0.1') !== false ||
    preg_match('/\.test$|\.local$|\.dev$/', $host)
);
if ($isLocal) {
    echo json_encode([
        'success' => false,
        'message' => 'Update sistem hanya dapat dijalankan di server hosting, bukan di local.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$projectRoot = dirname(dirname(dirname(__FILE__)));

// ── Konfigurasi ──────────────────────────────────────────────────────────────
$GITHUB_USER   = 'dewecorp';
$GITHUB_REPO   = 'ppdb';
$BRANCHES      = ['master', 'main']; // coba master dulu, fallback ke main

// File/folder yang TIDAK akan ditimpa (data lokal)
$PROTECTED = [
    'uploads',
    'backups',
    'config.php',
    '.htaccess',
];

// ── Helper ───────────────────────────────────────────────────────────────────
function recursiveCopy(string $src, string $dst, array $protected, string $rootDir): array
{
    $copied  = 0;
    $skipped = 0;
    $errors  = [];

    $dir = opendir($src);
    if (!$dir) {
        $errors[] = "Gagal buka direktori: $src";
        return compact('copied', 'skipped', 'errors');
    }

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') continue;

        $srcPath = $src . DIRECTORY_SEPARATOR . $file;
        $dstPath = $dst . DIRECTORY_SEPARATOR . $file;

        // Relatif terhadap root project (untuk cek protected list)
        // str_ireplace supaya aman dari perbedaan case di Windows (D:\ vs d:\)
        $relPath = ltrim(str_ireplace($rootDir, '', $dstPath), DIRECTORY_SEPARATOR . '/\\');
        $relTop  = explode(DIRECTORY_SEPARATOR, $relPath)[0];
        $relTop  = explode('/', $relTop)[0];

        // Cek apakah di-protect (cek folder top-level ATAU nama file langsung)
        if (in_array($relTop, $protected, true) || in_array($file, $protected, true)) {
            $skipped++;
            continue;
        }

        if (is_dir($srcPath)) {
            if (!is_dir($dstPath)) {
                if (!mkdir($dstPath, 0755, true)) {
                    $errors[] = "Gagal buat folder: $dstPath";
                    continue;
                }
            }
            $sub = recursiveCopy($srcPath, $dstPath, $protected, $rootDir);
            $copied  += $sub['copied'];
            $skipped += $sub['skipped'];
            $errors   = array_merge($errors, $sub['errors']);
        } else {
            if (@copy($srcPath, $dstPath)) {
                $copied++;
            } else {
                $errors[] = "Gagal copy: $file";
            }
        }
    }
    closedir($dir);
    return compact('copied', 'skipped', 'errors');
}

function recursiveRemove(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? recursiveRemove($path) : @unlink($path);
    }
    @rmdir($dir);
}

// ── Step 1: Download ZIP dari repository ─────────────────────────────────────────────────────────────────────
$log        = [];
$zipUrl     = null;
$zipFile    = $projectRoot . DIRECTORY_SEPARATOR . '_update_' . time() . '.zip';
$extractDir = $projectRoot . DIRECTORY_SEPARATOR . '_update_extract_' . time();
$ghToken    = trim(get_option('github_token', ''));

$headers = ['Accept: application/vnd.github+json'];
if ($ghToken !== '') {
    $headers[] = 'Authorization: token ' . $ghToken;
    $log[] = '>> Autentikasi: menggunakan Personal Access Token';
} else {
    $log[] = '>> Autentikasi: tanpa token (repository public)';
}

foreach ($BRANCHES as $branch) {
    $url = "https://github.com/$GITHUB_USER/$GITHUB_REPO/archive/refs/heads/$branch.zip";
    $log[] = ">> Mencoba download: $url";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'PPDB-Updater/1.0',
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    $data     = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && strlen($data) > 1024) {
        $log[] = ">> Download berhasil (branch: $branch, size: " . round(strlen($data) / 1024) . " KB)";
        file_put_contents($zipFile, $data);
        $zipUrl = $url;
        break;
    } else {
        $log[] = ">> Branch '$branch' tidak tersedia (HTTP $httpCode): $curlErr";
    }
}

if (!$zipUrl) {
    @unlink($zipFile);
    $hint = $ghToken === ''
        ? 'Repository tidak ditemukan atau private. Pastikan repository bersifat <strong>public</strong>.'
        : 'Token tidak valid atau repository tidak ditemukan. Periksa konfigurasi repository.';
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengunduh file update. ' . $hint,
        'output'  => implode("\n", $log)
    ]);
    exit;
}

// ── Step 2: Extract ZIP ──────────────────────────────────────────────────────
$log[] = '>> Mengekstrak ZIP...';

$zip = new ZipArchive();
if ($zip->open($zipFile) !== true) {
    @unlink($zipFile);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengekstrak file ZIP.',
        'output'  => implode("\n", $log)
    ]);
    exit;
}

if (!mkdir($extractDir, 0755, true)) {
    $zip->close();
    @unlink($zipFile);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal membuat folder sementara.',
        'output'  => implode("\n", $log)
    ]);
    exit;
}

$zip->extractTo($extractDir);
$zip->close();
@unlink($zipFile);

// ZIP berisi satu folder di level teratas: ppdb-main/ atau ppdb-master/
$extractedRoot = null;
$items = scandir($extractDir);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $candidate = $extractDir . DIRECTORY_SEPARATOR . $item;
    if (is_dir($candidate)) {
        $extractedRoot = $candidate;
        break;
    }
}

if (!$extractedRoot) {
    recursiveRemove($extractDir);
    echo json_encode([
        'success' => false,
        'message' => 'Struktur ZIP tidak valid (tidak ditemukan folder utama).',
        'output'  => implode("\n", $log)
    ]);
    exit;
}

$log[] = '>> ZIP diekstrak ke: ' . basename($extractedRoot);

// ── Step 3: Copy file ke project root ────────────────────────────────────────
$log[] = '>> Menyalin file (melindungi: ' . implode(', ', $PROTECTED) . ')...';

$result = recursiveCopy($extractedRoot, $projectRoot, $PROTECTED, $projectRoot);

$log[] = ">> File disalin: {$result['copied']}, dilewati: {$result['skipped']}";
if (!empty($result['errors'])) {
    $log[] = '>> Error: ' . implode(', ', array_slice($result['errors'], 0, 5));
}

// ── Step 4: Cleanup ──────────────────────────────────────────────────────────
recursiveRemove($extractDir);
$log[] = '>> Folder sementara dihapus.';

// ── Step 5: Response ─────────────────────────────────────────────────────────
$hasErrors = !empty($result['errors']);

// Simpan versi baru hanya jika update sukses
$newVersion = '';
if (!$hasErrors) {
    $newVersion = date('YmdHis');
    set_option('app_version', $newVersion);
}

echo json_encode([
    'success'    => !$hasErrors,
    'message'    => $hasErrors
        ? 'Update selesai dengan beberapa error. Cek log di bawah.'
        : 'Sistem berhasil diperbarui.',
    'version'    => $newVersion,
    'output'     => implode("\n", $log),
    'copied'     => $result['copied'],
    'skipped'    => $result['skipped'],
    'errors'     => $result['errors'],
]);
