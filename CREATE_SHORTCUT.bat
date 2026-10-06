@echo off
REM Create Desktop Shortcut for Store Management System

setlocal enabledelayedexpansion

cd /d "%~dp0"

set "appDir=%cd%"
set "desktopDir=%userprofile%\Desktop"
set "shortcutPath=!desktopDir!\Store Management.lnk"

REM Create VBScript to generate shortcut
set "vbs=!temp!\create_shortcut.vbs"

(
    echo Set oWS = WScript.CreateObject("WScript.Shell"^)
    echo sLinkFile = "!shortcutPath!"
    echo Set oLink = oWS.CreateShortcut(sLinkFile^)
    echo oLink.TargetPath = "!appDir!\LaunchApp.vbs"
    echo oLink.WorkingDirectory = "!appDir!"
    echo oLink.Description = "Store Management System"
    echo oLink.IconLocation = "!appDir!\public\favicon.ico"
    echo oLink.Save
) > "!vbs!"

cscript.exe "!vbs!" >nul 2>&1

if exist "!vbs!" del "!vbs!"

echo.
echo [OK] Desktop shortcut created: "!shortcutPath!"
echo.
echo You can now double-click the shortcut to launch the application.
echo.
pause
