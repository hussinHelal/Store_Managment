@echo off
REM Build NSIS installer for Store Management System
REM Requirements: NSIS (makensis) installed and in PATH

setlocal enabledelayedexpansion

cd /d "%~dp0\.."
set "PROJECT_DIR=%cd%"
set "BUILD_DIR=%PROJECT_DIR%\installer_build"
set "PACKAGE_DIR=%PROJECT_DIR%\installer\package"

if exist "%BUILD_DIR%" rd /s /q "%BUILD_DIR%"
mkdir "%BUILD_DIR%"
mkdir "%PACKAGE_DIR%"

echo [1/4] Preparing package directory...

REM Copy project files (exclude node_modules, vendor can be included if needed)
robocopy "%PROJECT_DIR%" "%PACKAGE_DIR%" /MIR /XD "%PROJECT_DIR%\\node_modules" "%PROJECT_DIR%\\.git" "%PROJECT_DIR%\\installer_build" "%PROJECT_DIR%\\vendor" /XF "*.log" "*.env"  >nul

REM NOTE: Include vendor if you need dependencies shipped. If not, ensure target machine has Composer/PHP needed extensions.

REM Ensure portable PHP folder is present
if not exist "%PROJECT_DIR%\portable_php" (
    echo [WARNING] portable_php folder not found.
    echo Please download a Windows PHP binary and place it in the 'portable_php' folder.
    pause
    exit /b 1
)

echo [2/4] Copying portable PHP...
xcopy /E /I /Y "%PROJECT_DIR%\portable_php" "%PACKAGE_DIR%\portable_php" >nul

echo [3/4] Creating package zip
powershell -Command "Add-Type -A 'System.IO.Compression.FileSystem'; [IO.Compression.ZipFile]::CreateFromDirectory('%PACKAGE_DIR%','%BUILD_DIR%\package.zip')"

echo [4/4] Building installer with NSIS
if exist "%PROJECT_DIR%\installer\installer.nsi" (
    makensis -V2 "%PROJECT_DIR%\installer\installer.nsi"
    if %errorlevel% neq 0 (
        echo [ERROR] makensis failed
        pause
        exit /b 1
    ) else (
        echo [OK] Installer built successfully
    )
) else (
    echo [ERROR] installer.nsi not found
    pause
    exit /b 1
)

echo Done.
pause
