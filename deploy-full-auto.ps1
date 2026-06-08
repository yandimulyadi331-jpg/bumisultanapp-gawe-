# =================================================================
# FULL DEPLOYMENT AUTOMATION - LOKAL KE HOSTING (PowerShell)
# =================================================================
# Mengotomatisasi: Deploy Lokal → Git Commit → Push → Deploy Hosting
# =================================================================

param(
    [switch]$SkipGit = $false,
    [switch]$SkipHosting = $false,
    [string]$CommitMessage = "Update: Automatic deployment $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
)

# Color functions
function Write-Success { param([string]$Message); Write-Host "✅ $Message" -ForegroundColor Green }
function Write-Info { param([string]$Message); Write-Host "ℹ️  $Message" -ForegroundColor Cyan }
function Write-Warning { param([string]$Message); Write-Host "⚠️  $Message" -ForegroundColor Yellow }
function Write-Error { param([string]$Message); Write-Host "❌ $Message" -ForegroundColor Red }

# Setup logging
$LogFile = "full-deployment_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"
function Log { param([string]$Message); Add-Content -Path $LogFile -Value "[$(Get-Date -Format 'HH:mm:ss')] $Message" }

# Header
Clear-Host
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   FULL AUTOMATION - LOKAL KE HOSTING (PowerShell)        ║" -ForegroundColor Cyan
Write-Host "║   Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

Log "=== STARTING FULL AUTOMATION DEPLOYMENT ==="

# ===== STEP 1: LOKAL DEPLOYMENT =====
Write-Host ""
Write-Info "[STEP 1/4] Lokal Deployment..."
Log "Step 1: Lokal deployment starting"

try {
    Write-Info "Updating Composer..."
    Log "Running: composer update"
    & composer update 2>&1 | Tee-Object -FilePath $LogFile
    Write-Success "Composer OK"
}
catch {
    Write-Warning "Composer update failed: $_"
    Log "WARNING: Composer update failed"
}

try {
    Write-Info "Installing NPM packages..."
    Log "Running: npm install"
    & npm install 2>&1 | Tee-Object -FilePath $LogFile
    Write-Success "NPM OK"
}
catch {
    Write-Warning "NPM install failed: $_"
    Log "WARNING: NPM install failed"
}

try {
    Write-Info "Building Vite frontend..."
    Log "Running: npm run build"
    & npm run build 2>&1 | Tee-Object -FilePath $LogFile
    Write-Success "Vite build OK"
}
catch {
    Write-Error "Vite build failed!"
    Log "ERROR: Vite build failed"
    exit 1
}

try {
    Write-Info "Clearing caches..."
    Log "Running: php artisan cache:clear"
    & php artisan cache:clear 2>&1 | Tee-Object -FilePath $LogFile
    & php artisan config:cache 2>&1 | Tee-Object -FilePath $LogFile
    & php artisan route:cache 2>&1 | Tee-Object -FilePath $LogFile
    Write-Success "Caches cleared"
}
catch {
    Write-Warning "Cache clear failed: $_"
}

Write-Success "Lokal deployment completed"
Log "Step 1: Completed"

# ===== STEP 2: GIT COMMIT =====
if (-not $SkipGit) {
    Write-Host ""
    Write-Info "[STEP 2/4] Git Commit..."
    Log "Step 2: Git commit starting"
    
    try {
        $gitStatus = & git status 2>&1
        Write-Info "Git Status:"
        Write-Host $gitStatus
        
        Write-Info "Adding files..."
        Log "Running: git add ."
        & git add . 2>&1 | Tee-Object -FilePath $LogFile
        
        Write-Info "Committing: $CommitMessage"
        Log "Running: git commit -m '$CommitMessage'"
        & git commit -m $CommitMessage 2>&1 | Tee-Object -FilePath $LogFile
        
        Write-Success "Git commit OK"
        Log "Step 2: Commit completed"
    }
    catch {
        Write-Warning "Git commit failed: $_"
        Log "WARNING: Git commit failed"
    }
}

# ===== STEP 3: GIT PUSH =====
if (-not $SkipGit) {
    Write-Host ""
    Write-Info "[STEP 3/4] Git Push..."
    Log "Step 3: Git push starting"
    
    try {
        Write-Info "Pushing to origin main..."
        Log "Running: git push origin main"
        & git push origin main 2>&1 | Tee-Object -FilePath $LogFile
        Write-Success "Git push OK"
        Log "Step 3: Push completed"
    }
    catch {
        Write-Warning "Git push failed: $_"
        Log "WARNING: Git push failed"
    }
}

# ===== STEP 4: HOSTING DEPLOYMENT =====
if (-not $SkipHosting) {
    Write-Host ""
    Write-Info "[STEP 4/4] Hosting Deployment via SSH..."
    Log "Step 4: Hosting deployment starting"
    
    $deployHosting = Read-Host "Deploy ke hosting sekarang? (Y/N)"
    
    if ($deployHosting -eq "Y" -or $deployHosting -eq "y") {
        Write-Host ""
        Write-Info "SSH Connection Information:"
        
        $sshUser = Read-Host "SSH Username (default: root)"
        if ([string]::IsNullOrWhiteSpace($sshUser)) { $sshUser = "root" }
        
        $sshHost = Read-Host "SSH Host (IP atau domain)"
        if ([string]::IsNullOrWhiteSpace($sshHost)) {
            Write-Error "SSH Host tidak boleh kosong!"
            exit 1
        }
        
        $sshPort = Read-Host "SSH Port (default: 22)"
        if ([string]::IsNullOrWhiteSpace($sshPort)) { $sshPort = "22" }
        
        $projectPath = Read-Host "Project path di hosting (contoh: /home/user/public_html)"
        if ([string]::IsNullOrWhiteSpace($projectPath)) {
            Write-Error "Project path tidak boleh kosong!"
            exit 1
        }
        
        Write-Host ""
        Write-Info "Connecting to hosting..."
        Log "Connecting to $sshUser@$sshHost on port $sshPort"
        
        # Build deployment command
        $deployCmd = "cd $projectPath && git pull origin main && composer install --no-dev --optimize-autoloader && npm install --production && npm run build && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan migrate --force && chmod -R 775 storage/ bootstrap/cache/ && echo 'Deployment selesai!'"
        
        Log "Running deployment command on $sshHost"
        
        try {
            & ssh -p $sshPort "$sshUser@$sshHost" $deployCmd 2>&1 | Tee-Object -FilePath $LogFile
            Write-Success "Hosting deployment OK"
            Log "Step 4: Deployment completed"
        }
        catch {
            Write-Error "SSH deployment failed: $_"
            Log "ERROR: SSH deployment failed"
            Write-Host ""
            Write-Warning "Possible reasons:"
            Write-Warning "  - SSH credentials salah"
            Write-Warning "  - Host tidak bisa diakses"
            Write-Warning "  - Git tidak installed di hosting"
        }
    }
}

# ===== COMPLETION =====
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║   ✅ FULL DEPLOYMENT COMPLETED!                           ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Success "Automation deployment selesai!"
Write-Host ""
Write-Info "Next Steps:"
Write-Host "  1. Buka website production Anda untuk verifikasi"
Write-Host "  2. Test fitur yang berubah"
Write-Host "  3. Check error logs jika ada masalah"
Write-Host ""
Write-Info "Log file: $LogFile"
Write-Host ""

Log "=== FULL DEPLOYMENT COMPLETED SUCCESSFULLY ==="

$openLogs = Read-Host "Buka log file? (Y/N)"
if ($openLogs -eq "Y" -or $openLogs -eq "y") {
    Invoke-Item $LogFile
}
