@echo off
REM Store Management System - GUI Launcher (Hidden Console)
REM This script runs the app with a hidden console window

setlocal enabledelayedexpansion

cd /d "%~dp0"

REM Check if database exists, if not run setup
if not exist "database\storemanagement.sqlite" (
    echo Initializing database for first-time use...
    call SETUP_DATABASE.bat
    if errorlevel 1 exit /b 1
)

REM Create a VBScript to run PHP server hidden and open browser
set "vbs_file=%temp%\launch_app.vbs"

(
    echo Set objShell = CreateObject("WScript.Shell"^)
    echo Set objFSO = CreateObject("Scripting.FileSystemObject"^)
    echo workingDir = "%cd%"
    echo objShell.CurrentDirectory = workingDir
    echo.
    echo REM Start PHP server in hidden mode
    echo Set objProc = objShell.Exec("cmd.exe /c php artisan serve --host=127.0.0.1 --port=8000"^)
    echo.
    echo REM Wait a moment for server to start
    echo WScript.Sleep 2000
    echo.
    echo REM Open browser
    echo objShell.Run "http://127.0.0.1:8000", 1, False
)

cscript.exe "!vbs_file!" >nul 2>&1

REM Keep the VBScript running by waiting
timeout /t 1 /nobreak >nul

REM Cleanup
if exist "!vbs_file!" del "!vbs_file!"
