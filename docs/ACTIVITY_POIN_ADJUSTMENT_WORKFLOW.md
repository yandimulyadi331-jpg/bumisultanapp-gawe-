# Workflow Diagram - Admin Activity Point Adjustment

## System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    KARYAWAN WORKFLOW                             │
└─────────────────────────────────────────────────────────────────┘

    Karyawan Input Aktivitas
           ↓
    Deskripsi + Foto
           ↓
    ┌─────────────────────────────────────────┐
    │  Auto Calculate Poin                    │
    │  Formula: (word_count÷10) + photo(20)  │
    │  Max: 100                               │
    │  Stored as: tipe_poin = 'auto'         │
    └─────────────────────────────────────────┘
           ↓
    Poin Tersimpan di Database
    (aktivitas_karyawan.poin)


┌─────────────────────────────────────────────────────────────────┐
│                    ADMIN WORKFLOW (NEW!)                         │
└─────────────────────────────────────────────────────────────────┘

    Admin Buka Halaman KPI
           ↓
    Lihat Tabel Input Realisasi KPI
           ↓
    Cari Indikator dengan:
    - Mode: Auto
    - Metric Source: activity_poin
           ↓
    Klik "Lihat detail X aktivitas [Total Poin]"
           ↓
    ┌─────────────────────────────────────────┐
    │   EXPAND: Detail Aktivitas Karyawan     │
    │   ─────────────────────────────────────  │
    │   Tampil semua aktivitas dalam periode  │
    │   dengan poin auto-calculated           │
    └─────────────────────────────────────────┘
           ↓
    Untuk Setiap Aktivitas:
           ↓
    ┌─────────────────────────────────────────┐
    │   Poin (Auto):     15.00               │
    │   Poin Disesuaikan: [Input 0-100]      │
    │   Tipe: Auto / Manual + Adjusted by    │
    │   Action: [Revert] (jika sudah adjust)│
    └─────────────────────────────────────────┘
           ↓
    Admin Edit Poin Sesuai Kebutuhan
    (Bisa tambah/kurangi)
           ↓
    Klik "Simpan Perubahan Poin"
           ↓
    ┌─────────────────────────────────────────┐
    │   BACKEND PROCESSING                    │
    │   ─────────────────────────────────────  │
    │   1. Save semua poin baru ke DB        │
    │   2. Set tipe_poin = 'manual'          │
    │   3. Record: poin_adjusted_by          │
    │   4. Record: poin_adjusted_at          │
    │   5. Save: poin_original (audit trail) │
    └─────────────────────────────────────────┘
           ↓
    ┌─────────────────────────────────────────┐
    │   AUTOMATIC RECALCULATION               │
    │   ─────────────────────────────────────  │
    │   1. Hitung total poin aktivitas       │
    │   2. Update KpiDetail.realisasi         │
    │   3. Recalc skor: (realisasi÷target)  │
    │      × bobot                           │
    │   4. Hitung total_nilai KPI            │
    │   5. Update grade (A/B/C/D/E)         │
    └─────────────────────────────────────────┘
           ↓
    Success Alert + KPI Updated
           ↓
    Admin Klik "Simpan Realisasi" (Form KPI)
           ↓
    KPI Tersimpan dengan Poin Adjusted
    ✅ Done!
```

## Data Flow & Transformations

```
┌─────────────────────────────────────────────────────────────────┐
│ AKTIVITAS_KARYAWAN TABLE                                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ BEFORE Adjustment:                                              │
│ ├─ id: 1                                                        │
│ ├─ nik: 'E001'                                                  │
│ ├─ aktivitas: 'Menyelesaikan report Q1'                         │
│ ├─ poin: 25.00                                                  │
│ ├─ tipe_poin: 'auto'                                            │
│ ├─ poin_set_at: 2026-06-01 10:30:00                            │
│ ├─ poin_original: NULL  ← NEW                                   │
│ ├─ poin_adjusted_by: NULL  ← NEW                                │
│ └─ poin_adjusted_at: NULL  ← NEW                                │
│                                                                   │
│ AFTER Admin Adjustment (Admin set poin = 35):                  │
│ ├─ id: 1                                                        │
│ ├─ nik: 'E001'                                                  │
│ ├─ aktivitas: 'Menyelesaikan report Q1'                         │
│ ├─ poin: 35.00  ← CHANGED (increased)                           │
│ ├─ tipe_poin: 'manual'  ← CHANGED                               │
│ ├─ poin_set_at: 2026-06-08 14:50:00  ← UPDATED                │
│ ├─ poin_original: 25.00  ← NEW (saved for audit)               │
│ ├─ poin_adjusted_by: 'admin_user'  ← NEW                        │
│ └─ poin_adjusted_at: 2026-06-08 14:50:00  ← NEW               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ KPI_DETAILS TABLE                                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ BEFORE Adjustment:                                              │
│ ├─ id: 10                                                       │
│ ├─ kpi_employee_id: 1                                           │
│ ├─ realisasi: 58.00  ← (sum of all auto activities)            │
│ ├─ target: 500                                                  │
│ ├─ skor: 2.90  ← (58/500 * 25 = 2.90)                          │
│ └─ ...                                                           │
│                                                                   │
│ AFTER Bulk Adjustment:                                          │
│ ├─ id: 10                                                       │
│ ├─ kpi_employee_id: 1                                           │
│ ├─ realisasi: 75.00  ← RECALCULATED (new sum)                  │
│ ├─ target: 500                                                  │
│ ├─ skor: 3.75  ← RECALCULATED (75/500 * 25 = 3.75)            │
│ └─ ...                                                           │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ KPI_EMPLOYEES TABLE                                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ BEFORE Adjustment:                                              │
│ ├─ id: 1                                                        │
│ ├─ total_nilai: 65.20  ← (sum of all skor indicators)         │
│ ├─ grade: 'C'  ← (65.20 range)                                  │
│ └─ ...                                                           │
│                                                                   │
│ AFTER Adjustment & Recalculation:                               │
│ ├─ id: 1                                                        │
│ ├─ total_nilai: 68.50  ← RECALCULATED                           │
│ ├─ grade: 'C'  ← (68.50 range, grade A≥90,B≥80,C≥70,D≥60)     │
│ └─ ...                                                           │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## API Endpoints Used

```
┌─────────────────────────────────────────────────────────────────┐
│ API ENDPOINT: POST /api/activity-point/bulk-update              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ REQUEST:                                                        │
│ {                                                               │
│   "kpi_employee_id": 1,                                         │
│   "activities": [                                               │
│     { "id": 1, "poin": 35.00 },                                │
│     { "id": 2, "poin": 8.00 },                                 │
│     { "id": 3, "poin": 25.00 }                                 │
│   ]                                                             │
│ }                                                               │
│                                                                   │
│ RESPONSE:                                                       │
│ {                                                               │
│   "success": true,                                             │
│   "message": "Poin aktivitas berhasil diperbarui...",         │
│   "data": {                                                     │
│     "total_activity_points": 68.00,  ← New total             │
│     "total_nilai_kpi": 68.50,        ← KPI recalc            │
│     "grade": "C",                      ← Updated              │
│     "updated_count": 3                 ← How many saved       │
│   }                                                             │
│ }                                                               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ API ENDPOINT: POST /api/activity-point/{id}/revert              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ REQUEST: POST /api/activity-point/1/revert                      │
│                                                                   │
│ RESPONSE:                                                       │
│ {                                                               │
│   "success": true,                                             │
│   "message": "Poin aktivitas berhasil dikembalikan...",       │
│   "data": {                                                     │
│     "id": 1,                                                    │
│     "poin": 25.00,        ← Back to original                  │
│     "tipe_poin": "auto"   ← Back to auto                      │
│   }                                                             │
│ }                                                               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## UI Components Updated

```
┌─────────────────────────────────────────────────────────────────┐
│ ACTIVITY DETAILS TABLE (NEW UI)                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ ┌──┬──────────────────┬──────┬──────────┬─────┬──────┬──────┐   │
│ │No│ Aktivitas        │ Poin │ Disesuai │Type │Tanggal│Aksi │   │
│ ├──┼──────────────────┼──────┼──────────┼─────┼──────┼──────┤   │
│ │ 1│Report Q1 (80..)  │ 25.0 │ [25.00] │Auto │Jun 1 │      │   │
│ ├──┼──────────────────┼──────┼──────────┼─────┼──────┼──────┤   │
│ │ 2│Meeting client(30)│ 3.00 │ [3.00]  │Auto │Jun 2 │      │   │
│ ├──┼──────────────────┼──────┼──────────┼─────┼──────┼──────┤   │
│ │ 3│Revisi proj(100..)│ 30.0 │ [30.00] │Auto │Jun 3 │      │   │
│ └──┴──────────────────┴──────┴──────────┴─────┴──────┴──────┘   │
│                                                                   │
│ Total Poin: [58.00]  Rata-rata: [19.33]                         │
│                                                                   │
│ [Reset]  [Simpan Perubahan Poin]                                │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Validation & Error Handling

```
┌─────────────────────────────────────────────────────────────────┐
│ VALIDATION CHECKS                                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│ 1. Poin Range Check:                                            │
│    ✓ Min: 0                                                     │
│    ✓ Max: 100                                                   │
│    ✗ Invalid: -5 atau 150 → Error message                       │
│                                                                   │
│ 2. Permission Check:                                            │
│    ✓ User is admin or has 'kpi.transaction.update'             │
│    ✗ Karyawan user → Forbidden                                  │
│                                                                   │
│ 3. Status Check:                                                │
│    ✓ KPI status != 'approved'                                   │
│    ✗ KPI approved → Field readonly                              │
│                                                                   │
│ 4. Database Integrity:                                          │
│    ✓ Activity exists in DB                                      │
│    ✗ Non-existent ID → 404 Not Found                            │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

## Backward Compatibility

```
✅ Existing data NOT affected
   - Old activities remain as 'auto'
   - poin_original = NULL (means never adjusted)
   - Full backward compatibility

✅ Migration is reversible
   - Down migration removes new columns
   - Old data structure restored

✅ No breaking changes
   - Existing API endpoints still work
   - UI gracefully handles activities without new fields
```
