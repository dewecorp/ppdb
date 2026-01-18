param(
    [string]$Message = "update",
    [string]$Branch = "",
    [string]$RemoteUrl = ""
)

$inside = git rev-parse --is-inside-work-tree 2>$null
if ($inside -ne "true") {
    Write-Host "Bukan repository Git. Inisialisasi dengan: git init"
    exit 1
}

if ([string]::IsNullOrWhiteSpace($Branch)) {
    $Branch = git rev-parse --abbrev-ref HEAD
} else {
    git checkout $Branch
}

$remotes = git remote
while (-not $remotes) {
    if ([string]::IsNullOrWhiteSpace($RemoteUrl)) {
        $RemoteUrl = Read-Host "Masukkan URL remote (contoh https://...)"
    }
    if (-not [string]::IsNullOrWhiteSpace($RemoteUrl)) {
        git remote add origin $RemoteUrl
        $remotes = git remote
    } else {
        Write-Host "Remote wajib diset untuk keamanan. Silakan isi."
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

$pullResult = $null
try { $pullResult = git pull --rebase } catch {}
$upstream = git rev-parse --abbrev-ref "$Branch@{upstream}" 2>$null
if ($upstream) { 
    git push 
} else { 
    git push -u origin $Branch 
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Push gagal. Periksa remote/koneksi anda."
} else {
    Write-Host ("Push berhasil ke branch {0}." -f $Branch)
}

if (-not (Test-Path "backups")) { New-Item -ItemType Directory -Path "backups" | Out-Null }
$ts = Get-Date -Format "yyyyMMdd-HHmmss"
Compress-Archive -Path (Get-ChildItem -Force) -DestinationPath ("backups/project-{0}.zip" -f $ts) -Force
