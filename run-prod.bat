@echo off
setlocal EnableExtensions

set "ROOT_DIR=%~dp0"
if "%API_HOST%"=="" set "API_HOST=0.0.0.0"
if "%API_PORT%"=="" set "API_PORT=8081"
call "%ROOT_DIR%run-be.bat" prod
exit /b %ERRORLEVEL%
