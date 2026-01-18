@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

set MSG=%*
if "%MSG%"=="" (
    set /p MSG=Commit message: 
)

for /f "delims=" %%i in ('git rev-parse --is-inside-work-tree 2^>nul') do set INSIDE=%%i
if not "%INSIDE%"=="true" (
    echo Bukan repository Git. Inisialisasi dengan: git init
    pause
    exit /b 1
)

set HASREMOTE=
:askremote
git remote > "%TEMP%\\remotes.tmp" 2>nul
for /f %%x in ('find /c /v "" ^< "%TEMP%\\remotes.tmp"') do set REMCOUNT=%%x
del "%TEMP%\\remotes.tmp" 2>nul
if "%REMCOUNT%"=="0" (
    echo Remote belum dikonfigurasi. Diperlukan untuk push.
    set /p REMOTEURL=Masukkan URL remote (contoh https://... ): 
    if "%REMOTEURL%"=="" goto askremote
    git remote add origin "%REMOTEURL%"
)

for /f "delims=" %%b in ('git rev-parse --abbrev-ref HEAD') do set BRANCH=%%b

git fetch --all 1>nul 2>nul
git status --porcelain > "%TEMP%\\gitstatus.tmp"
for /f %%x in ('findstr /r /c:".*" "%TEMP%\\gitstatus.tmp" ^| find /c /v ""') do set CHANGES=%%x
del "%TEMP%\\gitstatus.tmp" 2>nul

if "%CHANGES%"=="0" (
    echo Tidak ada perubahan lokal untuk di-commit.
) else (
    git add -A
    git commit -m "%MSG%"
)

for /f "delims=" %%u in ('git rev-parse --abbrev-ref "%BRANCH%@{upstream}" 2^>nul') do set UPSTREAM=%%u
git pull --rebase
if defined UPSTREAM (
    git push
) else (
    git push -u origin %BRANCH%
)

if errorlevel 1 (
    echo Push gagal. Periksa konfigurasi remote atau koneksi.
) else (
    echo Push berhasil ke %BRANCH%.
)

set CONFIRMBACKUP=Y
echo Membuat backup ZIP...
echo Membuat backup ZIP...
if not exist "%~dp0backups" mkdir "%~dp0backups"
for /f "delims=" %%t in ('powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Get-Date -Format yyyyMMdd-HHmmss"') do set TS=%%t
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command ^
"$items = Get-ChildItem -Force -Path '%~dp0' | Where-Object { $_.Name -ne 'backups' -and $_.Name -ne '.git' } | Select-Object -Expand FullName; ^
Compress-Archive -Path $items -DestinationPath ('%~dp0backups\\project-%TS%.zip') -Force"

echo Selesai. Snapshot ZIP disimpan di folder backups.
