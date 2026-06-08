# INTEGRASI POIN AKTIVITAS DENGAN KPI ASSESSMENT (VERSI SIMPLE)

## 📋 OVERVIEW

**Fitur sederhana**: Saat karyawan input aktivitas, sistem otomatis generate poin yang langsung masuk ke KPI assessment mereka.

---

## 🎯 ALUR SISTEM (SIMPLE)

```
KARYAWAN INPUT AKTIVITAS
    ↓ (form tetap sama, tidak ada perubahan)
SISTEM AUTO-GENERATE POIN
    ↓ (berdasarkan deskripsi + foto)
POIN OTOMATIS MASUK KPI
    ↓ 
GRADE KPI BERUBAH OTOMATIS
```

---

## ⚙️ BAGAIMANA SISTEM MENGHITUNG POIN

### Formula Auto-Poin:
```
Poin = MIN( (word_count / 10) + photo_bonus, 100 )

Contoh:
- Aktivitas: "Meeting dengan klien untuk diskusi project terbaru" (8 kata)
  Poin = MIN((8/10) + 0, 100) = 0.8 poin

- Aktivitas sama + ADA FOTO:
  Poin = MIN((8/10) + 20, 100) = 20.8 poin
```

### Penjelasan:
- **Word Count / 10**: Setiap 10 kata = 1 poin
- **Photo Bonus**: Jika ada foto = +20 poin bonus
- **Max 100**: Poin tidak bisa lebih dari 100

---

## 📊 DATABASE SCHEMA

Kolom baru di tabel `aktivitas_karyawan`:

```sql
poin          DECIMAL(12,2)    DEFAULT 0
tipe_poin     ENUM('auto')     DEFAULT 'auto'
poin_set_at   TIMESTAMP        (waktu poin digenerate)
```

---

## 🎮 CARA PENGGUNAAN

### Untuk Karyawan:
1. Buka: Menu Aktivitas Karyawan → Tambah Aktivitas
2. Isi: **Deskripsi**, **Lokasi**, **Foto** (opsional)
3. **SELESAI!** Poin otomatis digenerate oleh sistem
4. Poin langsung masuk ke KPI Anda

**Catatan**: Form aktivitas **TETAP SAMA**, tidak ada input poin manual.

### Untuk Admin:
1. Setup KPI Indicator dengan:
   - **Mode**: Auto
   - **Metric Source**: `activity_poin`
   - **Jenis Target**: max (semakin banyak poin semakin baik)
   - **Target**: 300-500 (contoh: target 400 poin per periode)
   - **Bobot**: 20-30% (contoh: 25%)

2. View KPI Employee → Poin aktivitas **OTOMATIS** masuk sebagai realisasi

---

## 🧮 CONTOH SKENARIO

### Periode KPI: Januari 2024

**Aktivitas yang dicatat:**

| Tanggal | Aktivitas | Kata | Foto | Poin |
|---------|-----------|------|------|------|
| 05/01 | "Meeting dengan tim sales membahas target Q1" | 8 | Tidak | 0.80 |
| 08/01 | "Workshop internal tentang sistem baru yang sangat detail dan comprehensive sekali" | 12 | Ya | 21.20 |
| 12/01 | "Client visit untuk presentasi produk dan negosiasi harga serta kondisi pembayaran yang baik" | 15 | Ya | 21.50 |

**Total Poin: 0.80 + 21.20 + 21.50 = 43.50 poin**

**KPI Indicator Setup:**
- Target: 400 poin
- Bobot: 25%

**Score Calculation:**
```
Score = (Realisasi / Target) × Bobot
Score = (43.50 / 400) × 25
Score = 0.10875 × 25
Score = 2.72 poin
```

**Grade**: Tergantung total score dari semua indicators

---

## ✅ SETUP CHECKLIST

- [ ] Run migration: `php artisan migrate`
- [ ] Buat KPI Indicator dengan metric_source = 'activity_poin'
- [ ] Set Target & Bobot yang sesuai
- [ ] Pastikan total bobot semua indicator = 100%
- [ ] Karyawan mulai input aktivitas
- [ ] Admin review KPI → Poin otomatis masuk

---

## 🔄 INTEGRASI KE KPI

### 1. Controller Auto-Calculate:
File: `AktivitasKaryawanController.php`
```php
// Saat save/update, otomatis hitung poin
$aktivitas = new AktivitasKaryawan($data);
$aktivitas->calculateAutomaticPoin();
$aktivitas->save();
```

### 2. KPI Controller Pull Poin:
File: `KpiEmployeeController.php`
```php
// Method calculateAutomatedRealization() support:
case 'activity_poin':
    return $service->calculateActivityPoints($nik, $start, $end);
```

### 3. Service Kalkulasi:
File: `KpiActivityPointsService.php`
```php
// Calculate total points from activities dalam periode
calculateActivityPoints($nik, $startDate, $endDate): float
```

---

## 📁 FILES YANG DIMODIFIKASI

**Dibuat:**
- `database/migrations/2026_06_08_120000_add_poin_to_aktivitas_karyawan_table.php`
- `app/Services/KpiActivityPointsService.php`
- `docs/INTEGRASI_ACTIVITY_POIN_KPI_SIMPLE.md` (ini)

**Dimodifikasi:**
- `app/Models/AktivitasKaryawan.php` - Tambah calculateAutomaticPoin()
- `app/Http/Controllers/AktivitasKaryawanController.php` - Auto-calculate saat store/update
- `app/Http/Controllers/KpiEmployeeController.php` - Support activity_poin metric
- `resources/views/aktivitaskaryawan/create.blade.php` - Form tetap sama
- `resources/views/aktivitaskaryawan/edit.blade.php` - Form tetap sama

---

## 🚀 DEPLOYMENT

```bash
# Step 1: Run migration
php artisan migrate

# Step 2: Setup KPI Indicator di aplikasi
# Menu: Manajemen KPI → Indikator KPI
# - Tambah indicator baru
# - Mode: Auto
# - Metric Source: activity_poin
# - Target: 400 (contoh)
# - Bobot: 25 (contoh)

# Step 3: Karyawan mulai input aktivitas
# Menu: Aktivitas Karyawan → Tambah Aktivitas
# - Fill deskripsi, lokasi, foto (optional)
# - Poin otomatis digenerate!

# Step 4: Admin lihat KPI
# Menu: Penilaian KPI → List Karyawan
# - Poin aktivitas otomatis masuk sebagai realisasi
# - Grade berubah otomatis
```

---

## 📝 NOTES PENTING

1. **Form Tetap Sama**: Tidak ada UI changes, karyawan input seperti biasa
2. **Otomatis 100%**: Poin selalu auto-generate, tidak ada pilihan manual
3. **Real-time**: Poin langsung masuk KPI saat aktivitas disimpan
4. **Re-calculate**: Jika aktivitas diedit, poin juga otomatis recalculate
5. **Hanya Deskripsi + Foto**: Poin hanya berdasarkan kata-kata dan ada tidaknya foto

---

## ⚡ QUICK START

### Hanya 3 langkah:

1. **Run migration:**
   ```bash
   php artisan migrate
   ```

2. **Setup KPI Indicator** dengan metric_source `activity_poin`

3. **Karyawan input aktivitas** → Poin otomatis masuk ke KPI! ✓

---

## 📞 CONTOH KPI INDICATOR CONFIG

### Indicator 1: Total Poin Aktivitas
```
Nama Indikator       : Produktivitas Aktivitas
Deskripsi            : Total poin aktivitas yang dicatat
Satuan               : Poin
Jenis Target         : max
Bobot                : 25
Target               : 400
Mode                 : auto
Metric Source        : activity_poin
```

### Indicator 2: (Contoh) Kehadiran
```
Nama Indikator       : Kehadiran
Deskripsi            : Jumlah hari hadir
Satuan               : Hari
Jenis Target         : max
Bobot                : 30
Target               : 20
Mode                 : auto
Metric Source        : attendance_hadir
```

### Indicator 3: (Contoh) Target Sales
```
Nama Indikator       : Target Penjualan
Deskripsi            : Pencapaian target penjualan
Satuan               : Rupiah
Jenis Target         : max
Bobot                : 45
Target               : 100000000
Mode                 : manual
Metric Source        : (kosong - manual input)
```

**Total Bobot: 25 + 30 + 45 = 100% ✓**

---

Sistem sekarang **SIMPLE & CLEAN**! 🎉

