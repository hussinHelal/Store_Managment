' Store Management System - Desktop App Launcher
' This VBScript launches the PHP server and opens the app in a browser

Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Get the directory where this script is located
scriptPath = objFSO.GetParentFolderName(WScript.ScriptFullName)
workingDir = scriptPath

' Check if database exists
dbPath = workingDir & "\database\storemanagement.sqlite"
If Not objFSO.FileExists(dbPath) Then
    ' First time setup - run migrations
    MsgBox "Initializing database for first-time use..." & vbCrLf & "This may take a moment...", vbInformation, "Store Management System - Setup"
    
    Set objProc = objShell.Exec("cmd.exe /c cd /d """ & workingDir & """ && php artisan migrate --force")
    objProc.StdOut.ReadAll()
    objProc.StdErr.ReadAll()
    
    ' Create default admin user
    objShell.Exec("cmd.exe /c cd /d """ & workingDir & """ && php artisan tinker --execute ""exit;""").StdOut.ReadAll()
    
    MsgBox "Database setup complete! The app will now open in your browser.", vbInformation, "Store Management System"
End If

' Check if PHP is available
On Error Resume Next
Set phpTest = objShell.Exec("php --version")
If Err.Number <> 0 Then
    MsgBox "PHP is not installed or not in PATH." & vbCrLf & vbCrLf & _
           "Please:" & vbCrLf & _
           "1. Download PHP from https://windows.php.net/" & vbCrLf & _
           "2. Extract it to C:\php" & vbCrLf & _
           "3. Add C:\php to your system PATH" & vbCrLf & vbCrLf & _
           "Or use the portable PHP version included with this package.", vbCritical, "PHP Not Found"
    WScript.Quit 1
End If
On Error GoTo 0

' Hide the command window and start PHP server
Set objProc = objShell.Exec("cmd.exe /c cd /d """ & workingDir & """ && php artisan serve --host=127.0.0.1 --port=8000")

' Wait for server to start
WScript.Sleep 3000

' Open the app in the default browser
objShell.Run "http://127.0.0.1:8000", 1, False

' Keep running
Do
    WScript.Sleep 1000
Loop
