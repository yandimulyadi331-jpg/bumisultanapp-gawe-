@echo off
REM =================================================================
REM FULL DEPLOYMENT AUTOMATION - LOKAL KE HOSTING
REM =================================================================
REM Mengotomatisasi: Deploy Lokal → Git Commit → Push → Deploy Hosting
REM =================================================================

setlocal enabledelayedexpansion
set LOGFILE=full-deployment_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%.log
set TIMESTAMP=%date% %time%

cls
echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║   FULL AUTOMATION - LOKAL KE HOSTING                         ║
echo ║   %TIMESTAMP%
echo ╚════════════════════════════════════════════════════════════════╝
echo.

call :log "=== STARTING FULL AUTOMATION DEPLOYMENT ==="

REM ===== STEP 1: LOKAL DEPLOYMENT =====
call :log "STEP 1: Deployment Lokal..."
echo.
echo [STEP 1/4] Deployment Lokal...
echo.

REM Check PHP
php -v >nul 2>&1
if errorlevel 1 (
    call :log "ERROR: PHP tidak ditemukan!"
    echo ❌ ERROR: PHP tidak ditemukan
    pause
    exit /b 1
)

echo ✓ PHP ditemukan

REM Update Composer
call :log "Updating Composer..."
echo Updating Composer...
composer update >>%LOGFILE% 2>&1
if errorlevel 1 (
    call :log "WARNING: Composer update failed"
)

REM Update NPM
call :log "Updating NPM..."
echo Updating NPM...
npm install >>%LOGFILE% 2>&1
if errorlevel 1 (
    call :log "WARNING: NPM install failed"
)

REM Build Vite
call :log "Building Vite..."
echo Building Frontend...
npm run build >>%LOGFILE% 2>&1
if errorlevel 1 (
    call :log "ERROR: Vite build failed"
    echo ❌ Vite build failed!
    pause
    exit /b 1
)

REM Clear Caches
call :log "Clearing caches..."
php artisan cache:clear >>%LOGFILE% 2>&1
php artisan config:cache >>%LOGFILE% 2>&1
php artisan route:cache >>%LOGFILE% 2>&1

echo ✅ Lokal deployment OK

REM ===== STEP 2: GIT COMMIT =====
call :log "STEP 2: Git Commit..."
echo.
echo [STEP 2/4] Git Commit...
echo.

git status >>%LOGFILE% 2>&1
if errorlevel 1 (
    call :log "WARNING: Git tidak tersedia atau bukan repository"
    echo ⚠️  Git tidak tersedia
    set SKIP_GIT=1
) else (
    set /p COMMIT_MSG="📝 Masukkan commit message (atau Enter untuk default): "
    if "!COMMIT_MSG!"=="" (
        set COMMIT_MSG=Update: Automatic deployment %date% %time%
    )
    
    call :log "Adding files to git..."
    git add . >>%LOGFILE% 2>&1
    
    call :log "Committing: !COMMIT_MSG!"
    git commit -m "!COMMIT_MSG!" >>%LOGFILE% 2>&1
    
    if errorlevel 1 (
        call :log "WARNING: Git commit failed or no changes to commit"
        echo ⚠️  Tidak ada perubahan atau commit gagal
    ) else (
        echo ✅ Git commit OK
    )
)

REM ===== STEP 3: GIT PUSH =====
call :log "STEP 3: Git Push..."
echo.
echo [STEP 3/4] Git Push ke Repository...
echo.

if "!SKIP_GIT!"=="1" (
    echo ⚠️  Skipping Git push (Git not available)
    call :log "Skipped git push"
) else (
    call :log "Pushing to origin main..."
    git push origin main >>%LOGFILE% 2>&1
    
    if errorlevel 1 (
        call :log "WARNING: Git push failed"
        echo ⚠️  Git push failed
        set /p CONTINUE="Lanjut ke hosting deployment? (Y/N): "
        if /i "!CONTINUE!"=="N" (
            exit /b 1
        )
    ) else (
        echo ✅ Git push OK
    )
)

REM ===== STEP 4: HOSTING DEPLOYMENT (via SSH) =====
call :log "STEP 4: Hosting Deployment (SSH)..."
echo.
echo [STEP 4/4] Hosting Deployment via SSH...
echo.

set /p DEPLOY_HOSTING="Deploy ke hosting sekarang? (Y/N): "

if /i "!DEPLOY_HOSTING!"=="Y" (
    echo.
    echo Informasi SSH:
    echo (Dapatkan dari email hosting atau cpanel)
    echo.
    
    set /p SSH_USER="SSH Username (default: root): "
    if "!SSH_USER!"=="" set SSH_USER=root
    
    set /p SSH_HOST="SSH Host (IP atau domain): "
    if "!SSH_HOST!"=="" (
        echo ❌ SSH Host tidak boleh kosong!
        pause
        exit /b 1
    )
    
    set /p SSH_PORT="SSH Port (default: 22): "
    if "!SSH_PORT!"=="" set SSH_PORT=22
    
    set /p PROJECT_PATH="Project path di hosting (contoh: /home/user/public_html): "
    if "!PROJECT_PATH!"=="" (
        echo ❌ Project path tidak boleh kosong!
        pause
        exit /b 1
    )
    
    echo.
    echo Connecting ke hosting...
    echo.
    
    REM Membuat deployment script di hosting
    set DEPLOY_CMD="cd !PROJECT_PATH! && git pull origin main && composer install --no-dev --optimize-autoloader && npm install --production && npm run build && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan migrate --force && chmod -R 775 storage/ bootstrap/cache/ && echo OK"
    
    call :log "Deploying to hosting: !SSH_HOST!"
    
    REM Jalankan SSH command
    ssh -p !SSH_PORT! !SSH_USER!@!SSH_HOST! !DEPLOY_CMD! >>%LOGFILE% 2>&1
    
    if errorlevel 1 (
        call :log "WARNING: SSH deployment failed"
        echo ⚠️  SSH deployment failed
        echo Possible reasons:
        echo   - SSH credentials salah
        echo   - Host tidak bisa diakses
        echo   - Git tidak installed di hosting
        echo.
        set /p RETRY="Retry? (Y/N): "
        if /i "!RETRY!"=="Y" goto :STEP 4
    ) else (
        echo ✅ Hosting deployment OK
    )
)

REM ===== COMPLETION =====
echo.
echo ╔════════════════════════════════════════════════════════════════╗
echo ║   ✅ FULL DEPLOYMENT SELESAI!                                 ║
echo ╚════════════════════════════════════════════════════════════════╝
echo.
echo Langkah berikutnya:
echo 1. Buka website production Anda untuk verifikasi
echo 2. Test fitur yang berubah
echo 3. Check error logs jika ada masalah
echo.
echo Log file: %LOGFILE%
echo.

call :log "=== FULL DEPLOYMENT COMPLETED ==="

set /p OPEN_LOGS="Buka log file? (Y/N): "
if /i "!OPEN_LOGS!"=="Y" (
    start notepad %LOGFILE%
)

pause
exit /b 0

:log
echo [%date% %time%] %~1 >> %LOGFILE%
echo %~1
goto :eof
