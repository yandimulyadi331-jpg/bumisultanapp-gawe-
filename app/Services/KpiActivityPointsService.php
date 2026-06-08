<?php

namespace App\Services;

use App\Models\AktivitasKaryawan;
use App\Models\KpiEmployee;
use App\Models\KpiDetail;
use DateTime;

class KpiActivityPointsService
{
    /**
     * Calculate total activity points for an employee in a specific period
     * 
     * @param string $nik - Employee NIK
     * @param DateTime $startDate - Period start date
     * @param DateTime $endDate - Period end date
     * @return float - Total points
     */
    public function calculateActivityPoints($nik, $startDate, $endDate)
    {
        $total_poin = AktivitasKaryawan::where('nik', $nik)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('poin', '>', 0)
            ->sum('poin');

        return round($total_poin, 2);
    }

    /**
     * Get count of activities with points in a period
     */
    public function getActivityCount($nik, $startDate, $endDate)
    {
        return AktivitasKaryawan::where('nik', $nik)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('poin', '>', 0)
            ->count();
    }

    /**
     * Get average points per activity
     */
    public function getAverageActivityPoints($nik, $startDate, $endDate)
    {
        $total = AktivitasKaryawan::where('nik', $nik)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('poin', '>', 0);

        $count = $total->count();

        if ($count === 0) {
            return 0;
        }

        return round($total->sum('poin') / $count, 2);
    }

    /**
     * Get activity points detail for KPI display
     */
    public function getActivityPointsDetail($nik, $startDate, $endDate)
    {
        $activities = AktivitasKaryawan::where('nik', $nik)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('poin', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'aktivitas', 'poin', 'tipe_poin', 'created_at', 'poin_input_by']);

        return [
            'total_poin' => $activities->sum('poin'),
            'count' => $activities->count(),
            'average' => $activities->count() > 0 ? round($activities->sum('poin') / $activities->count(), 2) : 0,
            'activities' => $activities
        ];
    }

    /**
     * Update or create KPI Detail entry for activity points
     * This is called when updating KPI Employee assessment
     */
    public function updateKpiActivityPoints(KpiEmployee $kpi_employee, $kpiIndicatorDetailId)
    {
        $activity_poin = $this->calculateActivityPoints(
            $kpi_employee->nik,
            $kpi_employee->period->start_date,
            $kpi_employee->period->end_date
        );

        // Find or create KPI Detail for activities
        $kpiDetail = KpiDetail::where('kpi_employee_id', $kpi_employee->id)
            ->where('kpi_indicator_detail_id', $kpiIndicatorDetailId)
            ->first();

        if ($kpiDetail) {
            // Update existing
            $kpiDetail->realisasi = $activity_poin;
            // Calculate score based on indicator
            $this->calculateScoreForActivityPoints($kpiDetail);
            $kpiDetail->save();
        } else {
            // Create new
            $kpiDetail = new KpiDetail([
                'kpi_employee_id' => $kpi_employee->id,
                'kpi_indicator_detail_id' => $kpiIndicatorDetailId,
                'realisasi' => $activity_poin,
                'target' => 100, // Default target for activities
                'bobot' => 0, // Will be filled from indicator
            ]);
            
            $this->calculateScoreForActivityPoints($kpiDetail);
            $kpiDetail->save();
        }

        return $kpiDetail;
    }

    /**
     * Calculate score for activity points based on jenis_target
     */
    private function calculateScoreForActivityPoints(KpiDetail $kpiDetail)
    {
        $indicator = $kpiDetail->indicator;
        $bobot = $indicator->bobot;
        $realisasi = $kpiDetail->realisasi;
        $target = $kpiDetail->target;

        if ($indicator->jenis_target === 'max') {
            // Higher is better
            if ($target > 0) {
                $score = ($realisasi / $target) * $bobot;
            } else {
                $score = 0;
            }
        } else {
            // Min (lower is better)
            if ($realisasi == 0) {
                $score = $bobot; // Perfect score
            } else {
                $score = ($target / $realisasi) * $bobot;
            }
        }

        // Cap at maximum bobot value
        if ($score > $bobot) {
            $score = $bobot;
        }

        $kpiDetail->skor = round($score, 2);
    }

    /**
     * Get activities supporting evidence for KPI detail
     */
    public function getActivityEvidence($nik, $startDate, $endDate)
    {
        $activities = AktivitasKaryawan::where('nik', $nik)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('poin', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        $evidence = "Aktivitas Karyawan:\n\n";

        foreach ($activities as $activity) {
            $evidence .= "📌 " . $activity->created_at->format('d/m/Y H:i') . " - Poin: " . $activity->poin . "\n";
            $evidence .= "   " . substr($activity->aktivitas, 0, 100) . (strlen($activity->aktivitas) > 100 ? '...' : '') . "\n";
            if ($activity->lokasi) {
                $evidence .= "   Lokasi: " . $activity->lokasi . "\n";
            }
            $evidence .= "\n";
        }

        return $evidence;
    }
}
