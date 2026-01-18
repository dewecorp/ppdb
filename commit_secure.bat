@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"
set MSG=%*
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0commit_secure.ps1" -Message "%MSG%"
