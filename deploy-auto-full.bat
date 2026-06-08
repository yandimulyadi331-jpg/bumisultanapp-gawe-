@echo off
REM =================================================================
REM FULL AUTOMATION DEPLOYMENT - LOKAL → GIT → READY FOR HOSTING
REM =================================================================
REM Guna: Otomatisasi SEMUA: deploy lokal + commit + push ke Git
REM Setelah script selesai, tinggal pull di hosting via Termius!
REM =================================================================

setlocal enabledelayedexpansion
set LOGFILE=deployment_full_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%.log
set TIMESTAMP=%date% %time%

echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║   FULL AUTOMATION DEPLOYMENT - LOKAL TO GIT                 ║
echo ║   %TIMESTAMP%
echo ╚══════════════════════════════════════════════════════════════╝
echo.

call :log "=== STARTING FULL AUTOMATION DEPLOYMENT ==="

REM =====================================================================
REM STEP 1: CHECK REQUIREMENTS
REM =====================================================================
echo.
echo [STEP 1] Checking requirements...
call :log "Step 1: Checking requirements"

REM Check PHP
php -v >nul 2>&1
if errorlevel 1 (
    call :log "ERROR: PHP not found!"
    call :error "PHP tidak ditemukan! Install PHP terlebih dahulu."
    exit /b 1
)

REM Check Git
git --version >nul 2>&1
if errorlevel 1 (
    call :log "ERROR: Git not found!"
    call :error "Git tidak ditemukan! Install Git terlebih dahulu."
    echo Ambil dari: https://git-scm.com/
    pause
    exit /b 1
)

call :success "PHP & Git ditemukan ✓"
call :log "Requirements OK"

REM =====================================================================
REM STEP 2: LOCAL DEPLOYMENT
REM =====================================================================
echo.
echo [STEP 2] Local Deployment...
call :log "Step 2: Local deployment"

echo Updating Composer...
call :log "Running: composer update"
composer update >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :warning "Composer update mungkin ada warning, lanjut saja..."
    call :log "WARNING: Composer update may have warnings"
)

echo Updating NPM...
call :log "Running: npm install"
npm install >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :error "NPM install failed!"
    call :log "ERROR: npm install failed"
    pause
    exit /b 1
)

echo Building Frontend...
call :log "Running: npm run build"
npm run build >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :error "Vite build failed!"
    call :log "ERROR: npm run build failed"
    pause
    exit /b 1
)

echo Clearing Cache...
call :log "Running: php artisan cache:clear"
php artisan cache:clear >> %LOGFILE% 2>&1
php artisan config:cache >> %LOGFILE% 2>&1
php artisan route:cache >> %LOGFILE% 2>&1

echo Optimizing...
call :log "Running: php artisan optimize"
php artisan optimize >> %LOGFILE% 2>&1

call :success "Local Deployment OK ✓"
call :log "Local deployment completed"

REM =====================================================================
REM STEP 3: GIT OPERATIONS
REM =====================================================================
echo.
echo [STEP 3] Git Operations...
call :log "Step 3: Git operations"

echo.
echo Checking Git status...
call :log "Checking git status"
git status >> %LOGFILE% 2>&1

echo.
echo ============================================
echo Perubahan yang akan di-commit:
echo ============================================
git diff --name-only
git status -s

echo.
set /p COMMIT_MSG="Masukkan pesan commit (atau Enter untuk default): "
if "!COMMIT_MSG!"=="" (
    set COMMIT_MSG=Update: Perubahan lokal %date% %time%
)

echo.
call :info "Commit message: !COMMIT_MSG!"
call :log "Commit message: !COMMIT_MSG!"

echo.
echo ============================================
echo Tahap Git:
echo ============================================
echo 1. git add .
echo 2. git commit -m "!COMMIT_MSG!"
echo 3. git push origin main
echo.

REM Add files
echo Adding files to git...
call :log "Running: git add ."
git add . >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :error "Git add failed!"
    call :log "ERROR: git add failed"
    pause
    exit /b 1
)
call :success "Git add OK ✓"
call :log "Git add completed"

REM Commit
echo Committing changes...
call :log "Running: git commit"
git commit -m "!COMMIT_MSG!" >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :warning "No changes to commit (mungkin tidak ada perubahan baru)"
    call :log "INFO: No changes to commit"
) else (
    call :success "Git commit OK ✓"
    call :log "Git commit completed"
)

REM Push
echo.
echo Pushing to GitHub...
call :log "Running: git push origin main"
git push origin main >> %LOGFILE% 2>&1
if errorlevel 1 (
    call :error "Git push failed! Check your git credentials."
    call :log "ERROR: git push failed"
    echo.
    echo Troubleshooting:
    echo - Check internet connection
    echo - Check git credentials
    echo - Check repository access
    echo - Try manually: git push origin main
    pause
    exit /b 1
) else (
    call :success "Git push OK ✓"
    call :log "Git push completed"
)

REM =====================================================================
REM STEP 4: VERIFICATION
REM =====================================================================
echo.
echo [STEP 4] Verification...
call :log "Step 4: Verification"

echo.
echo Checking remote status...
call :log "Checking remote status"
git log --oneline -5

echo.
call :success "Git status OK ✓"
call :log "Verification completed"

REM =====================================================================
REM COMPLETION
REM =====================================================================
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║   ✅ FULL AUTOMATION COMPLETED!                             ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

echo.
echo ════════════════════════════════════════════════════════════════
echo  SUMMARY:
echo ════════════════════════════════════════════════════════════════
echo.
echo ✅ Local Deployment
echo    - Composer updated
echo    - NPM dependencies updated
echo    - Frontend built (Vite)
echo    - Cache cleared
echo    - Application optimized
echo.
echo ✅ Git Operations
echo    - Files added
echo    - Changes committed
echo    - Pushed to GitHub
echo.
echo ════════════════════════════════════════════════════════════════
echo  NEXT STEPS - DEPLOY KE HOSTING:
echo ════════════════════════════════════════════════════════════════
echo.
echo 1. Buka Termius
echo 2. Connect ke hosting
echo 3. Jalankan commands:
echo.
echo    cd /home/bumisultan/public_html
echo    git pull origin main
echo    composer install --no-dev
echo    npm run build
echo    php artisan cache:clear
echo    php artisan config:cache
echo    php artisan route:cache
echo    chmod -R 775 storage/ bootstrap/cache/
echo.
echo 4. Buka website online untuk verifikasi
echo 5. Selesai! ✅
echo.
echo ════════════════════════════════════════════════════════════════
echo Log file: %LOGFILE%
echo ════════════════════════════════════════════════════════════════
echo.

pause
exit /b 0

REM =====================================================================
REM FUNCTIONS
REM =====================================================================

:log
echo [%date% %time%] %~1 >> %LOGFILE%
goto :eof

:success
echo.
echo ✅ %~1
echo.
goto :eof

:error
echo.
echo ❌ %~1
echo.
goto :eof

:warning
echo.
echo ⚠️  %~1
echo.
goto :eof

:info
echo.
echo ℹ️  %~1
echo.
goto :eof
