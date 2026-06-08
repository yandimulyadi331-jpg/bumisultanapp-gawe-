# RENCANA PROYEK: INDIKATOR PENILAIAN AKTIVITAS KPI - MODE OTOMATIS

## 📋 RINGKASAN PROYEK

**Objective**: Membuat KPI Indicator untuk "Penilaian Aktivitas" dengan mode otomatis yang mengambil data dari menu aktivitas karyawan

**Sumber Data**:
1. **Total Poin Aktivitas** (sudah ada)
2. **Jumlah Aktivitas** (sudah ada)
3. **Nilai/Kualitas Aktivitas** (baru - perlu ditambah)

**Timeline**: 3-5 hari kerja
**Kompleksitas**: Medium

---

## 🎯 ANALISIS SISTEM SAAT INI

### A. Struktur KPI Yang Sudah Ada

```
KPI Indicator (Header)
├── kpi_indicator_id
├── kode_jabatan
├── kode_dept
└── KPI Indicator Details (banyak)
    ├── id
    ├── nama_indikator
    ├── satuan
    ├── jenis_target (max/min)
    ├── bobot
    ├── target
    ├── mode: 'manual' | 'auto'
    └── metric_source: (untuk auto)
```

### B. Metric Sources yang Sudah Tersedia

**Attendance-based** (dari tabel Presensi):
- `attendance_sakit` → COUNT hari sakit
- `attendance_izin` → COUNT hari izin
- `attendance_alpa` → COUNT hari alpa
- `attendance_cuti` → COUNT hari cuti
- `attendance_hadir` → COUNT hari hadir
- `attendance_terlambat` → COUNT hari terlambat

**Activity-based** (dari tabel AktivitasKaryawan):
- `activity_poin` → SUM(poin) - Total poin aktivitas
- `activity_count` → COUNT - Jumlah aktivitas

### C. Alur Kalkulasi Otomatis

```
KpiEmployeeController::show() / update()
    ↓
Untuk setiap detail dengan mode='auto':
    ↓
calculateAutomatedRealization($kpi_employee, $metric_source)
    ↓
Switch $metric_source:
    - Case 'activity_poin': SUM poin dari AktivitasKaryawan
    - Case 'activity_count': COUNT aktivitas
    - Case attendance_*: COUNT dari Presensi
    ↓
Return $realisasi (nilai numerik)
    ↓
Update KpiDetail.realisasi
    ↓
Calculate Score: (realisasi / target) × bobot
```

---

## 🔍 YANG PERLU DITAMBAHKAN

### 1. **Field Baru di Tabel `aktivitas_karyawan`** ⭐ PHASE 1

Field untuk menangkap "nilai" atau "kualitas" aktivitas:

```sql
ALTER TABLE aktivitas_karyawan ADD COLUMN (
    -- Untuk input manual nilai/rating (opsional)
    nilai INT DEFAULT NULL COMMENT 'Nilai/Rating aktivitas (1-5 atau 1-10)',
    
    -- Untuk tracking approval workflow
    status_aktivitas ENUM('draft', 'pending', 'approved', 'rejected') DEFAULT 'draft',
    
    -- Admin notes
    catatan_admin VARCHAR(255) NULL,
    
    -- Approval tracking
    disetujui_oleh VARCHAR(50) NULL,
    tanggal_approval TIMESTAMP NULL
);

-- Index untuk query performa
CREATE INDEX idx_status_aktivitas ON aktivitas_karyawan(nik, status_aktivitas, created_at);
CREATE INDEX idx_nilai_aktivitas ON aktivitas_karyawan(nik, nilai, created_at);
```

### 2. **Model Enhancement** ⭐ PHASE 1

File: `app/Models/AktivitasKaryawan.php`

```php
// Tambahkan method baru
public function calculateActivityQuality($period_start, $period_end)
{
    // Rata-rata nilai aktivitas yang approved
    return self::where('nik', $this->nik)
        ->whereBetween('created_at', [$period_start, $period_end])
        ->where('status_aktivitas', 'approved')
        ->avg('nilai') ?? 0;
}

public function getActivityMetrics($period_start, $period_end)
{
    return [
        'total_poin' => self::where('nik', $this->nik)
            ->whereBetween('created_at', [$period_start, $period_end])
            ->sum('poin'),
        
        'total_count' => self::where('nik', $this->nik)
            ->whereBetween('created_at', [$period_start, $period_end])
            ->count(),
        
        'average_quality' => self::where('nik', $this->nik)
            ->whereBetween('created_at', [$period_start, $period_end])
            ->where('status_aktivitas', 'approved')
            ->avg('nilai') ?? 0,
        
        'total_approved' => self::where('nik', $this->nik)
            ->whereBetween('created_at', [$period_start, $period_end])
            ->where('status_aktivitas', 'approved')
            ->count(),
    ];
}
```

### 3. **New Metric Sources** ⭐ PHASE 1

Di `KpiEmployeeController::calculateAutomatedRealization()`, tambahkan cases baru:

```php
case 'activity_quality_avg':
    // Rata-rata nilai/rating aktivitas
    return AktivitasKaryawan::where('nik', $nik)
        ->whereBetween('created_at', [$start, $end])
        ->where('status_aktivitas', 'approved')
        ->avg('nilai') ?? 0;

case 'activity_approved':
    // Jumlah aktivitas yang sudah di-approve
    return AktivitasKaryawan::where('nik', $nik)
        ->whereBetween('created_at', [$start, $end])
        ->where('status_aktivitas', 'approved')
        ->count();

case 'activity_value_total':
    // Total nilai (poin × rating rata-rata)
    $activities = AktivitasKaryawan::where('nik', $nik)
        ->whereBetween('created_at', [$start, $end])
        ->where('status_aktivitas', 'approved')
        ->get();
    
    $total_value = 0;
    foreach ($activities as $act) {
        $rating = $act->nilai ?? 3; // Default 3 jika tidak ada rating
        $total_value += ($act->poin * $rating / 5); // Normalize ke skala 5
    }
    
    return round($total_value, 2);
```

### 4. **Update UI - Dropdown Metric Source** ⭐ PHASE 2

File: `resources/views/kpi/indicators/create.blade.php` & `edit.blade.php`

```blade
<select class="form-select" id="metric_source">
    <option value="">Pilih Sumber Data</option>
    
    <!-- Group: Attendance -->
    <optgroup label="📋 Kehadiran">
        <option value="attendance_sakit">Total Sakit (Hari)</option>
        <option value="attendance_izin">Total Izin (Hari)</option>
        <option value="attendance_alpa">Total Alpa (Hari)</option>
        <option value="attendance_cuti">Total Cuti (Hari)</option>
        <option value="attendance_hadir">Total Kehadiran (Hari)</option>
        <option value="attendance_terlambat">Total Terlambat (Hari)</option>
    </optgroup>
    
    <!-- Group: Activity -->
    <optgroup label="🎯 Aktivitas">
        <option value="activity_poin">Total Poin Aktivitas</option>
        <option value="activity_count">Jumlah Aktivitas</option>
        <option value="activity_approved">Jumlah Aktivitas Approved</option>
        <option value="activity_quality_avg">Rata-rata Nilai Aktivitas</option>
        <option value="activity_value_total">Total Nilai Aktivitas (Poin × Rating)</option>
    </optgroup>
</select>
```

### 5. **Activity Management Feature** ⭐ PHASE 2

Tambahkan UI untuk admin approve/reject aktivitas:

**New View**: `resources/views/kpi/activities/approval-list.blade.php`

Fitur:
- List semua aktivitas dalam periode (dengan filter)
- Kolom: NIK, Aktivitas, Poin Auto, Rating Input, Status, Aksi
- Action buttons: Approve, Reject, Lihat Detail
- Bulk approve/reject

**New Controller**: `AktivitasApprovalController@index`, `@approve`, `@reject`

---

## 📊 CONTOH IMPLEMENTASI

### Scenario: KPI "Produktivitas & Kualitas Aktivitas"

**Setup Indikator**:

```
Nama Indikator: Produktivitas & Kualitas Aktivitas
Deskripsi: Penilaian berdasarkan jumlah dan kualitas aktivitas
Satuan: Poin
Jenis Target: Max
Bobot: 30%
Target: 400
Mode: Auto
Metric Source: activity_value_total (poin × rating)
```

**Data Karyawan dalam Periode Agustus**:

| Tanggal | Aktivitas | Poin Auto | Rating (1-5) | Nilai Terbobot |
|---------|-----------|-----------|--------------|----------------|
| 01/08 | Meeting klien | 15 | 5 | 15.00 |
| 05/08 | Workshop training | 25 | 4 | 20.00 |
| 10/08 | Presentasi project | 30 | 5 | 30.00 |
| 15/08 | Report analisis | 20 | 3 | 12.00 |
| 20/08 | Follow-up project | 18 | 4 | 14.40 |
| | **TOTAL** | **108** | - | **91.40** |

**Calculation**:
```
Metric Source: activity_value_total
Realisasi: 91.40
Target: 400
Score: (91.40 / 400) × 30 = 6.86 (dari 30 poin)
```

---

## 📋 TASK BREAKDOWN

### PHASE 1: Backend Foundation (2 hari)

- [ ] **Task 1.1**: Create migration untuk tambah kolom di `aktivitas_karyawan`
  - Kolom baru: nilai, status_aktivitas, catatan_admin, disetujui_oleh, tanggal_approval
  - Create indexes untuk performa
  - File: `database/migrations/2026_XX_XX_XXXXXX_add_nilai_columns_to_aktivitas_karyawan.php`

- [ ] **Task 1.2**: Update `AktivitasKaryawan` model
  - Add relationships & scopes
  - Add `getActivityMetrics()` method
  - Add `calculateActivityQuality()` method
  - File: `app/Models/AktivitasKaryawan.php`

- [ ] **Task 1.3**: Expand `calculateAutomatedRealization()` method
  - Add 5 new metric_source cases
  - File: `app/Http/Controllers/KpiEmployeeController.php`

- [ ] **Task 1.4**: Update seeder data
  - Add sample KPI indicators dengan new metric sources
  - File: `database/seeders/AsfindokpiSeeder.php` atau `KpiDummyDataSeeder.php`

### PHASE 2: Frontend & Admin Features (2 hari)

- [ ] **Task 2.1**: Update KPI Indicator Create/Edit views
  - Add optgroup di dropdown metric_source
  - Add help text untuk setiap sumber data
  - Files:
    - `resources/views/kpi/indicators/create.blade.php`
    - `resources/views/kpi/indicators/edit.blade.php`

- [ ] **Task 2.2**: Create Activity Approval Management
  - New view: list aktivitas dengan status
  - New controller: `AktivitasApprovalController`
  - Fitur: approve, reject, add rating
  - Files:
    - `app/Http/Controllers/AktivitasApprovalController.php`
    - `resources/views/activities/approval-list.blade.php`
    - `routes/web.php` - add routes

- [ ] **Task 2.3**: Add menu item di navigation
  - Menu: "Persetujuan Aktivitas" / "Activity Approval"
  - Link ke approval list
  - File: `resources/views/layouts/navigation.blade.php`

### PHASE 3: Testing & Optimization (1 hari)

- [ ] **Task 3.1**: Create unit tests
  - Test calculateActivityMetrics()
  - Test new metric_source calculations
  - File: `tests/Unit/KpiActivityMetricsTest.php`

- [ ] **Task 3.2**: End-to-end testing
  - Setup KPI dengan metric_source baru
  - Input aktivitas → auto poin
  - Admin approve → nilai updated
  - KPI realisasi berubah otomatis

- [ ] **Task 3.3**: Documentation update
  - Update docs dengan contoh setup
  - Create user guide untuk admin approval feature
  - File: `docs/INDIKATOR_AKTIVITAS_GUIDE.md`

---

## 🗂️ FILES YANG AKAN DIUBAH/DIBUAT

### Existing Files (Modify):
1. `app/Models/AktivitasKaryawan.php` - Add methods
2. `app/Http/Controllers/KpiEmployeeController.php` - Add metric_source cases
3. `resources/views/kpi/indicators/create.blade.php` - Update dropdown
4. `resources/views/kpi/indicators/edit.blade.php` - Update dropdown
5. `database/seeders/AsfindokpiSeeder.php` - Add sample data
6. `routes/web.php` - Add new routes

### New Files (Create):
1. `database/migrations/2026_XX_XX_XXXXXX_add_nilai_columns_to_aktivitas_karyawan.php`
2. `app/Http/Controllers/AktivitasApprovalController.php`
3. `resources/views/activities/approval-list.blade.php`
4. `resources/views/activities/activity-detail-modal.blade.php`
5. `tests/Unit/KpiActivityMetricsTest.php`
6. `docs/INDIKATOR_AKTIVITAS_GUIDE.md`

---

## 💡 TECHNICAL NOTES

### 1. Metric Source Naming Convention
```
pattern: {module}_{metric}_{aggregation?}
- activity_poin           → SUM of poin
- activity_count          → COUNT of activities
- activity_quality_avg    → AVG of rating/nilai
- activity_approved       → COUNT of approved only
- activity_value_total    → WEIGHTED SUM
```

### 2. Rating/Nilai Skala
Recommended: **1-5 scale** (common di HR)
- 1 = Poor / Jelek
- 2 = Below Average / Di Bawah Rata-rata
- 3 = Average / Rata-rata
- 4 = Good / Baik
- 5 = Excellent / Sangat Baik

### 3. Performance Considerations
- Add indexes: `(nik, status_aktivitas, created_at)` dan `(nik, nilai, created_at)`
- Cache KPI calculations jika ada banyak aktivitas
- Consider: Batch approval untuk bulk reject/approve

### 4. Backward Compatibility
- New fields di AktivitasKaryawan adalah **nullable** (tidak mandatory)
- Existing KPI indicators tetap berjalan
- New metric sources bisa dipilih optional

---

## 🚀 DEPLOYMENT STEPS

```bash
# 1. Create & run migration
php artisan make:migration add_nilai_columns_to_aktivitas_karyawan
php artisan migrate

# 2. Clear cache
php artisan cache:clear
php artisan config:cache

# 3. Seed sample data (optional)
php artisan db:seed --class=AsfindokpiSeeder

# 4. Run tests
php artisan test

# 5. Test di local
# - Buat KPI Indicator dengan metric_source baru
# - Input aktivitas → verify poin auto-calculate
# - Approve aktivitas → verify status berubah
# - Lihat KPI → verify realisasi update otomatis
```

---

## 📝 SUCCESS CRITERIA

✅ KPI Indicator bisa dibuat dengan mode "Auto" dan metric_source "activity_value_total"
✅ Saat karyawan input aktivitas, poin auto-generate (existing)
✅ Admin bisa approve/reject aktivitas dengan rating
✅ KPI realisasi berubah otomatis saat aktivitas di-approve
✅ Dropdown metric_source menampilkan semua opsi dengan kategori
✅ Unit tests untuk kalkulasi metrics pass 100%
✅ Documentation lengkap dengan contoh setup

---

## 📞 QUESTIONS FOR CLARIFICATION

Sebelum mulai implementasi, perlu tanya user:

1. **Skala Rating**: 1-5 atau 1-10? Mandatory atau optional?
2. **Workflow Approval**: Siapa saja yang bisa approve? (HR, Manager, Admin KPI)
3. **Auto-Rating**: Apakah sistem bisa auto-rate berdasarkan poin? (contoh: poin > 30 = 5)
4. **Retention**: Berapa lama history aktivitas disimpan?
5. **Notification**: Butuh notifikasi saat aktivitas di-reject?

---

**Status**: Ready for Review & Implementation
**Priority**: High
**Owner**: Dev Team
