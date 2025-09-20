@echo off
REM Zyra Video Conferencing - Development Script for Windows
REM This script helps with development and testing

echo 🎥 Zyra Video Conferencing - Development Setup
echo ==============================================

REM Check if we're in the right directory
if not exist "index.php" (
    echo ❌ Error: Please run this script from the Zyra project root directory
    pause
    exit /b 1
)

REM Create necessary directories
echo 📁 Creating necessary directories...
if not exist "logs" mkdir logs
if not exist "assets\css" mkdir assets\css
if not exist "assets\js" mkdir assets\js
if not exist "api" mkdir api

REM Check PHP version
echo 🐘 Checking PHP version...
php -v
if %errorlevel% neq 0 (
    echo ❌ Error: PHP is not installed or not in PATH
    echo Please install PHP or add it to your system PATH
    pause
    exit /b 1
)

REM Check if XAMPP is running
echo 🌐 Checking web server...
curl -s http://localhost/Zyra/ >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Web server is running
    echo 🔗 Application URL: http://localhost/Zyra/
) else (
    echo ⚠️  Web server not detected. Please start XAMPP
    echo    Then access: http://localhost/Zyra/
)

REM Start Tailwind CSS watcher if available
echo 🎨 Checking for Tailwind CSS...
where npx >nul 2>&1
if %errorlevel% equ 0 (
    echo Starting Tailwind CSS watcher in background...
    start /b npx tailwindcss -i assets/css/style.css -o assets/css/output.css --watch
    echo ✅ Tailwind CSS watcher started
) else (
    echo ⚠️  npx not found. Install Node.js to use Tailwind CSS watcher
)

echo.
echo 🚀 Development setup complete!
echo.
echo Next steps:
echo 1. Open your browser and go to http://localhost/Zyra/
echo 2. Test creating and joining meetings
echo 3. Check browser console for any errors
echo 4. Modify files as needed - changes will be reflected immediately
echo.
echo Press any key to continue...
pause >nul
