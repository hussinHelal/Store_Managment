@echo off
REM Build the offline installer using Inno Setup

cd /d "%~dp0"

if not exist "..\dist" mkdir "..\dist"
where iscc.exe >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Inno Setup compiler not found in PATH.
    echo Install Inno Setup from https://jrsoftware.org and make sure iscc.exe is accessible.
    pause
    exit /b 1
)

echo Building installer...
iscc.exe "StoreManagementInstaller.iss"
if errorlevel 1 (
    echo [ERROR] Installer build failed.
    pause
    exit /b 1
)

echo [OK] Installer built successfully.
if exist "..\dist\StoreManagementInstaller.exe" (
    echo Output: ..\dist\StoreManagementInstaller.exe
) else (
    echo [WARNING] Output file not found in ..\dist.
)
pause
