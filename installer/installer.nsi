!define APP_NAME "Store Management System"
!define APP_VERSION "1.0.0"
!define APP_PUBLISHER "Local"
!define INSTALL_DIR "$PROGRAMFILES\${APP_NAME}"

;--------------------------------
; Modern UI 2
!include "MUI2.nsh"

Name "${APP_NAME} ${APP_VERSION}"
OutFile "${APP_NAME}_Installer_${APP_VERSION}.exe"
InstallDir "${INSTALL_DIR}"

; Request application privileges for Windows Vista/7/8/10
RequestExecutionLevel user

;--------------------------------
; Pages
!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

;--------------------------------
; Languages
!insertmacro MUI_LANGUAGE "English"

Section "Install"
    SetOutPath "$INSTDIR"

    ; Copy all files shipped in the installer into $INSTDIR
    File /r "${EXEDIR}\package\*.*"

    ; Create uploads and database directories if not present
    CreateDirectory "$INSTDIR\database"
    CreateDirectory "$INSTDIR\public\uploads\products"
    CreateDirectory "$INSTDIR\public\uploads\users"

    ; Create Start Menu shortcut
    CreateDirectory "$SMPROGRAMS\${APP_NAME}"
    CreateShortCut "$SMPROGRAMS\${APP_NAME}\${APP_NAME}.lnk" "$INSTDIR\LaunchApp.vbs" "" "$INSTDIR\public\favicon.ico" 0

    ; Create Desktop shortcut
    CreateShortCut "$DESKTOP\${APP_NAME}.lnk" "$INSTDIR\LaunchApp.vbs" "" "$INSTDIR\public\favicon.ico" 0

SectionEnd

Section "Uninstall"
    Delete "$SMPROGRAMS\${APP_NAME}\${APP_NAME}.lnk"
    RMDir "$SMPROGRAMS\${APP_NAME}"
    Delete "$DESKTOP\${APP_NAME}.lnk"

    RMDir /r "$INSTDIR"
SectionEnd
