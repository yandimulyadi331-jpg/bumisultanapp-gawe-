# =================================================================
# FULL AUTOMATION DEPLOYMENT - LOKAL → GIT → READY FOR HOSTING
# =================================================================
# Guna: Otomatis deploy lokal + commit + push ke Git
# PowerShell version (lebih advanced & powerful)
# =================================================================

param(
    [switch]$SkipPush = $false,
    [string]$CommitMessage = "",
    [switch]$DryRun = $false
)

# Color functions
function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Cyan
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

function Write-Error-Custom {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

# Log setup
$LogFile = "deployment_full_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"
$Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

function Log {
    param([string]$Message)
    $LogMessage = "[$Timestamp] $Message"
    Add-Content -Path $LogFile -Value $LogMessage
}

# Header
Clear-Host
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   FULL AUTOMATION - LOKAL → GIT → HOSTING                ║" -ForegroundColor Cyan
Write-Host "║   Started: $Timestamp" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

Log "=== STARTING FULL AUTOMATION DEPLOYMENT ==="
Log "Parameters: SkipPush=$SkipPush, CommitMessage=$CommitMessage, DryRun=$DryRun"

# Function to run commands and log
function Invoke-Logged {
    param(
        [string]$Title,
        [string]$Command,
        [switch]$SkipOnError = $false
    )
    
    Write-Info $Title
    Log "Running: $Command"
    
    try {
        Invoke-Expression $Command 2>&1 | Tee-Object -FilePath $LogFile
        Write-Success "$Title - OK"
        Log "$Title - Completed successfully"
        return $true
    }
    catch {
        Write-Error-Custom "$Title - FAILED"
        Log "ERROR: $Title - $($_.Exception.Message)"
        if (-not $SkipOnError) {
            return $false
        }
        return $true
    }
}

# =====================================================================
# STEP 1: CHECK REQUIREMENTS
# =====================================================================
Write-Host ""
Write-Info "STEP 1: Checking requirements..."
Log "Step 1: Checking requirements"

try {
    $phpVersion = php -v 2>$null | Select-Object -First 1
    Write-Success "PHP: $phpVersion"
    Log "PHP: $phpVersion"
}
catch {
    Write-Error-Custom "PHP tidak ditemukan! Install dari: https://www.php.net/downloads"
    Log "ERROR: PHP not found"
    exit 1
}

try {
    $gitVersion = git --version 2>$null
    Write-Success "Git: $gitVersion"
    Log "Git: $gitVersion"
}
catch {
    Write-Error-Custom "Git tidak ditemukan! Install dari: https://git-scm.com/"
    Log "ERROR: Git not found"
    exit 1
}

# =====================================================================
# STEP 2: LOCAL DEPLOYMENT
# =====================================================================
Write-Host ""
Write-Info "STEP 2: Local Deployment..."
Log "Step 2: Local deployment"

if (-not (Invoke-Logged "Composer update" "composer update 2>&1")) {
    Write-Warning "Composer warnings, lanjut saja..."
}

if (-not (Invoke-Logged "NPM install" "npm install 2>&1")) {
    Write-Error-Custom "NPM install failed!"
    exit 1
}

if (-not (Invoke-Logged "Vite build" "npm run build 2>&1")) {
    Write-Error-Custom "Vite build failed!"
    exit 1
}

Invoke-Logged "Clear cache" "php artisan cache:clear 2>&1" -SkipOnError
Invoke-Logged "Config cache" "php artisan config:cache 2>&1" -SkipOnError
Invoke-Logged "Route cache" "php artisan route:cache 2>&1" -SkipOnError
Invoke-Logged "Optimize" "php artisan optimize 2>&1" -SkipOnError

Write-Success "Local Deployment OK"
Log "Local deployment completed"

# =====================================================================
# STEP 3: GIT OPERATIONS
# =====================================================================
Write-Host ""
Write-Info "STEP 3: Git Operations..."
Log "Step 3: Git operations"

Write-Host ""
Write-Info "Checking git status..."
git status 2>&1 | Tee-Object -FilePath $LogFile

Write-Host ""
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Yellow
Write-Host "Files to be committed:" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Yellow
git diff --name-only
git status -s

Write-Host ""

# Get commit message
if ($CommitMessage -eq "") {
    $CommitMessage = Read-Host "Masukkan pesan commit (atau Enter untuk auto)"
    if ($CommitMessage -eq "") {
        $CommitMessage = "Update: Perubahan lokal $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
    }
}

Write-Info "Commit message: $CommitMessage"
Log "Commit message: $CommitMessage"

if ($DryRun) {
    Write-Warning "DRY RUN MODE - Tidak akan benar-benar push ke Git"
    Log "DRY RUN MODE"
}

Write-Host ""
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "Git Operations:" -ForegroundColor Cyan
Write-Host "  1. git add ." -ForegroundColor Cyan
Write-Host "  2. git commit -m `"$CommitMessage`"" -ForegroundColor Cyan
if (-not $SkipPush -and -not $DryRun) {
    Write-Host "  3. git push origin main" -ForegroundColor Cyan
}
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Cyan

# Git add
Write-Host ""
Write-Info "Adding files to git..."
Log "Running: git add ."

if ($DryRun) {
    Write-Warning "(DRY RUN) Would run: git add ."
}
else {
    git add . 2>&1 | Tee-Object -FilePath $LogFile
    if ($LASTEXITCODE -ne 0) {
        Write-Error-Custom "Git add failed!"
        Log "ERROR: git add failed"
        exit 1
    }
    Write-Success "Git add OK"
    Log "Git add completed"
}

# Git commit
Write-Host ""
Write-Info "Committing changes..."
Log "Running: git commit"

if ($DryRun) {
    Write-Warning "(DRY RUN) Would run: git commit -m `"$CommitMessage`""
}
else {
    git commit -m "$CommitMessage" 2>&1 | Tee-Object -FilePath $LogFile
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Git commit OK"
        Log "Git commit completed"
    }
    else {
        Write-Warning "No changes to commit"
        Log "INFO: No changes to commit"
    }
}

# Git push
if (-not $SkipPush -and -not $DryRun) {
    Write-Host ""
    Write-Info "Pushing to GitHub..."
    Log "Running: git push origin main"
    
    git push origin main 2>&1 | Tee-Object -FilePath $LogFile
    if ($LASTEXITCODE -ne 0) {
        Write-Error-Custom "Git push failed!"
        Log "ERROR: git push failed"
        Write-Host ""
        Write-Host "Troubleshooting:" -ForegroundColor Yellow
        Write-Host "  - Check internet connection"
        Write-Host "  - Check git credentials"
        Write-Host "  - Try manually: git push origin main"
        exit 1
    }
    Write-Success "Git push OK"
    Log "Git push completed"
}

# =====================================================================
# STEP 4: VERIFICATION
# =====================================================================
Write-Host ""
Write-Info "STEP 4: Verification..."
Log "Step 4: Verification"

Write-Host ""
Write-Info "Recent commits:"
git log --oneline -5 2>&1 | Tee-Object -FilePath $LogFile

Write-Success "Verification OK"
Log "Verification completed"

# =====================================================================
# COMPLETION
# =====================================================================
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║   ✅ FULL AUTOMATION COMPLETED!                          ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green

Write-Host ""
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "  SUMMARY:" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""
Write-Host "✅ Local Deployment" -ForegroundColor Green
Write-Host "   - Composer updated" -ForegroundColor Green
Write-Host "   - NPM dependencies updated" -ForegroundColor Green
Write-Host "   - Frontend built (Vite)" -ForegroundColor Green
Write-Host "   - Cache cleared" -ForegroundColor Green
Write-Host "   - Application optimized" -ForegroundColor Green
Write-Host ""
Write-Host "✅ Git Operations" -ForegroundColor Green
Write-Host "   - Files added" -ForegroundColor Green
Write-Host "   - Changes committed" -ForegroundColor Green
if (-not $SkipPush -and -not $DryRun) {
    Write-Host "   - Pushed to GitHub" -ForegroundColor Green
}
Write-Host ""

Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  NEXT STEPS - DEPLOY KE HOSTING:" -ForegroundColor Cyan
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Buka Termius" -ForegroundColor Cyan
Write-Host "2. Connect ke hosting" -ForegroundColor Cyan
Write-Host "3. Jalankan commands:" -ForegroundColor Cyan
Write-Host ""
Write-Host "   cd /home/bumisultan/public_html" -ForegroundColor Yellow
Write-Host "   git pull origin main" -ForegroundColor Yellow
Write-Host "   composer install --no-dev && npm run build" -ForegroundColor Yellow
Write-Host "   php artisan cache:clear && php artisan config:cache" -ForegroundColor Yellow
Write-Host "   chmod -R 775 storage/ bootstrap/cache/" -ForegroundColor Yellow
Write-Host ""
Write-Host "4. Buka website online untuk verifikasi" -ForegroundColor Cyan
Write-Host "5. Selesai! ✅" -ForegroundColor Cyan
Write-Host ""

Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "Log file: $LogFile" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green

Write-Host ""
Log "=== DEPLOYMENT COMPLETED SUCCESSFULLY ==="
