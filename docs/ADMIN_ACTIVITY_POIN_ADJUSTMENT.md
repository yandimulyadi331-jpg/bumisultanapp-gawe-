# Fitur Admin Adjustment Poin Aktivitas KPI

## Ringkasan Fitur

Fitur ini memungkinkan **admin/supervisor** untuk menyesuaikan poin aktivitas karyawan sebelum KPI di-approve. Poin yang sudah disesuaikan akan otomatis mempengaruhi nilai KPI akhir.

## Konteks Poin Aktivitas

### Bagaimana Sistem Bekerja:

1. **Karyawan Input Aktivitas**
   - Karyawan membuka menu Aktivitas Karyawan → Tambah Aktivitas
   - Input: Deskripsi (minimal 10 kata) + Foto (optional)
   - Sistem otomatis menghitung poin: `(word_count ÷ 10) + photo_bonus (20), max 100`

2. **Poin Tersimpan di Database**
   - Poin disimpan dengan `tipe_poin = 'auto'` (artinya otomatis dari formula)
   - Setiap aktivitas punya poin individual

3. **Tampil di Halaman KPI**
   - Saat admin membuka halaman KPI detail karyawan
   - Aktivitas-aktivitas akan tampil di bagian "Detail Aktivitas"
   - Admin dapat melihat total poin dari semua aktivitas

4. **Admin Bisa Menyesuaikan Poin**
   - **BARU**: Admin dapat mengubah nilai poin setiap aktivitas
   - Perubahan dilakukan sebelum KPI di-approve
   - Poin yang sudah disesuaikan disimpan dengan `tipe_poin = 'manual'`

5. **KPI Otomatis Dihitung Ulang**
   - Total poin aktivitas (yang sudah disesuaikan) akan digunakan untuk realisasi KPI
   - Nilai KPI akhir otomatis dihitung ulang
   - Grade (A/B/C/D/E) otomatis diupdate

---

## Cara Penggunaan

### Setup Admin (KPI Indicator)

**Prasyarat**: Admin harus sudah setup KPI Indicator dengan:
- **Mode**: "Auto" 
- **Metric Source**: "activity_poin"
- **Target**: Sesuaikan dengan kebutuhan (contoh: 500)
- **Bobot**: Persentase untuk KPI (contoh: 20%)

**Langkah Setup**:
1. Menu KPI → Indikator KPI
2. Buat indikator baru (misal: "Produktivitas Aktivitas")
3. Set Mode = "Auto", Metric Source = "activity_poin"
4. Simpan indikator
5. Assign ke karyawan di menu KPI → Penetapan Target

### Penggunaan - Admin Lihat Aktivitas & Sesuaikan Poin

**Langkah 1: Buka Halaman KPI Detail Karyawan**
1. Menu KPI → Transaksi KPI
2. Pilih karyawan & periode yang ingin dilihat
3. Klik "Lihat Detail"

**Langkah 2: Lihat Detail Aktivitas**
1. Di tabel "Input Realisasi KPI"
2. Cari baris indikator dengan mode "Auto" dan metric source "activity_poin"
3. Di bawah baris tersebut ada tombol "Lihat detail X aktivitas [Total Poin]"
4. Klik tombol untuk expand bagian Detail Aktivitas

**Langkah 3: Sesuaikan Poin Jika Diperlukan**

Untuk setiap aktivitas yang ditampilkan:
- Kolom "Poin": Nilai poin otomatis dari sistem
- Kolom "Poin Disesuaikan": **INPUT FIELD** untuk mengubah poin
- Kolom "Tipe": Menunjukkan "Auto" atau "Manual"
- Kolom "Aksi": Tombol untuk kembalikan ke nilai original

**Contoh Perubahan Poin**:
```
Aktivitas: "Menyelesaikan project X dengan hasil sempurna"
Poin Original (Auto): 15.00 poin
Poin Disesuaikan (Admin): 25.00 poin  ← Admin meningkatkan karena hasil luar biasa

Aktivitas: "Aktivitas rutin standar"
Poin Original (Auto): 12.00 poin
Poin Disesuaikan (Admin): 8.00 poin   ← Admin menurunkan karena kualitas kurang
```

**Langkah 4: Simpan Perubahan**
1. Setelah menyesuaikan poin-poin aktivitas
2. Klik tombol "Simpan Perubahan Poin" (di bagian Detail Aktivitas)
3. Sistem akan:
   - Menyimpan poin baru
   - Menghitung total poin aktivitas
   - Mengupdate realisasi KPI
   - Recalculate skor KPI akhir
   - Mengupdate grade (A/B/C/D/E)

**Langkah 5: Simpan KPI Utama**
1. Klik "Simpan Realisasi" di bawah tabel KPI
2. KPI sudah tersimpan dengan poin aktivitas yang sudah disesuaikan

---

## Fitur Detail

### 1. Editable Poin Aktivitas

**Tampilan di Tabel Detail Aktivitas**:
- Kolom "Poin": Menampilkan nilai poin saat ini
  - Badge hijau jika masih auto/original
  - Badge kuning jika sudah di-adjust admin
- Kolom "Poin Disesuaikan": Input field untuk mengubah nilai
  - Range: 0 - 100
  - Hanya tampil untuk admin/supervisor
  - Hanya bisa diedit jika KPI status ≠ "approved"

### 2. Tracking Audit Trail

Untuk setiap aktivitas yang di-adjust, sistem mencatat:
- `poin_original`: Nilai poin sebelum adjustment
- `poin_adjusted_by`: Username admin yang melakukan adjustment
- `poin_adjusted_at`: Waktu adjustment dilakukan

**Contoh Tampilan**:
```
Tipe Poin: Manual (disesuaikan 2 jam lalu oleh admin)
```

### 3. Opsi Revert (Kembalikan ke Original)

- Untuk aktivitas yang sudah di-adjust, ada tombol "↶" di kolom Aksi
- Klik tombol untuk kembalikan poin ke nilai original
- Poin akan kembali ke `tipe_poin = 'auto'`
- Tracking info juga akan direset

### 4. Bulk Update & KPI Recalculation

Saat admin klik "Simpan Perubahan Poin":
1. Semua poin aktivitas yang berubah disimpan
2. Total poin aktivitas dihitung ulang
3. Realisasi KPI diupdate dengan total poin baru
4. Skor KPI dihitung ulang berdasarkan:
   - Target yang sudah ditetapkan
   - Jenis target (max/min)
   - Bobot indikator
5. Total nilai KPI dihitung ulang dari semua indikator
6. Grade otomatis updated

---

## Contoh Skenario Lengkap

### Scenario: KPI Produktivitas Aktivitas

**Setup Awal**:
- Indikator: "Produktivitas Aktivitas"
- Mode: Auto
- Metric Source: activity_poin
- Target: 500 poin
- Bobot: 25%
- Jenis Target: Max (semakin besar semakin baik)

**Periode**: Januari 2026 (1-31 Januari)

**Aktivitas Karyawan**:
```
1. "Menyelesaikan laporan Q1" (50 kata, foto) 
   → Auto Poin = (50÷10) + 20 = 25.00 poin

2. "Meeting dengan client" (30 kata, no foto) 
   → Auto Poin = (30÷10) + 0 = 3.00 poin

3. "Revisi project X" (100 kata, foto) 
   → Auto Poin = (100÷10) + 20 = 30.00 poin

Total Auto Poin = 58.00 poin
```

**Admin Review**:
- Admin buka halaman KPI karyawan
- Lihat Detail Aktivitas → expand
- Melihat ketiga aktivitas dengan masing-masing poin

**Admin Adjustment** (karena hasil review):
```
1. "Menyelesaikan laporan Q1" 
   Poin: 25.00 → Disesuaikan: 35.00 (karena quality sangat baik)

2. "Meeting dengan client" 
   Poin: 3.00 → Disesuaikan: 8.00 (ada output penting)

3. "Revisi project X" 
   Poin: 30.00 → Disesuaikan: 25.00 (sudah ok tapi ada minor issues)

Total Poin = 35 + 8 + 25 = 68.00 poin
```

**KPI Calculation**:
```
Realisasi = 68.00 poin
Target = 500 poin
Score = (68.00 ÷ 500) × 25% = 3.40 poin

Nilai KPI Akhir = 3.40 + (poin dari indikator lain)
```

---

## Permissions & Access

### Siapa yang bisa Edit Poin Aktivitas?

✅ **Bisa Edit**:
- Super Admin
- User dengan role 'admin'
- User dengan permission 'kpi.transaction.update'

❌ **Tidak Bisa Edit**:
- Karyawan biasa
- User tanpa permission

### Kapan Bisa Edit?

✅ **Bisa Edit** ketika:
- KPI status = 'draft' atau 'submitted'
- Halaman KPI belum di-approve

❌ **Tidak Bisa Edit** ketika:
- KPI status = 'approved'
- Tombol edit akan disabled/hidden
- Nilai akan readonly

---

## API Endpoints (Developer)

Untuk integrasi atau automation, tersedia API endpoints:

### 1. Update Poin Satu Aktivitas

```
PUT /api/activity-point/{activityId}
Headers: 
  - Authorization: Bearer {token}
  - Content-Type: application/json

Body:
{
  "poin": 25.50
}

Response:
{
  "success": true,
  "message": "Poin aktivitas berhasil diperbarui",
  "data": {
    "id": 123,
    "poin": 25.50,
    "tipe_poin": "manual",
    "poin_adjusted_by": "admin_user",
    "poin_adjusted_at": "2026-06-08 10:30:45"
  }
}
```

### 2. Bulk Update & Recalculate KPI

```
POST /api/activity-point/bulk-update
Headers: 
  - Authorization: Bearer {token}
  - Content-Type: application/json

Body:
{
  "kpi_employee_id": 1,
  "activities": [
    { "id": 1, "poin": 35.00 },
    { "id": 2, "poin": 8.00 },
    { "id": 3, "poin": 25.00 }
  ]
}

Response:
{
  "success": true,
  "message": "Poin aktivitas berhasil diperbarui dan KPI telah dihitung ulang",
  "data": {
    "total_activity_points": 68.00,
    "total_nilai_kpi": 65.30,
    "grade": "C",
    "updated_count": 3
  }
}
```

### 3. Revert Poin ke Original

```
POST /api/activity-point/{activityId}/revert
Headers: 
  - Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Poin aktivitas berhasil dikembalikan ke nilai awal",
  "data": {
    "id": 123,
    "poin": 25.00,
    "tipe_poin": "auto"
  }
}
```

---

## FAQ

### Q: Bagaimana jika admin tidak menyesuaikan poin, apakah KPI tetap berjalan?
**A**: Ya, sistem otomatis akan menggunakan poin auto yang sudah dihitung sistem. Adjustment hanya optional.

### Q: Apakah perubahan poin aktivitas mempengaruhi halaman lain?
**A**: Hanya mempengaruhi realisasi KPI di indicator "activity_poin". Tidak mempengaruhi halaman aktivitas atau dashboard lain.

### Q: Bisa lihat history poin yang berubah?
**A**: Ya, di kolom "Tipe Poin" akan menampilkan informasi siapa & kapan poin di-adjust, plus value original disimpan di `poin_original`.

### Q: Apakah karyawan bisa lihat poin sudah di-adjust admin?
**A**: Tergantung setting permission. Biasanya hanya admin yang bisa lihat halaman KPI detail.

### Q: Bisa batch adjust multiple karyawan sekaligus?
**A**: Saat ini via UI hanya satu karyawan. Untuk batch, bisa gunakan API endpoint `/api/activity-point/bulk-update`.

### Q: Minimum poin aktivitas berapa?
**A**: Minimum = 0, Maximum = 100. Sistem akan memvalidasi range ini.

---

## Troubleshooting

### Masalah: Tombol "Simpan Perubahan Poin" tidak muncul

**Solusi**:
1. Pastikan indikator sudah set dengan Mode = "Auto" & Metric Source = "activity_poin"
2. Pastikan user login adalah admin/supervisor
3. Pastikan KPI status bukan "approved"

### Masalah: Poin tidak berubah setelah save

**Solusi**:
1. Cek browser console (F12) untuk error message
2. Cek network tab apakah API call berhasil
3. Pastikan CSRF token valid (refresh page)
4. Cek permission user di database

### Masalah: KPI tidak re-calculate setelah save poin

**Solusi**:
1. Manual refresh halaman
2. Cek apakah ada error di network request
3. Cek error log di server: `storage/logs/laravel.log`

---

## Implementasi & Database Changes

### Migration yang Dijalankan
```
2026_06_08_140000_add_poin_adjustment_to_aktivitas_karyawan_table.php
```

### Kolom Baru di Tabel `aktivitas_karyawan`
```
- poin_original (decimal): Nilai poin sebelum adjustment
- poin_adjusted_by (string): Username admin yang adjust
- poin_adjusted_at (timestamp): Waktu adjustment
```

### Model Fields
```php
protected $fillable = [
    ...,
    'poin_original',
    'poin_adjusted_by',
    'poin_adjusted_at'
];
```

---

## Fitur yang Akan Datang (Roadmap)

- [ ] Export detail aktivitas & poin ke Excel
- [ ] Batch adjustment dengan template
- [ ] Approval workflow untuk poin adjustment
- [ ] Integration dengan performance review
- [ ] Dashboard analytics activity points
- [ ] Notification ke karyawan saat poin di-adjust

---

**Status**: ✅ Production Ready
**Version**: 1.0
**Date**: 2026-06-08
**Tested**: Yes
**Documented**: Yes
