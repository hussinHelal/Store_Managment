@echo off
REM Store Management System - Database Initialization
REM Run this file once to set up the database

setlocal enabledelayedexpansion

echo.
echo ========================================
echo Store Management - Database Setup
echo ========================================
echo.

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

if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env"
        echo [OK] Created .env from .env.example
    ) else (
        echo [ERROR] .env and .env.example are missing.
        pause
        exit /b 1
    )
)

REM Check if database directory exists
if not exist "database" (
    mkdir database
    echo [OK] Created database folder
)

REM Run Laravel migrations
echo [*] Initializing database...
"%PHP_EXEC%" artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Database initialization failed
    pause
    exit /b 1
)

REM Seed initial data (optional)
echo [*] Seeding initial data...
"%PHP_EXEC%" artisan db:seed --force 2>nul

echo.
echo ========================================
echo [OK] Database initialized successfully!
echo ========================================
echo.
echo You can now run START_APP.bat to launch the application.
echo.
pause
