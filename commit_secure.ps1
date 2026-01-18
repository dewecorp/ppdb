param(
    [string]$Message = "",
    [string]$Branch = "",
    [string]$RemoteUrl = ""
)
Set-Location -Path $PSScriptRoot
$inside = git rev-parse --is-inside-work-tree 2>$null
if ($inside -ne "true") {
    Write-Host "Bukan repository Git. Inisialisasi dengan: git init"
    Read-Host "Tekan Enter untuk menutup"
    exit 1
}
if ([string]::IsNullOrWhiteSpace($Message)) {
    $Message = Read-Host "Pesan commit"
}
if ([string]::IsNullOrWhiteSpace($Branch)) {
    $Branch = git rev-parse --abbrev-ref HEAD
} else {
    git checkout $Branch
}
$uname = git config --get user.name
if ([string]::IsNullOrWhiteSpace($uname)) {
    $uname = Read-Host "Masukkan Git user.name"
    if (-not [string]::IsNullOrWhiteSpace($uname)) { git config user.name $uname }
}
$uemail = git config --get user.email
if ([string]::IsNullOrWhiteSpace($uemail)) {
    $uemail = Read-Host "Masukkan Git user.email"
    if (-not [string]::IsNullOrWhiteSpace($uemail)) { git config user.email $uemail }
}
$remotes = git remote
while (-not $remotes) {
    if ([string]::IsNullOrWhiteSpace($RemoteUrl)) {
        $RemoteUrl = Read-Host "https://github.com/dewecorp/ppdb.git"
    }
    if (-not [string]::IsNullOrWhiteSpace($RemoteUrl)) {
        git remote add origin $RemoteUrl
        $remotes = git remote
    } else {
        Write-Host "Remote wajib diset untuk push."
    }
}
git fetch --all | Out-Null
$status = git status --porcelain
if ($status) {
    git add -A
    git commit -m $Message
} else {
    Write-Host "Tidak ada perubahan lokal untuk di-commit."
}
try { git pull --rebase } catch {}
$upstream = git rev-parse --abbrev-ref "$Branch@{upstream}" 2>$null
if ($upstream) {
    git push
} else {
    git push -u origin $Branch
}
Write-Host "Membuat backup ZIP..."
if (-not (Test-Path "backups")) { New-Item -ItemType Directory -Path "backups" | Out-Null }
$ts = Get-Date -Format "yyyyMMdd-HHmmss"
$items = Get-ChildItem -Force -Path $PSScriptRoot | Where-Object { $_.Name -ne ".git" -and $_.Name -ne "backups" } | Select-Object -ExpandProperty FullName
Compress-Archive -Path $items -DestinationPath ("backups/project-{0}.zip" -f $ts) -Force
Write-Host "Selesai. Backup: backups\project-$ts.zip"
Read-Host "Tekan Enter untuk menutup"
