<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=bumisultanapp;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== SETUP ACTIVITY POIN KPI INDICATOR ===\n\n";

// Get KPI Period for Januari 2026
$stmt = $pdo->query("SELECT id FROM kpi_periods WHERE nama_periode = 'Januari 2026' LIMIT 1");
$period = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$period) {
    echo "ERROR: KPI Period 'Januari 2026' not found!\n";
    exit;
}

$periodId = $period['id'];
echo "KPI Period ID: " . $periodId . "\n";

// Get KPI Indicator for J01/PRD (Adam's position/dept)
$stmt = $pdo->prepare("SELECT id FROM kpi_indicators WHERE kode_jabatan = 'J01' AND kode_dept = 'PRD'");
$stmt->execute();
$indicator = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$indicator) {
    echo "ERROR: KPI Indicator for J01/PRD not found!\n";
    echo "Creating it now...\n";
    
    $stmt = $pdo->prepare("INSERT INTO kpi_indicators (kode_jabatan, kode_dept, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute(['J01', 'PRD']);
    $indicatorId = $pdo->lastInsertId();
    echo "Created KPI Indicator ID: " . $indicatorId . "\n";
} else {
    $indicatorId = $indicator['id'];
    echo "KPI Indicator ID (existing): " . $indicatorId . "\n";
}

// Check if activity_poin indicator detail already exists
$stmt = $pdo->prepare("SELECT id FROM kpi_indicator_details WHERE kpi_indicator_id = ? AND metric_source = 'activity_poin'");
$stmt->execute([$indicatorId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "\n✓ Activity Poin indicator detail already exists (ID: " . $existing['id'] . ")\n";
    exit;
}

// Create KPI Indicator Detail for activity_poin
echo "\n=== CREATING ACTIVITY POIN INDICATOR DETAIL ===\n";

$stmt = $pdo->prepare("INSERT INTO kpi_indicator_details (
    kpi_indicator_id,
    nama_indikator,
    deskripsi,
    satuan,
    jenis_target,
    bobot,
    target,
    mode,
    metric_source,
    created_at,
    updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

$result = $stmt->execute([
    $indicatorId,
    'Produktivitas Aktivitas',
    'Total poin dari aktivitas karyawan',
    'Poin',
    'max',
    25,
    400.00,
    'auto',
    'activity_poin'
]);

if ($result) {
    $detailId = $pdo->lastInsertId();
    echo "✓ Created indicator detail ID: " . $detailId . "\n";
    echo "✓ Nama: Produktivitas Aktivitas\n";
    echo "✓ Mode: auto\n";
    echo "✓ Metric Source: activity_poin\n";
    echo "✓ Bobot: 25%\n";
    echo "✓ Target: 400 poin\n";
} else {
    echo "ERROR: Failed to create indicator detail\n";
    exit;
}

// Now create KPI Detail for Adam Adifa A
echo "\n=== CREATE KPI DETAIL FOR ADAM ADIFA A ===\n";

// Get Adam's KPI Employee
$stmt = $pdo->prepare("SELECT id FROM kpi_employees WHERE nik = '250100001' AND kpi_period_id = ?");
$stmt->execute([$periodId]);
$kpiEmp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kpiEmp) {
    echo "ERROR: KPI Employee for Adam not found\n";
    exit;
}

$kpiEmpId = $kpiEmp['id'];

// Check if KPI Detail already exists
$stmt = $pdo->prepare("SELECT id FROM kpi_details WHERE kpi_employee_id = ? AND kpi_indicator_detail_id = ?");
$stmt->execute([$kpiEmpId, $detailId]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "✓ KPI Detail already exists for Adam (ID: " . $existing['id'] . ")\n";
} else {
    // Calculate Adam's realisasi (total poin dari aktivitas)
    $stmt = $pdo->prepare("SELECT SUM(poin) as total_poin FROM aktivitas_karyawan 
        WHERE nik = '250100001' AND created_at >= '2026-01-01' AND created_at <= '2026-01-31'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $realisasi = floatval($result['total_poin'] ?? 0);
    
    echo "Adam's realisasi (total poin): " . $realisasi . "\n";
    
    // Create KPI Detail
    $stmt = $pdo->prepare("INSERT INTO kpi_details (
        kpi_employee_id,
        kpi_indicator_detail_id,
        target,
        realisasi,
        bobot,
        skor,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    $target = 400.00;
    $score = ($realisasi / $target) * 25;  // Bobot = 25
    $score = min($score, 25);  // Max tidak boleh lebih dari bobot
    
    $result = $stmt->execute([
        $kpiEmpId,
        $detailId,
        $target,
        $realisasi,
        25,
        $score
    ]);
    
    if ($result) {
        echo "✓ Created KPI Detail for Adam\n";
        echo "  Target: " . $target . "\n";
        echo "  Realisasi: " . $realisasi . "\n";
        echo "  Score: " . $score . " (dari 25)\n";
    }
}

echo "\n=== SETUP COMPLETE ===\n";
echo "Activity Poin indicator sudah di-setup!\n";
echo "Refresh KPI page untuk lihat perubahan.\n";

?>