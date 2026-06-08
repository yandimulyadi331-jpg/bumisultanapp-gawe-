# PANDUAN DEPLOYMENT BUMI SULTAN APP

## 📋 DAFTAR ISI
1. [Deployment Lokal](#deployment-lokal)
2. [Deployment ke Hosting via Termius](#deployment-ke-hosting-via-termius)
3. [Troubleshooting](#troubleshooting)

---

## 🖥️ DEPLOYMENT LOKAL

### Opsi 1: Menggunakan Script Batch (REKOMENDASI - Windows)

**Langkah pertama kali:**
1. Buka Command Prompt atau PowerShell
2. Arahkan ke folder project: `cd "c:\Users\usedr\Desktop\APLIKASI BUMI SULTAN TERBARU\presensiv2\presensigpsv2-main\gawe.bumisultan"`
3. Jalankan script: `deploy-local.bat`
4. Ikuti instruksi yang muncul di layar

**Script akan otomatis melakukan:**
- ✅ Update Composer dependencies
- ✅ Update NPM packages
- ✅ Build Vite frontend assets
- ✅ Clear Laravel caches
- ✅ Run migrations (jika ada)
- ✅ Seed database (opsional)
- ✅ Optimize application

### Opsi 2: Manual Command (Jika script error)

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

# 6. Jalankan server
php artisan serve
```

### Menjalankan Development Server

Setelah deployment selesai:
```bash
php artisan serve
```

Aplikasi akan berjalan di: **http://localhost:8000**

---

## 🌐 DEPLOYMENT KE HOSTING VIA TERMIUS

### Persiapan Awal (HANYA 1 KALI)

1. **Download Termius** (Jika belum ada)
   - Windows: https://termius.com/
   - Install aplikasi

2. **Buka Termius dan setup SSH connection:**
   - Klik "+" → "New Host"
   - Isi data hosting Anda:
     - **Hostname**: IP atau domain hosting
     - **Username**: User SSH (biasanya `root` atau nama lain)
     - **Password**: SSH password
     - **Port**: 22 (default)
   - Save connection

### Cara Deploy Setiap Kali Ada Perubahan

#### Langkah 1: Via Termius (Terminal SSH)
```bash
# 1. Masuk ke folder project di hosting
cd /home/bumisultan/public_html  # atau path hosting Anda

# 2. Pull kode terbaru dari Git (jika menggunakan Git)
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Build frontend
npm install --production
npm run build

# 5. Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# 6. Run migrations (jika ada perubahan DB)
php artisan migrate --force

# 7. Optimize
php artisan optimize

# 8. Set permissions (important!)
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

#### Langkah 2: Verifikasi Deployment
```bash
# Check Laravel status
php artisan tinker
>>> echo('OK');
```

### Cara Cepat dengan Script SSH (Opsional)

Jika hosting mendukung custom script, buat file `deploy.sh` di hosting:

```bash
#!/bin/bash
cd /home/bumisultan/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
npm install --production && npm run build
php artisan cache:clear && php artisan config:cache && php artisan route:cache
php artisan migrate --force
chmod -R 775 storage/ bootstrap/cache/
echo "✅ Deployment selesai!"
```

Kemudian jalankan via Termius:
```bash
bash deploy.sh
```

---

## 📋 FULL DEPLOYMENT CHECKLIST

### Sebelum Deploy:
- [ ] Pastikan semua kode sudah di-commit ke Git
- [ ] Backup database hosting
- [ ] Backup file hosting
- [ ] Catat versi aplikasi lama (di VERSION file)

### Deploy Lokal:
- [ ] Jalankan `deploy-local.bat`
- [ ] Test semua fitur yang berubah
- [ ] Cek console browser (F12) untuk errors
- [ ] Cek network tab untuk failed requests

### Deploy Hosting:
- [ ] SSH connect via Termius
- [ ] Pull kode terbaru
- [ ] Update dependencies
- [ ] Build frontend
- [ ] Run migrations
- [ ] Clear caches
- [ ] Test di production URL
- [ ] Monitor error logs

### Post Deployment:
- [ ] Test login & basic features
- [ ] Cek email notifications (jika ada)
- [ ] Monitor server logs
- [ ] Cek file permissions
- [ ] Update VERSION file

---

## 🔧 TROUBLESHOOTING

### Masalah: Script batch tidak berjalan
**Solusi:**
```powershell
# Buka PowerShell as Administrator
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Masalah: Composer update lambat
**Solusi:**
```bash
composer install --no-dev  # Lebih cepat
composer dump-autoload -o   # Optimize autoloader
```

### Masalah: Vite build error
**Solusi:**
```bash
npm cache clean --force
npm install
npm run build
```

### Masalah: Migration error di hosting
**Solusi:**
```bash
php artisan migrate:rollback
php artisan migrate
```

### Masalah: Permission denied di hosting
**Solusi:**
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data /home/bumisultan/public_html
```

### Masalah: Cache tidak clear
**Solusi:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
rm -rf bootstrap/cache/*
```

---

## 📊 INFORMASI VERSI

**Current Version:** Lihat file `VERSION`

**Changelog locations:**
- Database: `docs/ALUR_UPDATE.md`
- System Changes: `docs/DESIGN_CHANGES_SUMMARY.txt`
- Activity Points: `docs/INTEGRASI_ACTIVITY_POIN_KPI.md`

---

## ⚠️ PENTING!

1. **Jangan deploy langsung ke production tanpa test lokal!**
2. **Selalu backup database sebelum migration!**
3. **Test semua fitur critical setelah deployment!**
4. **Monitor error logs dalam 1 jam pertama!**
5. **Keep VERSION file updated!**

---

## 📞 QUICK REFERENCE

| Task | Command |
|------|---------|
| Deploy Lokal | `deploy-local.bat` |
| Dev Server | `php artisan serve` |
| Check Status | `php artisan tinker` |
| View Logs | `tail -f storage/logs/laravel.log` (hosting) |
| SSH Connect | Buka Termius → Select host → Connect |
| Build Frontend | `npm run build` |
| Clear All Cache | `php artisan optimize:clear` |
| Run Migrations | `php artisan migrate` |

---

**Last Updated:** 2026-06-08
**Created for:** Bumi Sultan App v2
