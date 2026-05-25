#!/usr/bin/env bash
set -euo pipefail

# Build script for NSIS installer (Linux/macOS environment for cross-building)
# Requires: zip, makensis (nsis)

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BUILD_DIR="$PROJECT_DIR/installer_build"
PACKAGE_DIR="$PROJECT_DIR/installer/package"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR"

echo "[1/4] Preparing package directory..."
rsync -a --exclude 'node_modules' --exclude '.git' --exclude 'installer_build' --exclude 'vendor' --exclude '*.log' --exclude '.env' "$PROJECT_DIR/" "$PACKAGE_DIR/"

if [ ! -d "$PROJECT_DIR/portable_php" ]; then
  echo "[WARNING] portable_php folder not found. Place a Windows PHP build in 'portable_php'"
  exit 1
fi

echo "[2/4] Copying portable PHP..."
rsync -a "$PROJECT_DIR/portable_php/" "$PACKAGE_DIR/portable_php/"

echo "[3/4] Creating package zip..."
( cd "$PACKAGE_DIR" && zip -r "$BUILD_DIR/package.zip" . )

echo "[4/4] Building installer with makensis..."
if ! command -v makensis >/dev/null 2>&1; then
  echo "makensis not found. Install NSIS (makensis) to build the installer."
  exit 1
fi

makensis -V2 "$PROJECT_DIR/installer/installer.nsi"

echo "Done. Installer in current directory if build succeeded."
