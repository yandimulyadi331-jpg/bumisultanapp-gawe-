════════════════════════════════════════════════════════════════════════════════
  PANDUAN SETUP TERMIUS UNTUK DEPLOYMENT HOSTING
════════════════════════════════════════════════════════════════════════════════

TERMIUS adalah SSH client yang memudahkan Anda connect dan deploy ke hosting.
Website: https://termius.com/

════════════════════════════════════════════════════════════════════════════════
  LANGKAH 1: INSTALL & SETUP TERMIUS
════════════════════════════════════════════════════════════════════════════════

1. Download & Install Termius:
   - Windows: https://termius.com/windows
   - Download installer
   - Run installer dan follow instruksi
   - Buat akun Termius (opsional tapi recommended untuk sync)

2. Buka Termius setelah instalasi

════════════════════════════════════════════════════════════════════════════════
  LANGKAH 2: TAMBAH SSH CONNECTION KE HOSTING
════════════════════════════════════════════════════════════════════════════════

1. Di Termius, klik "+" (new host)
2. Pilih "SSH"
3. Isi informasi hosting Anda:
   
   FIELD                NILAI
   ─────────────────────────────────────────────────
   Label               Bumi Sultan Hosting
   Address             [IP hosting atau domain]
   Username            [User SSH - biasanya 'root' atau 'ubuntu']
   Password            [SSH password Anda]
   Port                22 (default)
   
4. Klik "Save"

📌 CATATAN:
   - Label bisa apa saja (untuk identifikasi lokal)
   - Address: tanya kepada provider hosting
   - Username & Password: dari email hosting atau cpanel
   - Jika SSH tidak aktif, minta ke provider hosting

════════════════════════════════════════════════════════════════════════════════
  LANGKAH 3: CONNECT KE HOSTING
════════════════════════════════════════════════════════════════════════════════

1. Klik host "Bumi Sultan Hosting" di Termius
2. Klik "Connect"
3. Termius akan connect via SSH
4. Sekarang Anda bisa menjalankan commands di hosting

════════════════════════════════════════════════════════════════════════════════
  LANGKAH 4: DEPLOY PERUBAHAN KE HOSTING
════════════════════════════════════════════════════════════════════════════════

Setelah connect di Termius, copy-paste command berikut:
(JANGAN LUPA GANTI PATH SESUAI HOSTING ANDA!)

─────────────────────────────────────────────────────────────────────────────

# STEP 1: Navigate ke folder project
cd /home/bumisultan/public_html

# STEP 2: Pull kode terbaru (jika pakai Git)
git pull origin main

# STEP 3: Update dependencies
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# STEP 4: Clear & optimize
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# STEP 5: Database migration (jika ada)
php artisan migrate --force

# STEP 6: Permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# STEP 7: Done!
echo "✅ Deployment Selesai!"

─────────────────────────────────────────────────────────────────────────────

🎯 TIPS:
- Copy satu per satu jika tidak yakin
- Tunggu setiap command selesai sebelum yang berikutnya
- Jika ada error, baca pesan error dengan teliti
- Monitor: tail -f storage/logs/laravel.log (untuk cek error realtime)

════════════════════════════════════════════════════════════════════════════════
  COMMAND REFERENSI TERMIUS
════════════════════════════════════════════════════════════════════════════════

NAVIGASI:
  cd /path/to/folder        → Masuk folder
  ls                        → List files
  pwd                       → Print working directory
  mkdir folder-name         → Buat folder

GIT:
  git pull origin main      → Pull kode terbaru
  git status                → Cek status git
  git log                   → Lihat commit history

PHP ARTISAN:
  php artisan migrate       → Run migrations
  php artisan cache:clear   → Clear cache
  php artisan tinker        → Interactive shell

NPM:
  npm run build             → Build frontend
  npm install               → Install packages

UTILITIES:
  tail -f file.log          → Follow log file
  htop                      → Monitor system
  df -h                     → Check disk space
  free -h                   → Check RAM

════════════════════════════════════════════════════════════════════════════════
  TROUBLESHOOTING TERMIUS
════════════════════════════════════════════════════════════════════════════════

MASALAH: "Connection refused"
SOLUSI:
  - Cek IP hosting benar?
  - SSH port aktif di hosting?
  - Username & password benar?
  - Contact provider hosting jika masih error

MASALAH: "Permission denied"
SOLUSI:
  - Gunakan user dengan privileges yang cukup
  - Atau gunakan: sudo command-name
  - Cek: whoami (untuk lihat current user)

MASALAH: "Command not found"
SOLUSI:
  - Pastikan sudah cd ke folder yang benar
  - Cek path: pwd
  - Cek apakah PHP/Node installed: php -v, node -v

MASALAH: Connection putus di tengah deployment
SOLUSI:
  - Reconect di Termius
  - Jalankan lagi command dari awal
  - Atau check status: php artisan migrate:status

════════════════════════════════════════════════════════════════════════════════
  WORKFLOW DEPLOYMENT LENGKAP
════════════════════════════════════════════════════════════════════════════════

1. ✅ TEST LOKAL DULU
   - Buka Command Prompt di folder project
   - Jalankan: deploy-local.bat
   - Test semua fitur baru di: http://localhost:8000
   - Pastikan tidak ada error

2. ✅ BACKUP HOSTING
   - SSH ke hosting via Termius
   - Backup DB: mysqldump -u user -p database > backup.sql
   - Backup files: tar -czf backup.tar.gz /path/to/project

3. ✅ DEPLOY KE HOSTING
   - Connect via Termius
   - Jalankan deployment commands (lihat LANGKAH 4)
   - Tunggu hingga selesai

4. ✅ VERIFIKASI
   - Buka website di browser
   - Test login & fitur utama
   - Cek error logs: tail -f storage/logs/laravel.log
   - Monitor 1-2 jam pertama

5. ✅ ROLLBACK JIKA PERLU
   - Restore dari backup
   - Atau git checkout ke versi sebelumnya

════════════════════════════════════════════════════════════════════════════════
  INFORMASI KONTAK HOSTING
════════════════════════════════════════════════════════════════════════════════

Isi informasi hosting Anda di sini untuk referensi cepat:

Provider:           ________________________
IP/Domain:          ________________________
SSH Username:       ________________________
SSH Password:       ________________________
SSH Port:           ________________________
Project Path:       ________________________
Database Name:      ________________________
Database User:      ________________________
Database Password:  ________________________
FTP/SFTP Info:      ________________________
Support Email:      ________________________

════════════════════════════════════════════════════════════════════════════════
  TERMIUS TIPS & TRICKS
════════════════════════════════════════════════════════════════════════════════

1. Save commands yang sering digunakan:
   - Pilih command di history
   - Klik "+" untuk save as snippet
   - Gunakan lagi dengan mudah

2. Batch commands dengan script:
   - Buat file: deploy.sh
   - Isi dengan commands
   - Jalankan: bash deploy.sh

3. Sync preferences di Termius:
   - Settings → Account
   - Login dengan Termius account
   - Preferences auto-sync across devices

4. Keyboard shortcuts:
   - Ctrl+A = Go to beginning of line
   - Ctrl+E = Go to end of line
   - Ctrl+R = Search command history
   - Tab = Auto-complete

════════════════════════════════════════════════════════════════════════════════
  SECURITY TIPS
════════════════════════════════════════════════════════════════════════════════

⚠️  PENTING!

1. Jangan share password SSH
2. Gunakan SSH key jika memungkinkan (lebih aman)
3. Jangan jalankan delete/rm command tanpa yakin
4. Selalu backup sebelum deploy
5. Monitor error logs setelah deploy
6. Jangan biarkan .env file dengan credentials di Git
7. Gunakan HTTPS untuk semua koneksi

════════════════════════════════════════════════════════════════════════════════

Untuk bantuan lebih lanjut:
- Baca DEPLOYMENT_GUIDE.md
- Lihat quick-deploy-steps.txt untuk referensi cepat
- Jalankan deploy-local.bat untuk deployment lokal

Generated: 2026-06-08
Updated for: Bumi Sultan App v2
