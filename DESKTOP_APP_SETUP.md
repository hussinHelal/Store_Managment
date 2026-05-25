# Store Management System - Desktop Application Setup Guide

## 🖥️ System Requirements

- **Windows 7** or later (Windows 10, 11 supported)
- **PHP 7.4+** (included in portable version, or install separately)
- **1GB RAM** minimum
- **200MB free disk space** (including database and uploads)
- No internet connection required (works completely offline)

## 📦 Installation & Setup

### Option 1: Automatic Setup (Recommended)

1. **Download & Extract**
   - Extract the `storeManagement` folder to a location like `C:\StoreManagement`

2. **Install PHP** (if not already installed)
   - Download from: https://windows.php.net/
   - Extract to: `C:\php`
   - Add `C:\php` to Windows PATH (see instructions below)

3. **Initialize Database**
   - Double-click `SETUP_DATABASE.bat`
   - Wait for it to complete (creates the SQLite database)

4. **Launch Application**
   - Double-click `LaunchApp.vbs`
   - The app will open in your default browser automatically
   - OR use `START_APP.bat` for visible console

5. **Create Desktop Shortcut** (Optional)
   - Double-click `CREATE_SHORTCUT.bat`
   - A shortcut will be created on your Desktop for easy access

### Option 2: Manual Setup

```bash
# In Command Prompt, navigate to the app directory
cd C:\StoreManagement

# Install dependencies
composer install

# Initialize database
php artisan migrate --force

# Start the app
php artisan serve --host=127.0.0.1 --port=8000

# Then open browser to: http://127.0.0.1:8000
```

## 🚀 Running the Application

### Method 1: VBScript Launcher (Recommended)
- **Double-click**: `LaunchApp.vbs`
- Starts server in background and opens browser
- Most user-friendly approach

### Method 2: Batch File
- **Double-click**: `START_APP.bat`
- Shows console window
- Useful for troubleshooting

### Method 3: Command Line
```bash
cd C:\StoreManagement
php artisan serve --host=127.0.0.1 --port=8000
# Then open: http://127.0.0.1:8000
```

## 📋 File Descriptions

| File | Purpose |
|------|---------|
| `LaunchApp.vbs` | **Main launcher** - Double-click to start app |
| `START_APP.bat` | Start app with visible console window |
| `SETUP_DATABASE.bat` | Initialize SQLite database (run once) |
| `CREATE_SHORTCUT.bat` | Create desktop shortcut |
| `app.conf` | Application configuration |
| `.env` | Database and app settings (SQLite configured) |
| `database/` | SQLite database folder (created during setup) |

## 🔑 Default Login Credentials

After first setup, use these to login:
- **Email**: admin@example.com
- **Password**: password

Change these immediately after first login!

## 🌐 Access the App

Once running, the app is available at:
```
http://127.0.0.1:8000
```

Or use the desktop shortcut if created.

## 📁 Adding PHP to Windows PATH

**Windows 7:**
1. Right-click "Computer" → Properties
2. Click "Advanced system settings"
3. Click "Environment Variables"
4. Under "System variables", click "New"
5. Variable name: `PATH`
6. Variable value: `C:\php`
7. Click OK multiple times

**Windows 10/11:**
1. Search for "Environment Variables"
2. Click "Edit the system environment variables"
3. Click "Environment Variables" button
4. Edit "Path" in System variables
5. Click "New" and add: `C:\php`
6. Click OK

## 🔧 Troubleshooting

### PHP not found error
```
Solution: 
1. Install PHP from https://windows.php.net/
2. Add it to Windows PATH
3. Restart your computer
```

### Port 8000 already in use
```
Solution:
1. Edit START_APP.bat or LaunchApp.vbs
2. Change 8000 to another port (e.g., 8001, 8002)
3. Make sure to access http://127.0.0.1:NEW_PORT
```

### Database errors
```
Solution:
1. Delete the database/storemanagement.sqlite file
2. Run SETUP_DATABASE.bat again
3. This will recreate the database with fresh data
```

### Uploads folder permission error
```
Solution:
1. Right-click public/uploads folder
2. Properties → Security → Edit
3. Select your user → Full Control → Apply
```

### Cannot access uploads/images
```
Make sure these folders exist and are writable:
- public/uploads/products/
- public/uploads/users/

If missing, create them manually or run:
php artisan storage:link
```

## 💾 Database Information

- **Type**: SQLite (file-based)
- **Location**: `database/storemanagement.sqlite`
- **Backup**: Copy this file to backup your data
- **No server needed**: Works completely offline

## 📊 Features Available

✅ Product Management with Images
✅ Invoice Management & Printing
✅ Customer Management
✅ Installment Tracking
✅ Backup & Export (ZIP with CSV files)
✅ User Roles & Permissions
✅ Category Management
✅ Maintenance Tracking
✅ Arabic Language Support
✅ Offline Operation

## 🔒 Security Notes

1. Change default credentials after first login
2. Database file is stored locally in `database/` folder
3. All data stays on your computer - no cloud sync
4. Regular backups recommended (use the backup feature)
5. Keep Windows 7 updated for security patches

## 📱 First Time Login

1. After setup, the app opens automatically at `http://127.0.0.1:8000`
2. You'll see a login page
3. Use credentials provided in setup
4. Dashboard appears after successful login
5. Start managing your products and invoices!

## 🆘 Getting Help

If you encounter issues:

1. **Check the console** - Note any error messages
2. **Check PHP version** - `php --version` in Command Prompt
3. **Check port** - Make sure port 8000 is not in use
4. **Check database** - Ensure database folder has write permissions
5. **Check .env file** - Make sure it's configured for SQLite

## 📝 Notes

- Application requires PHP to be running
- Close the PHP server when you're done using the app
- You can minimize the console window (if using START_APP.bat)
- The app works best in Chrome, Firefox, or Edge
- Windows 7 support confirmed, but Windows 10+ recommended

---

**Version**: 1.0.0
**Last Updated**: May 2026
