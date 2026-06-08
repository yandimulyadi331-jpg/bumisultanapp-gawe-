# =================================================================
# DEPLOYMENT SCRIPT UNTUK LOKAL - BUMI SULTAN APP (PowerShell)
# =================================================================
# Guna: Otomatisasi deployment perubahan kode di komputer lokal
# =================================================================

param(
    [switch]$SkipMigrations = $false,
    [switch]$SkipSeeding = $false,
    [switch]$BuildOnly = $false,
    [switch]$Fast = $false
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

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

# Log file setup
$LogFile = "deployment_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"
$Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"

function Log {
    param([string]$Message)
    $LogMessage = "[$Timestamp] $Message"
    Add-Content -Path $LogFile -Value $LogMessage
}

# Header
Clear-Host
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║   BUMI SULTAN - LOCAL DEPLOYMENT SCRIPT (PowerShell)      ║" -ForegroundColor Cyan
Write-Host "║   Started: $Timestamp" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

Log "=== STARTING LOCAL DEPLOYMENT ==="
Log "Parameters: SkipMigrations=$SkipMigrations, SkipSeeding=$SkipSeeding, Fast=$Fast"

# Function to run commands and log
function Invoke-Command-Logged {
    param(
        [string]$Title,
        [string]$Command,
        [switch]$SkipOnError = $false
    )
    
    Write-Info $Title
    Log "Running: $Command"
    
    try {
        Invoke-Expression $Command | Tee-Object -FilePath $LogFile
        Write-Success "$Title - OK"
        Log "$Title - Completed successfully"
        return $true
    }
    catch {
        Write-Error "$Title - FAILED"
        Log "ERROR: $Title - $($_.Exception.Message)"
        if (-not $SkipOnError) {
            return $false
        }
        return $true
    }
}

# Function to confirm action
function Get-Confirmation {
    param([string]$Message)
    $response = Read-Host "$Message (Y/N)"
    return $response -eq "Y" -or $response -eq "y"
}

# DEPLOYMENT STEPS
Write-Host ""
Write-Info "Memulai proses deployment..."
Write-Host ""

# Step 1: Check PHP & Composer
Write-Info "STEP 1: Checking PHP & Composer..."
Log "Step 1: Checking PHP & Composer"

try {
    $phpVersion = php -v 2>$null | Select-Object -First 1
    Write-Success "PHP: $phpVersion"
    Log "PHP: $phpVersion"
}
catch {
    Write-Error "PHP tidak ditemukan! Pastikan PHP sudah diinstall."
    Log "ERROR: PHP not found"
    exit 1
}

# Step 2: Update Composer
if (-not $BuildOnly) {
    Write-Host ""
    Write-Info "STEP 2: Updating Composer Dependencies..."
    Log "Step 2: Updating Composer"
    
    if (Invoke-Command-Logged "Composer update" "composer update 2>&1") {
        # OK
    }
    else {
        if (-not (Get-Confirmation "Composer update failed. Continue anyway?")) {
            exit 1
        }
    }
}

# Step 3: Update NPM
if (-not $BuildOnly) {
    Write-Host ""
    Write-Info "STEP 3: Updating NPM Dependencies..."
    Log "Step 3: Updating NPM"
    
    Invoke-Command-Logged "NPM install" "npm install 2>&1" -SkipOnError
}

# Step 4: Build Frontend
Write-Host ""
Write-Info "STEP 4: Building Frontend with Vite..."
Log "Step 4: Building Vite frontend"

if (Invoke-Command-Logged "Vite build" "npm run build 2>&1") {
    # OK
}
else {
    if (-not (Get-Confirmation "Vite build failed. Continue anyway?")) {
        exit 1
    }
}

# Step 5: Clear Caches
if (-not $BuildOnly) {
    Write-Host ""
    Write-Info "STEP 5: Clearing Laravel Caches..."
    Log "Step 5: Clearing caches"
    
    Invoke-Command-Logged "Cache clear" "php artisan cache:clear 2>&1" -SkipOnError
    Invoke-Command-Logged "Config cache" "php artisan config:cache 2>&1" -SkipOnError
    Invoke-Command-Logged "Route cache" "php artisan route:cache 2>&1" -SkipOnError
}

# Step 6: Check Migrations
if (-not $SkipMigrations -and -not $BuildOnly) {
    Write-Host ""
    Write-Info "STEP 6: Database Migrations..."
    Log "Step 6: Checking migrations"
    
    php artisan migrate:status 2>&1 | Tee-Object -FilePath $LogFile
    
    if (Get-Confirmation "Run pending migrations?") {
        Invoke-Command-Logged "Migrate" "php artisan migrate 2>&1"
    }
}

# Step 7: Seed Database
if (-not $SkipSeeding -and -not $BuildOnly) {
    Write-Host ""
    if (Get-Confirmation "Run database seeding?") {
        Write-Info "STEP 7: Database Seeding..."
        Log "Step 7: Running seeders"
        
        Invoke-Command-Logged "DB Seed" "php artisan db:seed 2>&1" -SkipOnError
    }
}

# Step 8: Optimize
if (-not $BuildOnly) {
    Write-Host ""
    Write-Info "STEP 8: Optimizing Application..."
    Log "Step 8: Optimizing"
    
    Invoke-Command-Logged "Optimize" "php artisan optimize 2>&1" -SkipOnError
}

# COMPLETION
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║   ✅ DEPLOYMENT COMPLETED!                               ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Success "Perubahan berhasil di-deploy secara lokal!"
Write-Host ""
Write-Info "Langkah berikutnya:"
Write-Host "  1. Jalankan server: php artisan serve"
Write-Host "  2. Buka di browser: http://localhost:8000"
Write-Host "  3. Verifikasi perubahan"
Write-Host ""
Write-Info "Log file: $LogFile"
Write-Host ""

Log "=== DEPLOYMENT COMPLETED SUCCESSFULLY ==="

# Offer to run dev server
if (Get-Confirmation "Jalankan dev server sekarang?") {
    Write-Info "Menjalankan: php artisan serve"
    Log "Starting dev server"
    php artisan serve
}
