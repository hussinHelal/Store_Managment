[Setup]
AppName=Store Management System
AppVersion=1.0.0
AppVerName=Store Management System 1.0.0
AppPublisher=Store Management
AppPublisherURL=https://example.com
AppSupportURL=https://example.com
AppSupportPhone=
AppContact=Store Management Team
DefaultDirName={localappdata}\StoreManagement
DefaultGroupName=Store Management
OutputBaseFilename=StoreManagementInstaller
OutputDir=..\dist
Compression=lzma2/ultra
SolidCompression=yes
ArchitecturesAllowed=x86 x64
DisableDirPage=no
DisableProgramGroupPage=no
CreateAppDir=yes
PrivilegesRequired=lowest
WizardStyle=modern
VersionInfoVersion=1.0.0
VersionInfoCompany=Store Management
VersionInfoDescription=Offline installer for the Store Management System
VersionInfoProductName=Store Management System

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Files]
Source: "..\*"; DestDir: "{app}"; Flags: recursesubdirs createallsubdirs ignoreversion; Excludes: ".git\*;node_modules\*;tests\*;installer\*;.vscode\*;*.md;*.gitignore;*.gitattributes;composer.json;composer.lock;package.json;package-lock.json;*.sh;*.ps1;*.bat;*.log;app.conf;.env;.env.example"
Source: "..\php\*"; DestDir: "{app}\php"; Flags: recursesubdirs createallsubdirs ignoreversion
Source: "..\SETUP_DATABASE.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\START_APP.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\START_APP_GUI.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\app.conf"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\.env"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\.env.example"; DestDir: "{app}"; Flags: ignoreversion

[Dirs]
Name: "{app}\database"
Name: "{app}\storage\app"
Name: "{app}\storage\framework"
Name: "{app}\storage\framework\cache"
Name: "{app}\storage\framework\sessions"
Name: "{app}\storage\framework\views"
Name: "{app}\storage\logs"
Name: "{app}\public\uploads"

[Icons]
Name: "{group}\Store Management"; Filename: "{app}\START_APP_GUI.bat"
Name: "{group}\Uninstall Store Management"; Filename: "{uninstallexe}"
Name: "{userdesktop}\Store Management"; Filename: "{app}\START_APP_GUI.bat"; Tasks: desktopicon

[Tasks]
Name: "desktopicon"; Description: "Create a desktop icon"; GroupDescription: "Additional icons:"; Flags: unchecked

[Run]
Filename: "{app}\START_APP_GUI.bat"; Description: "Launch Store Management now"; Flags: shellexec postinstall skipifsilent

[UninstallDelete]
Type: filesandordirs; Name: "{app}\uploads"
Type: filesandordirs; Name: "{app}\storage\logs"
Type: filesandordirs; Name: "{app}\storage\framework"
Type: filesandordirs; Name: "{app}\database"
Type: filesandordirs; Name: "{app}\vendor"
Type: filesandordirs; Name: "{app}\public\build"
