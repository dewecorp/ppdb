$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$releaseDir = Join-Path $root '_release'
$stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$stageDir = Join-Path ([System.IO.Path]::GetTempPath()) ('ppdb_hosting_' + $stamp + '_' + [System.Guid]::NewGuid().ToString('N'))
$zipName = 'ppdb_hosting_latest.zip'
$zipPath = Join-Path $releaseDir $zipName

New-Item -ItemType Directory -Path $releaseDir -Force | Out-Null
New-Item -ItemType Directory -Path $stageDir | Out-Null

$topLevelItems = @(
    '.htaccess',
    'admin',
    'assets',
    'uploads',
    'backups',
    'cetak_kartu.php',
    'config.php',
    'data_pendaftar.php',
    'edit_pendaftar.php',
    'index.php',
    'simpan_pendaftaran.php'
)

foreach ($item in $topLevelItems) {
    $src = Join-Path $root $item
    if (!(Test-Path $src)) {
        continue
    }

    $dst = Join-Path $stageDir $item
    if (Test-Path $src -PathType Container) {
        New-Item -ItemType Directory -Path $dst -Force | Out-Null
        Get-ChildItem -LiteralPath $src -Recurse -Force | Where-Object {
            $_.FullName -notmatch '\\(\.git|\.qoder|\.agents|\.codex|wp-admin|wp-content|wp-includes)(\\|$)' -and
            $_.Name -notmatch '\.(zip|sql|log|bak|old)$'
        } | ForEach-Object {
            $relative = $_.FullName.Substring($src.Length).TrimStart('\')
            $target = Join-Path $dst $relative
            if ($_.PSIsContainer) {
                New-Item -ItemType Directory -Path $target -Force | Out-Null
            } else {
                New-Item -ItemType Directory -Path (Split-Path $target -Parent) -Force | Out-Null
                Copy-Item -LiteralPath $_.FullName -Destination $target -Force
            }
        }
    } else {
        Copy-Item -LiteralPath $src -Destination $dst -Force
    }
}

Get-ChildItem -LiteralPath $releaseDir -Filter 'ppdb_hosting_*.zip' -File -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -ne $zipName } |
    Remove-Item -Force

try {
    Compress-Archive -Path (Join-Path $stageDir '*') -DestinationPath $zipPath -Force
} finally {
    if (Test-Path $stageDir) {
        Remove-Item -LiteralPath $stageDir -Recurse -Force
    }
}

Write-Host "Paket hosting dibuat: $zipPath"
