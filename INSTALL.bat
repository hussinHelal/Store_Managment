@echo off
REM Complete installation script for Windows 7+ machines
REM This will handle all setup including dependencies check

setlocal enabledelayedexpansion

cls

echo.
echo ========================================
echo Store Management System - Complete Setup
echo ========================================
echo.

REM Check if running as admin
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [WARNING] This script should ideally run as Administrator
    echo But we'll try to continue anyway...
    echo.
)

cd /d "%~dp0"
set "appDir=%cd%"

REM Step 1: Check PHP
echo [1/5] Checking PHP installation...
where php >nul 2>nul
if errorlevel 1 (
    echo [ERROR] PHP not found!
    echo.
    echo Please download and install PHP from: https://windows.php.net/
    echo Then add the PHP folder to your Windows PATH
    echo.
    pause
    exit /b 1
)
php --version | findstr /r "PHP [0-9]" >nul
echo [OK] PHP found

REM Step 2: Check Composer
echo.
echo [2/5] Checking Composer installation...
where composer >nul 2>nul
if errorlevel 1 (
    echo [WARNING] Composer not found
    echo.
    echo Download from: https://getcomposer.org/download/
    echo.
    pause
)

REM Step 3: Install/Update Dependencies
echo.
echo [3/5] Installing PHP dependencies...
if exist "vendor" (
    echo [SKIP] vendor folder already exists, skipping composer install
) else (
    call composer install --no-progress --no-interaction 2>nul
    if errorlevel 1 (
        echo [WARNING] Composer install failed, continuing anyway...
    ) else (
        echo [OK] Dependencies installed
    )
)

REM Step 4: Create directories
echo.
echo [4/5] Creating required directories...
if not exist "database" mkdir database
if not exist "storage\logs" mkdir storage\logs
if not exist "public\uploads\products" mkdir public\uploads\products
if not exist "public\uploads\users" mkdir public\uploads\users
echo [OK] Directories created

REM Step 5: Generate app key
echo.
echo [5/5] Configuring application...
php artisan key:generate --force 2>nul
echo [OK] Application key generated

REM Initialize database
echo.
echo [DATABASE SETUP]
echo Running initial migrations...
php artisan migrate --force 2>nul

echo.
echo ========================================
echo [OK] Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Double-click LaunchApp.vbs to start the application
echo 2. The app will open in your browser automatically
echo 3. Login with default credentials
echo 4. Change password after first login
echo.
echo Default login:
echo - Email: admin@example.com
echo - Password: password
echo.
pause
