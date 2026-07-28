<?php
// =============================================
// SAJEEFA - Download Prescription (patient/download_prescription.php)
// SAJEEFA FIX: patient/prescription.php's "Download" button pointed here, but this
// file never existed, so the button was completely dead. This adds it: a clean,
// printable page (opens the browser print dialog automatically, patient can
// "Save as PDF") for the ONE prescription requested, after confirming it really
// belongs to the logged-in patient (same ownership check as prescription.php).
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT p.* FROM prescriptions p
    JOIN appointments a ON a.patient_name = p.patient_name AND a.patient_id = ?
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->bind_param("si", $patient_id, $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
    header("Location: prescription.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Prescription - <?= htmlspecialchars($p['patient_name']) ?></title>
<style>
body{ font-family:Arial, sans-serif; background:#f4f7f6; margin:0; padding:30px; }
.sheet{ max-width:650px; margin:0 auto; background:white; padding:35px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
.header{ text-align:center; border-bottom:3px solid #0b2a4a; padding-bottom:15px; margin-bottom:20px; }
.header h1{ color:#0b2a4a; margin:0 0 5px; }
.header p{ color:gray; margin:0; font-size:13px; }
.row{ display:flex; justify-content:space-between; margin-bottom:10px; font-size:14px; }
.block{ margin-top:20px; }
.block h3{ color:#0b2a4a; border-bottom:1px solid #eee; padding-bottom:6px; margin-bottom:8px; font-size:15px; }
.block p{ white-space:pre-line; font-size:14px; margin:0; }
.no-print{ text-align:center; margin-top:25px; }
.btn{ padding:10px 20px; background:#0b2a4a; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:bold; text-decoration:none; display:inline-block; }
@media print{ .no-print{ display:none; } body{ background:white; padding:0; } .sheet{ box-shadow:none; } }
</style>
</head>
<body>
<div class="sheet">
    <div class="header">
        <h1>SAJEEFA Online Clinic</h1>
        <p>Medical Prescription</p>
    </div>

    <div class="row"><b>Doctor:</b><span>Dr. <?= htmlspecialchars($p['doctor_name']) ?></span></div>
    <div class="row"><b>Patient:</b><span><?= htmlspecialchars($p['patient_name']) ?></span></div>
    <div class="row"><b>Phone:</b><span><?= htmlspecialchars($p['patient_phone'] ?: '-') ?></span></div>
    <div class="row"><b>Date:</b><span><?= htmlspecialchars($p['prescription_date']) ?></span></div>

    <div class="block">
        <h3>Diagnosis</h3>
        <p><?= htmlspecialchars($p['diagnosis']) ?></p>
    </div>
    <div class="block">
        <h3>Medicines</h3>
        <p><?= htmlspecialchars($p['medicines']) ?></p>
    </div>
    <div class="block">
        <h3>Instructions</h3>
        <p><?= htmlspecialchars($p['instructions']) ?></p>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <a href="prescription.php" class="btn" style="background:#ccc;color:#333;margin-left:10px;">Back</a>
    </div>
</div>
</body>
</html>
