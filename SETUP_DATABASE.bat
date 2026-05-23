@echo off
REM Store Management System - Database Initialization
REM Run this file once to set up the database

echo.
echo ========================================
echo Store Management - Database Setup
echo ========================================
echo.

cd /d "%~dp0"

REM Check if database directory exists
if not exist "database" (
    mkdir database
    echo [OK] Created database folder
)

REM Run Laravel migrations
echo [*] Initializing database...
php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Database initialization failed
    pause
    exit /b 1
)

REM Seed initial data (optional)
echo [*] Seeding initial data...
php artisan db:seed --force 2>nul

echo.
echo ========================================
echo [OK] Database initialized successfully!
echo ========================================
echo.
echo You can now run START_APP.bat to launch the application.
echo.
pause
