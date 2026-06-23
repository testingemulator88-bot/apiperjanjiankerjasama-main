@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
if "%API_HOST%"=="" set "API_HOST=127.0.0.1"
if "%API_PORT%"=="" set "API_PORT=8081"

if not exist "%ROOT_DIR%run-be.bat" (
  echo [ERROR] run-be.bat not found in %ROOT_DIR%
  exit /b 1
)

call "%ROOT_DIR%run-be.bat" local
exit /b %ERRORLEVEL%
