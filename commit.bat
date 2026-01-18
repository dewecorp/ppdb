@echo off
cd /d "%~dp0"

set MSG=%1

if "%MSG%"=="" (
    set /p MSG=Commit message: 
)

git status
git add -A

git commit -m "%MSG%"

git push

pause

