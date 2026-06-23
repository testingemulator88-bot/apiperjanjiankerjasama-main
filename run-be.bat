@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
set "MODE=%~1"
if "%MODE%"=="" set "MODE=local"

set "HOST=%API_HOST%"
if "%HOST%"=="" set "HOST=127.0.0.1"
set "PORT=%API_PORT%"
if "%PORT%"=="" set "PORT=8081"

if /I "%MODE%"=="local" (
  set "API_DIR=%ROOT_DIR%api\2026"
) else if /I "%MODE%"=="prod" (
  set "API_DIR=%ROOT_DIR%api"
) else (
  echo [ERROR] Unknown mode: %MODE%
  echo Usage: run-be.bat [local^|prod]
  exit /b 1
)

if not exist "%API_DIR%" (
  echo [ERROR] API directory not found: %API_DIR%
  exit /b 1
)

where php >nul 2>nul
if errorlevel 1 (
  echo [ERROR] php command not found in PATH
  exit /b 1
)

echo [API] Mode: %MODE%
echo [API] Starting at http://%HOST%:%PORT%
echo [API] Root: %API_DIR%
pushd "%API_DIR%" >nul
if exist "%API_DIR%\router.php" (
  php -S %HOST%:%PORT% router.php
) else (
  php -S %HOST%:%PORT%
)
set "EXIT_CODE=%ERRORLEVEL%"
popd >nul
exit /b %EXIT_CODE%
