<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AktivitasKaryawan;
use App\Models\KpiEmployee;
use App\Services\KpiActivityPointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityPointController extends Controller
{
    protected $activityService;

    public function __construct()
    {
        $this->activityService = new KpiActivityPointsService();
    }

    /**
     * Update poin aktivitas tertentu
     * PUT /api/activity-point/{activityId}
     */
    public function updateActivityPoint(Request $request, $activityId)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $activity = AktivitasKaryawan::findOrFail($activityId);

        // Check authorization - user must be admin/supervisor or updating own activity
        $user = Auth::user();
        $hasPermission = $user->hasPermissionTo('aktivitaskaryawan.edit') || 
                         $user->hasRole('admin') || 
                         $user->hasRole('supervisor');
        
        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah point aktivitas'
            ], 403);
        }

        $request->validate([
            'poin' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Simpan poin original jika belum ada
            if (empty($activity->poin_original)) {
                $activity->poin_original = $activity->poin;
            }

            // Update poin
            $activity->poin = round($request->poin, 2);
            $activity->tipe_poin = 'manual';
            $activity->poin_input_by = Auth::user()->username ?? Auth::user()->email;
            $activity->poin_set_at = now();
            $activity->poin_adjusted_by = Auth::user()->username ?? Auth::user()->email;
            $activity->poin_adjusted_at = now();

            $activity->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Poin aktivitas berhasil diperbarui',
                'data' => [
                    'id' => $activity->id,
                    'poin' => $activity->poin,
                    'tipe_poin' => $activity->tipe_poin,
                    'poin_adjusted_by' => $activity->poin_adjusted_by,
                    'poin_adjusted_at' => $activity->poin_adjusted_at->format('d/m/Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui poin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update poin aktivitas dan recalculate KPI
     * POST /api/activity-point/bulk-update
     */
    public function bulkUpdateAndRecalculate(Request $request)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check authorization
        $user = Auth::user();
        $hasPermission = $user->hasPermissionTo('aktivitaskaryawan.edit') || 
                         $user->hasRole('admin') || 
                         $user->hasRole('supervisor');
        
        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah point aktivitas'
            ], 403);
        }

        $request->validate([
            'kpi_employee_id' => 'required|exists:kpi_employees,id',
            'activities' => 'required|array',
            'activities.*.id' => 'required|exists:aktivitas_karyawan,id',
            'activities.*.poin' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $kpiEmployee = KpiEmployee::findOrFail($request->kpi_employee_id);
            $updatedActivities = [];

            // Update semua aktivitas
            foreach ($request->activities as $activityData) {
                $activity = AktivitasKaryawan::findOrFail($activityData['id']);

                if (empty($activity->poin_original)) {
                    $activity->poin_original = $activity->poin;
                }

                $activity->poin = round($activityData['poin'], 2);
                $activity->tipe_poin = 'manual';
                $activity->poin_input_by = Auth::user()->username ?? Auth::user()->email;
                $activity->poin_set_at = now();
                $activity->poin_adjusted_by = Auth::user()->username ?? Auth::user()->email;
                $activity->poin_adjusted_at = now();
                $activity->save();

                $updatedActivities[] = $activity;
            }

            // Recalculate total activity points untuk KPI ini
            $totalPoints = $this->activityService->calculateActivityPoints(
                $kpiEmployee->nik,
                $kpiEmployee->period->start_date,
                $kpiEmployee->period->end_date
            );

            // Update KPI Details yang menggunakan activity_poin
            $kpiDetails = $kpiEmployee->details()
                ->whereHas('indicator', function ($query) {
                    $query->where('metric_source', 'activity_poin');
                })
                ->get();

            foreach ($kpiDetails as $detail) {
                $detail->realisasi = $totalPoints;
                
                // Recalculate score
                $bobot = $detail->bobot;
                $target = $detail->target;
                $indicator = $detail->indicator;

                if ($indicator->jenis_target === 'max') {
                    if ($target > 0) {
                        $score = ($totalPoints / $target) * $bobot;
                    } else {
                        $score = 0;
                    }
                } else { // min
                    if ($totalPoints == 0) {
                        $score = $bobot;
                    } else {
                        $score = ($target / $totalPoints) * $bobot;
                    }
                }

                if ($score > $bobot) {
                    $score = $bobot;
                }

                $detail->skor = round($score, 2);
                $detail->save();
            }

            // Recalculate total nilai KPI
            $totalNilai = $kpiEmployee->details()->sum('skor');
            $kpiEmployee->total_nilai = round($totalNilai, 2);

            // Update grade
            if ($totalNilai >= 90) {
                $kpiEmployee->grade = 'A';
            } elseif ($totalNilai >= 80) {
                $kpiEmployee->grade = 'B';
            } elseif ($totalNilai >= 70) {
                $kpiEmployee->grade = 'C';
            } elseif ($totalNilai >= 60) {
                $kpiEmployee->grade = 'D';
            } else {
                $kpiEmployee->grade = 'E';
            }

            $kpiEmployee->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Poin aktivitas berhasil diperbarui dan KPI telah dihitung ulang',
                'data' => [
                    'total_activity_points' => $totalPoints,
                    'total_nilai_kpi' => $kpiEmployee->total_nilai,
                    'grade' => $kpiEmployee->grade,
                    'updated_count' => count($updatedActivities)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui poin dan KPI: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert poin aktivitas ke nilai original
     * POST /api/activity-point/{activityId}/revert
     */
    public function revertActivityPoint(Request $request, $activityId)
    {
        // Check authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check authorization
        $user = Auth::user();
        $hasPermission = $user->hasPermissionTo('aktivitaskaryawan.edit') || 
                         $user->hasRole('admin') || 
                         $user->hasRole('supervisor');
        
        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah point aktivitas'
            ], 403);
        }

        $activity = AktivitasKaryawan::findOrFail($activityId);

        if (empty($activity->poin_original)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada poin original untuk dikembalikan'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $activity->poin = $activity->poin_original;
            $activity->tipe_poin = 'auto';
            $activity->poin_adjusted_by = null;
            $activity->poin_adjusted_at = null;
            $activity->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Poin aktivitas berhasil dikembalikan ke nilai awal',
                'data' => [
                    'id' => $activity->id,
                    'poin' => $activity->poin,
                    'tipe_poin' => $activity->tipe_poin,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan poin: ' . $e->getMessage()
            ], 500);
        }
    }
}
