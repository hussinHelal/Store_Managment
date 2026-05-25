# Store Management Offline Installer

This folder contains an Inno Setup packaging script for the Store Management project.

## What this installer includes
- Laravel application files
- `vendor/` dependencies already installed
- compiled front-end assets under `public/build`
- `.env` configuration and database support
- portable PHP support when a runtime is placed in `php\`
- `START_APP.bat`, `START_APP_GUI.bat`, and `SETUP_DATABASE.bat`
- default superadmin accounts seeded on first install

## Requirements
1. Inno Setup installed on Windows: https://jrsoftware.org
2. A local portable PHP runtime copied into the project root under `php\`
   - PHP 8.2 is required by this application
   - For Windows 7 compatibility, use a Win7-capable PHP build if available
   - The folder should contain `php.exe` and its required extensions
3. The project should already contain built assets in `public\build` and `vendor\`

## How to build
1. Open `installer\build-installer.bat` with double-click or run it from a command prompt.
2. If `iscc.exe` is in PATH, the installer will compile.
3. The resulting EXE is created in `..\dist\StoreManagementInstaller.exe`.

## Notes
- Use the Windows uninstall entry `Store Management` to remove the application cleanly.
- The installer writes the application into `%LOCALAPPDATA%\StoreManagement` by default.
- The application uses SQLite and stores data locally in the installed folder.
- `START_APP_GUI.bat` will use the bundled PHP executable from `php\php.exe` if available.
- If you want a true offline EXE, make sure the portable PHP runtime is present before building.

## Default superadmin accounts
After first install and `SETUP_DATABASE.bat`, the database seeder will create two default superadmin accounts:
- `mohamed@mohamed.com` / `01008129710`
- `hussin@hussin.com` / `hussin98741`

## Create additional superadmins after install
After logging in with a superadmin, you can create new superadmin users either by:
- Using the web user management UI as a superadmin
- Running `php artisan make:superadmin` from the installed project folder
