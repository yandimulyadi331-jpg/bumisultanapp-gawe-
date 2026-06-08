@echo off
REM =================================================================
REM DEPLOYMENT HELPER - MENU AUTOMATION
REM =================================================================
REM Guna: Menu untuk memilih deployment options yang berbeda
REM =================================================================

setlocal enabledelayedexpansion

:menu
cls
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║      BUMI SULTAN - DEPLOYMENT AUTOMATION MENU               ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo LOKAL DEPLOYMENT:
echo   1. Full Deployment (recommended)
echo   2. Build Frontend Only (Vite)
echo   3. Update Dependencies Only (Composer + NPM)
echo   4. Run Migrations Only
echo   5. Clear Cache + Optimize
echo.
echo DEVELOPMENT:
echo   6. Start Dev Server
echo   7. Watch Frontend Changes (npm run dev)
echo   8. Database Seed
echo.
echo DATABASE:
echo   9.  Migrate + Rollback
echo   10. Tinker (Interactive Shell)
echo.
echo UTILITIES:
echo   11. View Error Logs
echo   12. Check Git Status
echo   13. Show Deployment Guide
echo.
echo OTHER:
echo   0. Exit
echo.
set /p choice="Pilih opsi (0-13): "

if "%choice%"=="0" goto end
if "%choice%"=="1" goto full_deploy
if "%choice%"=="2" goto build_only
if "%choice%"=="3" goto deps_only
if "%choice%"=="4" goto migrate_only
if "%choice%"=="5" goto cache_clear
if "%choice%"=="6" goto dev_server
if "%choice%"=="7" goto watch_dev
if "%choice%"=="8" goto seed_db
if "%choice%"=="9" goto db_tools
if "%choice%"=="10" goto tinker
if "%choice%"=="11" goto view_logs
if "%choice%"=="12" goto git_status
if "%choice%"=="13" goto show_guide

goto invalid

:full_deploy
echo.
echo Menjalankan FULL DEPLOYMENT...
echo.
call deploy-local.bat
goto end

:build_only
echo.
echo Building Frontend with Vite...
npm run build
echo.
echo ✅ Frontend build complete!
echo.
pause
goto menu

:deps_only
echo.
echo Updating dependencies...
composer update
npm install
echo.
echo ✅ Dependencies updated!
echo.
pause
goto menu

:migrate_only
echo.
php artisan migrate:status
echo.
set /p confirm="Run migrations? (Y/N): "
if /i "%confirm%"=="Y" (
    php artisan migrate
    echo.
    echo ✅ Migrations completed!
) else (
    echo Skipped.
)
echo.
pause
goto menu

:cache_clear
echo.
echo Clearing caches...
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan optimize
echo.
echo ✅ Caches cleared and optimized!
echo.
pause
goto menu

:dev_server
echo.
echo 🚀 Starting Laravel Development Server...
echo.
echo Server akan berjalan di: http://localhost:8000
echo.
echo Press CTRL+C untuk stop server
echo.
php artisan serve
goto menu

:watch_dev
echo.
echo 👀 Watching for frontend changes...
echo.
echo Tekan CTRL+C untuk stop
echo.
npm run dev
goto menu

:seed_db
echo.
set /p confirm="Run database seeding? (Y/N): "
if /i "%confirm%"=="Y" (
    php artisan db:seed
    echo.
    echo ✅ Database seeded!
) else (
    echo Skipped.
)
echo.
pause
goto menu

:db_tools
echo.
echo DATABASE TOOLS:
echo.
echo 1. Show Migration Status
echo 2. Run Rollback
echo 3. Migrate Fresh (WARNING: DATA LOSS!)
echo 4. Back to menu
echo.
set /p db_choice="Pilih (1-4): "

if "%db_choice%"=="1" (
    php artisan migrate:status
) else if "%db_choice%"=="2" (
    echo.
    echo Jalankan rollback? (Y/N)
    set /p confirm=
    if /i "!confirm!"=="Y" (
        php artisan migrate:rollback
    )
) else if "%db_choice%"=="3" (
    echo.
    echo ⚠️  WARNING: This will delete all data!
    echo Lanjutkan? (Y/N)
    set /p confirm=
    if /i "!confirm!"=="Y" (
        php artisan migrate:fresh
    )
)
echo.
pause
goto menu

:tinker
echo.
echo 📝 Entering Tinker (Interactive Shell)...
echo.
echo Type: exit (untuk keluar)
echo.
php artisan tinker
echo.
pause
goto menu

:view_logs
echo.
echo 📋 Last 50 lines of error log:
echo.
type storage\logs\laravel.log | findstr /r ".*" | tail -50
echo.
echo.
echo Ketik 'Q' untuk kembali, atau tekan Enter...
pause
goto menu

:git_status
echo.
git status
echo.
echo Ketik 'Q' untuk kembali, atau tekan Enter...
pause
goto menu

:show_guide
echo.
echo Membuka DEPLOYMENT_GUIDE.md...
echo.
if exist DEPLOYMENT_GUIDE.md (
    start notepad DEPLOYMENT_GUIDE.md
) else (
    echo File tidak ditemukan: DEPLOYMENT_GUIDE.md
)
echo.
pause
goto menu

:invalid
echo.
echo ❌ Invalid option. Coba lagi...
echo.
pause
goto menu

:end
echo.
echo ✅ Goodbye!
echo.
exit /b 0
