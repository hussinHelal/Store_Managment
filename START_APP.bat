@echo off
REM Store Management System - Desktop Application Launcher
REM This script starts the application server and opens it in the browser

setlocal enabledelayedexpansion

cd /d "%~dp0"

set "PHP_EXEC="
if exist "%~dp0php\php.exe" (
    set "PHP_EXEC=%~dp0php\php.exe"
) else (
    for /f "usebackq delims=" %%P in (`where php 2^>nul`) do if not defined PHP_EXEC set "PHP_EXEC=%%~fP"
)

if "%PHP_EXEC%"=="" (
    echo [ERROR] PHP executable not found.
    echo.
    echo Place a portable PHP runtime in the "php" folder or add php.exe to PATH.
    echo.
    pause
    exit /b 1
)

echo [*] Using PHP: %PHP_EXEC%

echo.
echo ========================================
echo Store Management System
echo ========================================
echo.

REM Check if database exists
if not exist "database\storemanagement.sqlite" (
    echo [WARNING] Database not found!
    echo.
    echo Run SETUP_DATABASE.bat first to initialize the database.
    echo.
    pause
    exit /b 1
)

echo [*] Starting application server...
echo [*] Access the app at: http://127.0.0.1:8000
echo.
echo Press Ctrl+C to stop the server
echo.

REM Start PHP built-in server
"%PHP_EXEC%" artisan serve --host=127.0.0.1 --port=8000

pause
