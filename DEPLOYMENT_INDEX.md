# 📦 DEPLOYMENT AUTOMATION - BUMI SULTAN APP

> **Panduan lengkap untuk automation deployment lokal dan hosting**

---

## 🎯 QUICK START (30 detik)

### Deployment Lokal:
```batch
REM Buka Command Prompt, arahkan ke folder project, lalu:
deploy-local.bat
```

### Deployment Hosting:
```bash
# Buka Termius, connect ke hosting, lalu copy-paste:
cd /home/bumisultan/public_html
git pull origin main && composer install --no-dev && npm install && npm run build
php artisan cache:clear && php artisan config:cache && php artisan route:cache
php artisan migrate --force
chmod -R 775 storage/ bootstrap/cache/
```

---

## 📋 DAFTAR FILE DEPLOYMENT

| File | Tujuan | Untuk |
|------|--------|-------|
| **deploy-local.bat** | ⭐ Automation deployment lokal | Windows CMD |
| **deploy-local.ps1** | Automation deployment lokal (alternatif) | Windows PowerShell |
| **DEPLOYMENT_GUIDE.md** | Panduan lengkap deployment | Referensi lengkap |
| **TERMIUS_SETUP_GUIDE.md** | Setup & cara pakai Termius | Hosting deployment |
| **quick-deploy-steps.txt** | Langkah cepat tanpa detail | Quick reference |
| **DEPLOYMENT_INDEX.md** | File ini - penjelasan semua file | Overview |

---

## 🖥️ OPSI 1: DEPLOYMENT LOKAL (WINDOWS)

### Metode A: Batch Script (REKOMENDASI)

**Keuntungan:**
- ✅ Fully automated
- ✅ Interactive prompts
- ✅ Error handling
- ✅ Comprehensive logging

**Cara:**
```batch
1. Buka Command Prompt (Win+R → cmd → Enter)
2. cd "c:\Users\usedr\Desktop\APLIKASI BUMI SULTAN TERBARU\presensiv2\presensigpsv2-main\gawe.bumisultan"
3. deploy-local.bat
4. Ikuti instruksi yang muncul
5. Setelah selesai, jalankan: php artisan serve
6. Buka: http://localhost:8000
```

### Metode B: PowerShell Script (Alternatif)

**Keuntungan:**
- ✅ Lebih modern & powerful
- ✅ Better error handling
- ✅ Colored output
- ✅ Flexible parameters

**Cara:**
```powershell
1. Buka PowerShell as Administrator
2. Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
3. cd "c:\Users\usedr\Desktop\APLIKASI BUMI SULTAN TERBARU\presensiv2\presensigpsv2-main\gawe.bumisultan"
4. .\deploy-local.ps1
   
   # Atau dengan parameters:
   .\deploy-local.ps1 -Fast                    # Quick deployment
   .\deploy-local.ps1 -SkipMigrations          # Skip database changes
   .\deploy-local.ps1 -BuildOnly               # Build frontend only
```

### Metode C: Manual (Jika script error)

```bash
# 1. Update dependencies
composer update
npm install

# 2. Build frontend
npm run build

# 3. Clear cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 4. Run migrations (jika ada)
php artisan migrate

# 5. Optimize
php artisan optimize

# 6. Run server
php artisan serve
```

---

## 🌐 OPSI 2: DEPLOYMENT HOSTING VIA TERMIUS

### Setup Awal (Satu Kali)

1. **Download Termius:** https://termius.com/
2. **Install & buka aplikasi**
3. **Tambah SSH host:**
   - Klik "+" → "SSH"
   - Isi: Label, IP/Domain, Username, Password, Port (22)
   - Save
4. **Lihat detail di:** [TERMIUS_SETUP_GUIDE.md](TERMIUS_SETUP_GUIDE.md)

### Deploy Setiap Kali Ada Perubahan

**Langkah 1:** Connect ke hosting via Termius
```bash
Termius → Select host → Connect
```

**Langkah 2:** Jalankan deployment commands
```bash
# Navigate ke project
cd /home/bumisultan/public_html

# Pull kode terbaru
git pull origin main

# Update & build
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Database migration
php artisan migrate --force

# Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

echo "✅ Deployment Selesai!"
```

**Langkah 3:** Verifikasi
```bash
# Check Laravel status
php artisan tinker
>>> echo 'OK'
```

---

## 📊 DEPLOYMENT CHECKLIST

### Sebelum Deploy Lokal:
- [ ] Pastikan MySQL running
- [ ] Pastikan Node.js installed (check: `node -v`)
- [ ] Pastikan PHP 8.1+ installed (check: `php -v`)
- [ ] Pastikan Composer installed (check: `composer -v`)
- [ ] `.env` file sudah ada

### Sebelum Deploy Hosting:
- [ ] Semua kode sudah di-commit ke Git
- [ ] Database hosting sudah di-backup
- [ ] File hosting sudah di-backup
- [ ] SSH credentials siap
- [ ] Termius sudah ter-install
- [ ] Host SSH sudah ditambah di Termius

### Setelah Deployment:
- [ ] Website bisa diakses
- [ ] Login berfungsi
- [ ] Fitur baru/perubahan berfungsi
- [ ] Tidak ada error di console browser (F12)
- [ ] File permissions OK (untuk hosting)
- [ ] Error logs OK (check: `tail storage/logs/laravel.log`)

---

## ⚡ COMMAND QUICK REFERENCE

| Task | Command |
|------|---------|
| **Lokal Deployment** | `deploy-local.bat` |
| **Lokal (PowerShell)** | `.\deploy-local.ps1` |
| **Dev Server** | `php artisan serve` |
| **Build Frontend** | `npm run build` |
| **Watch Frontend** | `npm run dev` |
| **Clear All Cache** | `php artisan optimize:clear` |
| **Run Migrations** | `php artisan migrate` |
| **Rollback Migration** | `php artisan migrate:rollback` |
| **Interactive Shell** | `php artisan tinker` |
| **Check Status** | `php artisan migrate:status` |
| **SSH Connect (Termius)** | Open Termius → Select host → Connect |
| **View Error Logs** | `tail -f storage/logs/laravel.log` |
| **Fix Permissions** | `chmod -R 775 storage/ bootstrap/cache/` |

---

## 🆘 TROUBLESHOOTING

### 🔴 Error: "deploy-local.bat not found"
**Solusi:**
```batch
# Pastikan Anda di folder project yang benar
cd "c:\Users\usedr\Desktop\APLIKASI BUMI SULTAN TERBARU\presensiv2\presensigpsv2-main\gawe.bumisultan"
# Lalu jalankan:
deploy-local.bat
```

### 🔴 Error: "PHP tidak ditemukan"
**Solusi:**
1. Pastikan PHP sudah diinstall
2. Check: `php -v`
3. Jika error, tambah PHP ke system PATH
4. Restart Command Prompt

### 🔴 Error: "npm ERR! 404"
**Solusi:**
```bash
npm cache clean --force
npm install
```

### 🔴 Error: "composer update lambat"
**Solusi:**
```bash
# Gunakan ini untuk lebih cepat:
composer install --no-dev
composer dump-autoload -o
```

### 🔴 Error: "Cannot connect ke hosting via Termius"
**Solusi:**
1. Cek IP/domain hosting benar?
2. Cek username & password benar?
3. Cek port SSH adalah 22?
4. Contact provider hosting jika masih error

### 🔴 Error: "Permission denied" di hosting
**Solusi:**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data /path/to/project
```

---

## 📖 DOKUMENTASI LENGKAP

- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Panduan detail deployment lokal & hosting
- **[TERMIUS_SETUP_GUIDE.md](TERMIUS_SETUP_GUIDE.md)** - Setup & tips penggunaan Termius
- **[quick-deploy-steps.txt](quick-deploy-steps.txt)** - Quick reference tanpa detail

---

## 🎬 WORKFLOW DEPLOYMENT LENGKAP

```
1. LOKAL (DEV)
   ├─ Buat perubahan kode
   ├─ Deploy lokal: deploy-local.bat
   ├─ Test di: http://localhost:8000
   └─ Commit ke Git: git push origin main

2. HOSTING (PRODUCTION)
   ├─ Backup database & files
   ├─ Connect via Termius
   ├─ Git pull & deploy
   ├─ Jalankan migrations
   ├─ Clear caches
   └─ Verifikasi di production URL

3. MONITORING
   ├─ Check error logs
   ├─ Test critical features
   ├─ Monitor untuk 1-2 jam
   └─ Report jika ada issue
```

---

## 📝 INFORMASI VERSI

**Aplikasi:** Bumi Sultan App v2  
**Created:** 2026-06-08  
**Framework:** Laravel 10 + Vite  
**PHP Version:** ^8.1  
**Database:** MySQL  

Lihat file `VERSION` untuk versi aplikasi saat ini.

---

## 🔐 SECURITY BEST PRACTICES

⚠️ **PENTING!**

1. ✅ Jangan share SSH credentials
2. ✅ Selalu backup sebelum deploy
3. ✅ Test lokal sebelum deploy ke production
4. ✅ Monitor error logs setelah deployment
5. ✅ Jangan commit `.env` ke Git
6. ✅ Gunakan HTTPS untuk koneksi production
7. ✅ Set proper file permissions (755/775)
8. ✅ Keep dependencies updated

---

## 💡 TIPS & TRICKS

### Speedup Local Deployment
```bash
# Gunakan --no-dev untuk lebih cepat
composer install --no-dev --optimize-autoloader

# Build production (minified)
npm run build
```

### Automated Deployment Script (Hosting)
Buat file `deploy.sh` di hosting:
```bash
#!/bin/bash
cd /home/bumisultan/public_html
git pull origin main
composer install --no-dev && npm install && npm run build
php artisan migrate --force
php artisan optimize
chmod -R 775 storage/
```

Jalankan: `bash deploy.sh`

### Watch Frontend Changes (Development)
```bash
npm run dev  # Auto-rebuild saat ada perubahan CSS/JS
```

---

## 📞 SUPPORT & HELP

**Jika ada masalah:**
1. Baca error message dengan teliti
2. Check documentation files
3. Search di Google dengan error message
4. Contact provider hosting (untuk masalah hosting)
5. Check Laravel documentation: https://laravel.com/docs

---

## ✅ CHECKLIST SEBELUM GO LIVE

- [ ] Semua deployment scripts sudah tested
- [ ] Lokal deployment berjalan lancar
- [ ] Hosting SSH connection working
- [ ] Database backup tersedia
- [ ] Error logs di-monitor
- [ ] Critical features tested
- [ ] Team sudah paham deployment process
- [ ] Rollback plan sudah ready

---

**Happy Deploying! 🚀**

Untuk questions atau updates, lihat DEPLOYMENT_GUIDE.md

Last Updated: 2026-06-08
