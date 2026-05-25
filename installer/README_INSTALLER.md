# Building a Windows Installer (NSIS) for Store Management

This folder contains an NSIS-based installer and utility scripts to create a Windows EXE that packages the Laravel app together with a portable PHP binary.

## Overview

- `installer.nsi` - NSIS script that creates the EXE installer.
- `build_installer.bat` - Windows batch script that prepares a package and runs `makensis`.
- `build_installer.sh` - Linux/macOS shell script for building (cross-building) if `makensis` is available.
- `package/` - temporary packaging directory created during build.

## Requirements (for building)

- NSIS (makensis) installed and in PATH
  - Windows: https://nsis.sourceforge.io/Download
  - Linux: `makensis` from package manager (e.g., `apt install nsis`)
- A Windows Portable PHP distribution placed in `portable_php/` at the repository root.
  - Example: download a thread-safe PHP zip from https://windows.php.net/download
  - Extract the contents of the PHP zip into `portable_php/` so `php.exe` is at `portable_php\php.exe`
- `zip` (or PowerShell on Windows) for packaging

## How to prepare `portable_php`

1. Download a suitable PHP build (7.4 or 8.x that supports your Laravel requirements).
2. Extract into `portable_php` at the repository root, e.g. `C:\path\to\storeManagement\portable_php\php.exe`.
3. Ensure required PHP extensions are present (pdo_sqlite, openssl, mbstring, tokenizer, fileinfo, bcmath, etc.).

## Build on Windows

1. Open a Command Prompt as Administrator.
2. Ensure `makensis.exe` is in your PATH.
3. Run:

```bat
cd C:\path\to\storeManagement\installer
build_installer.bat
```

4. After successful run, `installer\${APP_NAME}_Installer_${APP_VERSION}.exe` will be generated in the current directory.

## Build on Linux/macOS (cross-build)

1. Install `makensis` (NSIS).
2. Ensure `zip` and `rsync` are available.
3. Run:

```bash
cd /path/to/storeManagement/installer
./build_installer.sh
```

4. If `makensis` is installed, this will produce an EXE.

## Packaging Notes

- The installer bundles the `portable_php` folder. The installed app will use the included PHP binary via `LaunchApp.vbs` or `START_APP.bat` which call `php` from the project folder.
- We intentionally exclude `vendor` from the package by default to save space. If you prefer including Composer dependencies, adjust the build script to copy `vendor` into `installer/package/`.

## Post-install

- After installation, the user can run the desktop shortcut created by the installer to start the app. The included `LaunchApp.vbs` starts the local server and opens the default browser.

## License

Bundle only PHP binary you are allowed to redistribute. Respect third-party licenses when packaging binaries.
