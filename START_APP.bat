@echo off
REM Store Management System - Desktop Application Launcher
REM This script starts the application server and opens it in the browser

setlocal enabledelayedexpansion

cd /d "%~dp0"

<<<<<<< HEAD
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

=======
>>>>>>> 0e2ecca182843b2481ba3dd9aad86f25ce5eb5ad
echo.
echo ========================================
echo Store Management System
echo ========================================
echo.

<<<<<<< HEAD
=======
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

>>>>>>> 0e2ecca182843b2481ba3dd9aad86f25ce5eb5ad
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
<<<<<<< HEAD
"%PHP_EXEC%" artisan serve --host=127.0.0.1 --port=8000
=======
php artisan serve --host=127.0.0.1 --port=8000
>>>>>>> 0e2ecca182843b2481ba3dd9aad86f25ce5eb5ad

pause
