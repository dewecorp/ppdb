<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Script ini hanya boleh dijalankan lewat command line.');
}

require_once dirname(__DIR__) . '/config.php';

$username = $argv[1] ?? '';
$password = $argv[2] ?? '';

if ($username === '' || $password === '') {
    fwrite(STDERR, "Usage: php tools/create_admin.php <username> <password>\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Password minimal 8 karakter.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';
$foto = null;

$existingId = null;
if ($stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1')) {
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($existingId);
    $stmt->fetch();
    $stmt->close();
}

if ($existingId !== null) {
    $stmt = $mysqli->prepare('UPDATE users SET password = ?, role = ? WHERE id = ?');
    if (!$stmt) {
        fwrite(STDERR, "Gagal menyiapkan update user.\n");
        exit(1);
    }
    $stmt->bind_param('ssi', $hash, $role, $existingId);
    $ok = $stmt->execute();
    $stmt->close();
    echo $ok ? "Password admin '{$username}' berhasil direset.\n" : "Gagal mereset password admin.\n";
    exit($ok ? 0 : 1);
}

$stmt = $mysqli->prepare('INSERT INTO users (foto, username, password, role) VALUES (?, ?, ?, ?)');
if (!$stmt) {
    fwrite(STDERR, "Gagal menyiapkan pembuatan user.\n");
    exit(1);
}

$stmt->bind_param('ssss', $foto, $username, $hash, $role);
$ok = $stmt->execute();
$stmt->close();

echo $ok ? "Admin '{$username}' berhasil dibuat.\n" : "Gagal membuat admin.\n";
exit($ok ? 0 : 1);
