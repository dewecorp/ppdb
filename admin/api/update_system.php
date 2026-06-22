<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$projectRoot = dirname(dirname(dirname(__FILE__)));

// Check if exec/shell_exec is available
$disabled = array_map('trim', explode(',', ini_get('disable_functions')));
$canExec = !in_array('exec', $disabled) && !in_array('shell_exec', $disabled) && !in_array('proc_open', $disabled);

if (!$canExec) {
    echo json_encode([
        'success' => false,
        'message' => 'Fungsi exec/shell_exec dinonaktifkan di server ini. Hubungi penyedia hosting untuk mengaktifkannya.',
        'output'  => 'Disabled functions: ' . implode(', ', $disabled)
    ]);
    exit;
}

// Helper: run shell command and capture output
function runCmd(string $cmd, string $cwd): array
{
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $env = ['GIT_TERMINAL_PROMPT' => '0'];
    $process = proc_open($cmd, $descriptors, $pipes, $cwd, $env);

    if (!is_resource($process)) {
        return ['exit_code' => -1, 'stdout' => '', 'stderr' => 'Gagal menjalankan perintah.'];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exitCode = proc_close($process);

    return ['exit_code' => $exitCode, 'stdout' => trim($stdout), 'stderr' => trim($stderr)];
}

$log   = [];
$ok    = true;

// Step 1: Verify it's a git repo
$check = runCmd('git rev-parse --is-inside-work-tree', $projectRoot);
if ($check['exit_code'] !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Direktori project bukan git repository. Pastikan hosting sudah di-clone dari GitHub.',
        'output'  => $check['stderr'] ?: $check['stdout']
    ]);
    exit;
}

// Step 2: git fetch origin
$log[] = '>> git fetch origin';
$fetch = runCmd('git fetch origin', $projectRoot);
$log[] = $fetch['stdout'] ?: $fetch['stderr'];
if ($fetch['exit_code'] !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal fetch dari GitHub. Pastikan koneksi internet dan akses repository tersedia.',
        'output'  => implode("\n", $log)
    ]);
    exit;
}

// Step 3: Detect default branch (main or master)
$log[] = '>> Detecting branch...';
$branchDetect = runCmd('git symbolic-ref refs/remotes/origin/HEAD', $projectRoot);
$branch = 'main'; // fallback
if ($branchDetect['exit_code'] === 0 && preg_match('#refs/remotes/origin/(.+)$#', $branchDetect['stdout'], $m)) {
    $branch = trim($m[1]);
} else {
    // Fallback: check which branch exists on origin
    $checkMain   = runCmd('git rev-parse --verify origin/main', $projectRoot);
    $checkMaster = runCmd('git rev-parse --verify origin/master', $projectRoot);
    if ($checkMain['exit_code'] === 0) {
        $branch = 'main';
    } elseif ($checkMaster['exit_code'] === 0) {
        $branch = 'master';
    }
}
$log[] = "Branch: $branch";

// Step 4: git reset --hard origin/<branch>  (force overwrite local changes)
$log[] = ">> git reset --hard origin/$branch";
$reset = runCmd("git reset --hard origin/$branch", $projectRoot);
$log[] = $reset['stdout'] ?: $reset['stderr'];

if ($reset['exit_code'] !== 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal update. Cek log di bawah untuk detail.',
        'output'  => implode("\n", $log)
    ]);
    exit;
}

// Step 5: Get current commit hash for display
$hash = runCmd('git log -1 --format="%h %s (%ai)"', $projectRoot);
$log[] = '>> Latest commit: ' . ($hash['stdout'] ?: 'unknown');

echo json_encode([
    'success' => true,
    'message' => 'Sistem berhasil diperbarui ke versi terbaru.',
    'output'  => implode("\n", $log),
    'commit'  => $hash['stdout'] ?: ''
]);
