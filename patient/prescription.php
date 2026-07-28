<?php
// =============================================
// SAJEEFA - Patient Prescription (patient/prescription.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

// Get prescriptions for this patient (match by patient name from appointments)
$stmt = $conn->prepare("
    SELECT p.* FROM prescriptions p 
    JOIN appointments a ON a.patient_name = p.patient_name AND a.patient_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$prescriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Prescription</title>
<meta charset="UTF-8">
<style>
body{ margin:0;font-family:Arial;background:#f4f7f6; display:flex; min-height:100vh; }
.main{ flex:1;padding:25px;background:#f4f7f6; }
.presc-card{ background:white;padding:20px;margin-bottom:15px;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.08);max-width:650px; border-left:4px solid #0b2a4a; }
.download-btn{ padding:8px 14px;background:#0b2a4a;color:white;border:none;border-radius:5px;cursor:pointer;font-size:13px; }
.no-data{ background:white;padding:40px;text-align:center;border-radius:10px;color:#666;max-width:650px; }
</style>
</head>
<body>
<?php patientSidebar(); ?>
<div class="main">
<h2>💊 My Prescriptions</h2>

<?php if (empty($prescriptions)): ?>
<div class="no-data">
    <h3>No Prescriptions Available</h3>
    <p>Your doctor will add prescriptions after your appointment is approved.</p>
</div>
<?php else: ?>
<?php foreach ($prescriptions as $p): ?>
<div class="presc-card">
    <h3 style="margin:0 0 10px;">Dr. <?= htmlspecialchars($p['doctor_name']) ?></h3>
    <p><b>Patient:</b> <?= htmlspecialchars($p['patient_name']) ?></p>
    <p><b>Date:</b> <?= htmlspecialchars($p['prescription_date']) ?></p>
    <p><b>Diagnosis:</b> <?= htmlspecialchars($p['diagnosis']) ?></p>
    <p><b>Medicines:</b><br><span style="white-space:pre-line;"><?= htmlspecialchars($p['medicines']) ?></span></p>
    <p><b>Instructions:</b><br><span style="white-space:pre-line;"><?= htmlspecialchars($p['instructions']) ?></span></p>
    <button class="download-btn" onclick="downloadPrescription(<?= $p['id'] ?>)">⬇️ Download</button>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function downloadPrescription(id) {
    // Fetch prescription text from a simple endpoint
    window.location.href = 'download_prescription.php?id=' + id;
}
</script>
</body>
</html>
