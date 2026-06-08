@echo off
REM =================================================================
REM DEPLOYMENT SCRIPT UNTUK LOKAL - BUMI SULTAN APP
REM =================================================================
REM Guna: Otomatisasi deployment perubahan kode di komputer lokal
REM =================================================================

setlocal enabledelayedexpansion
set LOGFILE=deployment.log
set TIMESTAMP=%date% %time%

echo.
echo ========================================
echo  BUMI SULTAN - LOCAL DEPLOYMENT SCRIPT
echo  %TIMESTAMP%
echo ========================================
echo.

REM Fungsi untuk log
call :log "=== MEMULAI DEPLOYMENT LOKAL ==="

REM 1. STOP SERVER JIKA BERJALAN
call :log "1. Menghentikan server Laravel..."
echo Tekan CTRL+C jika ada server Laravel yang berjalan, atau Enter untuk lanjut...
pause

REM 2. PULL/UPDATE KODE (opsional, jika dari Git)
call :log "2. Mengecek status Git..."
git status >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :log "   WARNING: Git tidak tersedia atau bukan repository"
) else (
    echo. & echo Status Git OK
    call :log "   Git OK"
)

REM 3. INSTALL/UPDATE COMPOSER DEPENDENCIES
call :log "3. Update Composer dependencies..."
echo Updating Composer...
composer update >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :log "   ERROR: Composer update gagal!"
    pause
    exit /b 1
) else (
    call :log "   Composer OK"
    echo Composer updated successfully
)

REM 4. INSTALL/UPDATE NPM DEPENDENCIES
call :log "4. Update NPM dependencies..."
echo Updating NPM...
npm install >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :log "   ERROR: NPM install gagal!"
    pause
    exit /b 1
) else (
    call :log "   NPM OK"
    echo NPM updated successfully
)

REM 5. BUILD FRONTEND (Vite)
call :log "5. Building Frontend dengan Vite..."
echo Building Vite assets...
npm run build >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :log "   ERROR: Vite build gagal!"
    pause
    exit /b 1
) else (
    call :log "   Vite Build OK"
    echo Vite build completed
)

REM 6. CLEAR CACHES
call :log "6. Clearing Laravel caches..."
echo Clearing caches...
php artisan cache:clear >> %LOGFILE% 2>&1
php artisan config:cache >> %LOGFILE% 2>&1
php artisan route:cache >> %LOGFILE% 2>&1
call :log "   Caches cleared"

REM 7. RUN DATABASE MIGRATIONS (jika ada perubahan database)
call :log "7. Checking for pending migrations..."
php artisan migrate:status >> %LOGFILE% 2>&1
echo.
echo Apakah ada migrasi baru yang perlu dijalankan? (Y/N)
set /p MIGRATE=
if /i "%MIGRATE%"=="Y" (
    call :log "   Running migrations..."
    php artisan migrate >> %LOGFILE% 2>&1
    if errorlevel 1 (
        call :log "   WARNING: Migration mungkin gagal, cek log!"
    ) else (
        call :log "   Migrations OK"
    )
) else (
    call :log "   Skipped migrations"
)

REM 8. SEED DATABASE (opsional)
call :log "8. Database seeding (opsional)..."
echo Jalankan database seeding? (Y/N)
set /p SEED=
if /i "%SEED%"=="Y" (
    call :log "   Running seeders..."
    php artisan db:seed >> %LOGFILE% 2>&1
    call :log "   Seeding OK"
)

REM 9. OPTIMIZE
call :log "9. Optimizing application..."
php artisan optimize >> %LOGFILE% 2>&1
call :log "   Optimization OK"

REM 10. SUMMARY
echo.
echo ========================================
echo  DEPLOYMENT SELESAI!
echo ========================================
echo.
echo Langkah berikutnya:
echo 1. Jalankan: php artisan serve
echo 2. Buka: http://localhost:8000
echo 3. Cek perubahan di browser
echo.
echo Log file: %LOGFILE%
echo.
pause
exit /b 0

:log
echo [%date% %time%] %~1 >> %LOGFILE%
echo %~1
goto :eof
