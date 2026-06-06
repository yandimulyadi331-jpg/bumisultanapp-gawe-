<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\KpiIndicator;
use App\Models\KpiIndicatorDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsfindokpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Pastikan Jabatan Staff dengan Kode "05" exists
            $jabatanStaff = Jabatan::firstOrCreate(
                ['kode_jabatan' => '05'],
                ['nama_jabatan' => 'Staff']
            );

            // 2. Pastikan Departemen Produksi dengan Kode "12" exists
            $deptProduksi = Departemen::firstOrCreate(
                ['kode_dept' => '12'],
                ['nama_dept' => 'Produksi']
            );

            // 3. Pastikan Departemen Packing dengan Kode "14" exists
            $deptPacking = Departemen::firstOrCreate(
                ['kode_dept' => '14'],
                ['nama_dept' => 'Packing']
            );

            // 3a. Pastikan Departemen Security dengan Kode "17" exists
            $deptSecurity = Departemen::firstOrCreate(
                ['kode_dept' => '17'],
                ['nama_dept' => 'SECURITY']
            );

            // 3b. Pastikan Departemen Office Boy dengan Kode "16" exists
            $deptOfficeBoy = Departemen::firstOrCreate(
                ['kode_dept' => '16'],
                ['nama_dept' => 'OFFICE BOY']
            );

            // 3c. Pastikan Departemen Inventori dengan Kode "18" exists
            $deptInventori = Departemen::firstOrCreate(
                ['kode_dept' => '18'],
                ['nama_dept' => 'INVENTORI']
            );

            // 3d. Pastikan Departemen Driver dengan Kode "19" exists
            $deptDriver = Departemen::firstOrCreate(
                ['kode_dept' => '19'],
                ['nama_dept' => 'DRIVER']
            );

            // 4. Buat KPI Indicator Header untuk Staff Produksi
            $kpiStaffProduksi = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptProduksi->kode_dept
                ]
            );

            // 5. Buat KPI Indicator Header untuk Staff Packing
            $kpiStaffPacking = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptPacking->kode_dept
                ]
            );

            // 5a. Buat KPI Indicator Header untuk Staff Security
            $kpiStaffSecurity = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptSecurity->kode_dept
                ]
            );

            // 5b. Buat KPI Indicator Header untuk Staff Office Boy
            $kpiStaffOfficeBoy = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptOfficeBoy->kode_dept
                ]
            );

            // 5c. Buat KPI Indicator Header untuk Staff Inventori
            $kpiStaffInventori = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptInventori->kode_dept
                ]
            );

            // 5d. Buat KPI Indicator Header untuk Staff Driver
            $kpiStaffDriver = KpiIndicator::updateOrCreate(
                [
                    'kode_jabatan' => $jabatanStaff->kode_jabatan,
                    'kode_dept' => $deptDriver->kode_dept
                ]
            );

            // 6. Indikator Detail untuk Staff Produksi
            $produksiIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan APD',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Tanggung Jawab',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7. Indikator Detail untuk Staff Packing
            $packingIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Target Packing',
                    'deskripsi' => 'Jumlah paket / hari',
                    'satuan' => 'Paket',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 600.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Tanggung Jawab',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7a. Indikator Detail untuk Staff Security (Dept 17)
            $securityIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan Seragam',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Jumlah kejadian keamanan',
                    'deskripsi' => 'Jumlah insiden keamanan (Target: 0 Incident)',
                    'satuan' => 'Incident',
                    'jenis_target' => 'min',
                    'bobot' => 15,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Pelaksanaan patroli',
                    'deskripsi' => 'Jumlah patroli sesuai jadwal (Target: >= 7 kali)',
                    'satuan' => 'Kali',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 7.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Pengawasan CCTV',
                    'deskripsi' => 'Ketepatan monitoring (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7b. Indikator Detail untuk Staff Office Boy (Dept 16)
            $officeBoyIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Safety compliance',
                    'deskripsi' => 'Kepatuhan SOP & penggunaan Seragam',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kebersihan Pabrik',
                    'deskripsi' => 'Penilaian kebersihan area pabrik (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kepedulian area kerja',
                    'deskripsi' => 'Proaktif menjaga kebersihan (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7c. Indikator Detail untuk Staff Inventori (Dept 18)
            $inventoriIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Akurasi Stock',
                    'deskripsi' => 'Selisih stock fisik vs sistem (Target >= 99%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 99.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Persentase Stock Hilang',
                    'deskripsi' => 'Kehilangan barang (Target <= 0.5%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'min',
                    'bobot' => 5,
                    'target' => 0.50,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kepatuhan SOP Gudang',
                    'deskripsi' => 'Mengikuti SOP (Target 100%)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Ketepatan Laporan Stock',
                    'deskripsi' => 'Report harian/mingguan (Skala 1-5, Target: Sangat Baik / Tepat Waktu)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
            ];

            // 7d. Indikator Detail untuk Staff Driver (Dept 19)
            $driverIndicators = [
                [
                    'nama_indikator' => 'Attendance',
                    'deskripsi' => 'Total Kehadiran Kerja (Target 26 Hari)',
                    'satuan' => 'Hari',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 26.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_hadir',
                ],
                [
                    'nama_indikator' => 'Keterlambatan',
                    'deskripsi' => 'Jumlah hari keterlambatan per bulan',
                    'satuan' => 'Hari',
                    'jenis_target' => 'min',
                    'bobot' => 20,
                    'target' => 2.00,
                    'mode' => 'auto',
                    'metric_source' => 'attendance_terlambat',
                ],
                [
                    'nama_indikator' => 'Kepatuhan aturan lalu lintas',
                    'deskripsi' => 'Tilang / pelanggaran (Target: 0 pelanggaran)',
                    'satuan' => 'Pelanggaran',
                    'jenis_target' => 'min',
                    'bobot' => 10,
                    'target' => 0.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Teamwork',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Etika',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 10,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kejujuran',
                    'deskripsi' => 'Penilaian supervisor (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 20,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kondisi Kendaraan',
                    'deskripsi' => 'Penilaian kondisi kendaraan (Skala 1-5, Target: Sangat Baik)',
                    'satuan' => 'Skala',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 5.00,
                    'mode' => 'manual',
                ],
                [
                    'nama_indikator' => 'Kelengkapan dokumen',
                    'deskripsi' => 'SIM / STNK / KIR (Target: 100% Lengkap)',
                    'satuan' => 'Persen',
                    'jenis_target' => 'max',
                    'bobot' => 5,
                    'target' => 100.00,
                    'mode' => 'manual',
                ],
            ];

            // Sync Produksi Indicators
            $produksiNames = array_column($produksiIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffProduksi->id)
                ->whereNotIn('nama_indikator', $produksiNames)
                ->delete();

            foreach ($produksiIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffProduksi->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Packing Indicators
            $packingNames = array_column($packingIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffPacking->id)
                ->whereNotIn('nama_indikator', $packingNames)
                ->delete();

            foreach ($packingIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffPacking->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Security Indicators
            $securityNames = array_column($securityIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffSecurity->id)
                ->whereNotIn('nama_indikator', $securityNames)
                ->delete();

            foreach ($securityIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffSecurity->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Office Boy Indicators
            $officeBoyNames = array_column($officeBoyIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffOfficeBoy->id)
                ->whereNotIn('nama_indikator', $officeBoyNames)
                ->delete();

            foreach ($officeBoyIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffOfficeBoy->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Inventori Indicators
            $inventoriNames = array_column($inventoriIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffInventori->id)
                ->whereNotIn('nama_indikator', $inventoriNames)
                ->delete();

            foreach ($inventoriIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffInventori->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            // Sync Driver Indicators
            $driverNames = array_column($driverIndicators, 'nama_indikator');
            KpiIndicatorDetail::where('kpi_indicator_id', $kpiStaffDriver->id)
                ->whereNotIn('nama_indikator', $driverNames)
                ->delete();

            foreach ($driverIndicators as $detail) {
                KpiIndicatorDetail::updateOrCreate(
                    [
                        'kpi_indicator_id' => $kpiStaffDriver->id,
                        'nama_indikator' => $detail['nama_indikator']
                    ],
                    $detail
                );
            }

            DB::commit();
            $this->command->info('KPI Asfindo Seeder ran successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error running KPI Asfindo Seeder: ' . $e->getMessage());
        }
    }
}
