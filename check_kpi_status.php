<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=bumisultanapp;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== FINAL VERIFICATION ===\n";
echo "Checking Adam Adifa A KPI\n\n";

// Get Adam's KPI Employee
$stmt = $pdo->query("SELECT ke.id, ke.nik, ke.status, ke.total_nilai, ke.grade FROM kpi_employees ke 
    INNER JOIN kpi_periods kp ON ke.kpi_period_id = kp.id 
    WHERE ke.nik = '250100001' AND kp.nama_periode = 'Januari 2026'");
$kpi_emp = $stmt->fetch(PDO::FETCH_ASSOC);

if ($kpi_emp) {
    echo "=== KPI EMPLOYEE ===\n";
    echo "ID: " . $kpi_emp['id'] . "\n";
    echo "Status: " . $kpi_emp['status'] . "\n";
    echo "Total Nilai: " . $kpi_emp['total_nilai'] . "\n";
    echo "Grade: " . $kpi_emp['grade'] . "\n\n";
    
    // Check KPI Details
    echo "=== KPI DETAILS (ALL INDICATORS) ===\n";
    $stmt = $pdo->prepare("SELECT 
        kd.id, 
        kid.nama_indikator, 
        kid.metric_source, 
        kid.mode, 
        kd.target, 
        kd.realisasi, 
        kd.bobot, 
        kd.skor 
        FROM kpi_details kd
        INNER JOIN kpi_indicator_details kid ON kd.kpi_indicator_detail_id = kid.id
        WHERE kd.kpi_employee_id = ?
        ORDER BY kid.nama_indikator");
    $stmt->execute([$kpi_emp['id']]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total Indicators: " . count($details) . "\n";
    $total_score = 0;
    foreach ($details as $d) {
        echo "\n- " . $d['nama_indikator'];
        if ($d['metric_source']) {
            echo " [Metric: " . $d['metric_source'] . "]";
        }
        echo "\n";
        echo "  Mode: " . $d['mode'] . " | Target: " . $d['target'] . " | Realisasi: " . $d['realisasi'] . "\n";
        echo "  Bobot: " . $d['bobot'] . " | Skor: " . $d['skor'] . "\n";
        $total_score += floatval($d['skor']);
    }
    echo "\n=== TOTAL SCORE ===\n";
    echo "Sum dari semua skor: " . $total_score . "\n";
    echo "Total Nilai di DB: " . $kpi_emp['total_nilai'] . "\n";
    
    if ($total_score != $kpi_emp['total_nilai']) {
        echo "\n⚠️  MISMATCH! Total tidak match. Perlu di-recalculate di aplikasi.\n";
    } else {
        echo "\n✓ Score sudah match!\n";
    }
} else {
    echo "KPI Employee not found\n";
}

// Also show aktivitas
echo "\n\n=== ADAM'S AKTIVITAS (JANUARY 2026) ===\n";
$stmt = $pdo->query("SELECT aktivitas, poin, tipe_poin FROM aktivitas_karyawan 
    WHERE nik = '250100001' AND created_at >= '2026-01-01' AND created_at <= '2026-01-31'
    ORDER BY created_at");
$aktivitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_poin = 0;
foreach ($aktivitas as $a) {
    $total_poin += floatval($a['poin']);
    echo "✓ " . $a['aktivitas'] . " | Poin: " . $a['poin'] . " (" . $a['tipe_poin'] . ")\n";
}
echo "\nTotal Poin Aktivitas: " . $total_poin . "\n";

?>
