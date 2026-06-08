════════════════════════════════════════════════════════════════════════════════
  FULL AUTOMATION - LOKAL → GIT → HOSTING (ONE COMMAND!)
════════════════════════════════════════════════════════════════════════════════

Anda sekarang punya AUTOMATION SCRIPT yang otomatis:
✅ Deploy lokal (update deps, build, optimize)
✅ Commit ke Git
✅ Push ke GitHub

Setelah script selesai, tinggal pull di hosting via Termius!

════════════════════════════════════════════════════════════════════════════════
  OPSI 1: WINDOWS COMMAND PROMPT (Paling Mudah)
════════════════════════════════════════════════════════════════════════════════

LANGKAH PERTAMA KALI:
1. Setup Git di komputer Anda:
   - Download: https://git-scm.com/
   - Install dengan default settings
   - Restart Command Prompt

2. Setup Git credentials (HANYA 1x):
   Buka Command Prompt, jalankan:
   
   git config --global user.name "Nama Anda"
   git config --global user.email "email@example.com"

3. Setup GitHub SSH Key (HANYA 1x):
   (Biar push otomatis tanpa password)
   - Buka: https://github.com/settings/keys
   - Buat SSH key di komputer
   - Add ke GitHub
   (Atau ikuti: https://docs.github.com/en/authentication/connecting-to-github-with-ssh)

SETIAP KALI DEPLOY:
1. Edit file di VS Code
2. Buka Command Prompt
3. cd ke folder project:
   cd "c:\Users\usedr\Desktop\APLIKASI BUMI SULTAN TERBARU\presensiv2\presensigpsv2-main\gawe.bumisultan"
4. Jalankan:
   deploy-auto-full.bat
5. Masukkan pesan commit (atau Enter untuk auto)
6. Tunggu sampai selesai (3-5 menit)
7. Selesai! Kode sudah di GitHub ✅

KEMUDIAN DI HOSTING (TERMIUS):
1. Buka Termius
2. Connect ke hosting
3. Copy-paste commands ini:

   cd /home/bumisultan/public_html
   git pull origin main && composer install --no-dev && npm run build
   php artisan cache:clear && php artisan config:cache && php artisan route:cache
   chmod -R 775 storage/ bootstrap/cache/

4. Done! Perubahan sudah live di website ✅


════════════════════════════════════════════════════════════════════════════════
  OPSI 2: POWERSHELL (Advanced Users)
════════════════════════════════════════════════════════════════════════════════

Setup (HANYA 1x):
1. Buka PowerShell as Administrator
2. Jalankan:
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
3. Answer: Y

SETIAP KALI DEPLOY:
1. Buka PowerShell (regular, tidak perlu Admin)
2. cd ke folder project
3. Jalankan:
   .\deploy-auto-full.ps1
4. Ikuti instruksi
5. Selesai! ✅

ATAU dengan parameter:
   .\deploy-auto-full.ps1 -CommitMessage "Update: fitur baru"
   .\deploy-auto-full.ps1 -DryRun              # Test tanpa benar-benar push


════════════════════════════════════════════════════════════════════════════════
  WORKFLOW LENGKAP (REAL-WORLD EXAMPLE)
════════════════════════════════════════════════════════════════════════════════

Skenario: Anda ubah warna tombol dari biru jadi merah

TAHAP 1: EDIT DI VS CODE
────────────────────────────────────────────────────────────────────────────
1. Buka file: resources/css/button.css
2. Ubah: color: blue → color: red
3. Save (Ctrl+S)

TAHAP 2: RUN AUTOMATION SCRIPT DI LOKAL
────────────────────────────────────────────────────────────────────────────
1. Buka Command Prompt
2. cd ke folder project
3. Jalankan: deploy-auto-full.bat
4. Ketik pesan commit: "Update: Ubah warna tombol jadi merah"
5. Tekan Enter
6. Tunggu script selesai (3-5 menit)
   - Script akan: test lokal, commit, push otomatis
   - No more manual git commands! ✅

TAHAP 3: PULL DI HOSTING VIA TERMIUS
────────────────────────────────────────────────────────────────────────────
1. Buka Termius
2. Connect ke hosting
3. Jalankan:
   cd /home/bumisultan/public_html && git pull origin main && npm run build
4. Tunggu selesai
5. Done! Website sudah updated dengan tombol merah ✅

TOTAL TIME: ~10 menit dari awal sampai live!


════════════════════════════════════════════════════════════════════════════════
  FITUR SCRIPT
════════════════════════════════════════════════════════════════════════════════

✅ Otomatis Deploy Lokal
   ├─ Update Composer dependencies
   ├─ Update NPM packages
   ├─ Build Vite frontend
   ├─ Clear Laravel cache
   └─ Optimize application

✅ Otomatis Git Operations
   ├─ Add semua files (git add .)
   ├─ Commit dengan custom message
   └─ Push ke GitHub otomatis

✅ Logging
   ├─ Log file untuk troubleshooting
   ├─ Colored output untuk readability
   └─ Error handling yang baik

✅ Verification
   ├─ Show git status sebelum commit
   ├─ Show recent commits setelah selesai
   └─ Show next steps untuk hosting


════════════════════════════════════════════════════════════════════════════════
  REQUIREMENTS SETUP
════════════════════════════════════════════════════════════════════════════════

Sebelum bisa pakai script, pastikan sudah install:

1. PHP 8.1+
   Download: https://www.php.net/downloads
   Check: php -v (di Command Prompt)

2. Node.js
   Download: https://nodejs.org/
   Check: node -v (di Command Prompt)

3. Git
   Download: https://git-scm.com/
   Check: git --version (di Command Prompt)

4. GitHub Account + Repository
   - Create account di: https://github.com
   - Create repository untuk project
   - Clone ke lokal (sudah ada mungkin)

5. Setup Git Credentials (LOCAL):
   git config --global user.name "Nama Anda"
   git config --global user.email "email@anda.com"

6. (Optional) Setup SSH Key untuk auto-push:
   - Biar tidak perlu password setiap push
   - Guide: https://docs.github.com/en/authentication/connecting-to-github-with-ssh


════════════════════════════════════════════════════════════════════════════════
  TROUBLESHOOTING
════════════════════════════════════════════════════════════════════════════════

❌ ERROR: "Git tidak ditemukan"
✅ SOLUSI: Install Git dari https://git-scm.com/

❌ ERROR: "Git push failed"
✅ SOLUSI:
   - Cek internet connection
   - Cek git credentials: git config --list
   - Cek SSH key setup
   - Try manual: git push origin main

❌ ERROR: "NPM permission denied"
✅ SOLUSI:
   npm cache clean --force
   npm install

❌ ERROR: Script tidak jalan
✅ SOLUSI (PowerShell):
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

❌ ERROR: "No changes to commit"
✅ SOLUSI: Normal aja, berarti tidak ada perubahan file


════════════════════════════════════════════════════════════════════════════════
  QUICK COMMAND REFERENCE
════════════════════════════════════════════════════════════════════════════════

BATCH (CMD):
$ deploy-auto-full.bat        # Full automation

POWERSHELL:
$ .\deploy-auto-full.ps1                              # Full automation
$ .\deploy-auto-full.ps1 -CommitMessage "Update: ..." # Custom message
$ .\deploy-auto-full.ps1 -DryRun                      # Test tanpa push
$ .\deploy-auto-full.ps1 -SkipPush                    # Push manual

GIT MANUAL (Jika perlu):
$ git status                  # Check status
$ git add .                   # Add files
$ git commit -m "msg"        # Commit
$ git push origin main        # Push ke GitHub
$ git log --oneline -5       # Show recent commits


════════════════════════════════════════════════════════════════════════════════
  COMPLETE WORKFLOW DIAGRAM
════════════════════════════════════════════════════════════════════════════════

   LOKAL (Desktop)
   ┌─────────────────────────┐
   │ 1. Edit file di VS Code │
   │ 2. Save file            │
   └───────────┬─────────────┘
               │
               ↓
   ┌─────────────────────────────────────┐
   │ 3. Run deploy-auto-full.bat         │
   │    (ONE COMMAND!)                   │
   └───────────┬─────────────────────────┘
               │
               ├─ Deploy Lokal (auto)
               ├─ Git Add (auto)
               ├─ Git Commit (auto)
               └─ Git Push (auto)
               │
               ↓
   GitHub Repository
   ┌─────────────────────────┐
   │ Code pushed to GitHub   │
   │ Kode tersimpan & backup │
   └────────────┬────────────┘
                │
                ↓
   HOSTING (Production) - via Termius
   ┌──────────────────────────────┐
   │ 4. SSH ke hosting via Termius │
   │ 5. git pull origin main       │
   │ 6. npm run build              │
   │ 7. Cache clear                │
   └──────────────┬────────────────┘
                  │
                  ↓
   LIVE WEBSITE ✅
   ┌──────────────────────────────┐
   │ Perubahan sudah live online! │
   └──────────────────────────────┘


════════════════════════════════════════════════════════════════════════════════
  SUMMARY
════════════════════════════════════════════════════════════════════════════════

Sebelum: Manual → git add . → git commit → git push → pull di hosting
Sekarang: Sekali jalan script → SEMUA OTOMATIS ✅

Script yang dibuat:
✅ deploy-auto-full.bat  - untuk Windows CMD (mudah)
✅ deploy-auto-full.ps1  - untuk PowerShell (advanced)

Kapan pakai script:
→ Setiap ada perubahan kode
→ Tinggal jalankan script → semua otomatis
→ Terus pull di hosting via Termius
→ Done! Live ✅


════════════════════════════════════════════════════════════════════════════════

Mulai dari sekarang, workflow Anda:
1. Edit di VS Code
2. Jalankan: deploy-auto-full.bat
3. Tekan Enter setiap kali ada prompt
4. Done! Live di online ✅

Banyak lebih simple kan? 🚀

════════════════════════════════════════════════════════════════════════════════
