@echo off
REM Store Management System - Desktop Application Launcher
REM This script starts the application server and opens it in the browser

setlocal enabledelayedexpansion

cd /d "%~dp0"

echo.
echo ========================================
echo Store Management System
echo ========================================
echo.

REM Check if PHP is installed
where php >nul 2>nul
if errorlevel 1 (
    echo [ERROR] PHP is not installed or not in PATH
    echo.
    echo Please install PHP and add it to your system PATH
    echo Or use the bundled PHP from the PORTABLE folder
    echo.
    pause
    exit /b 1
)

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
php artisan serve --host=127.0.0.1 --port=8000

pause
