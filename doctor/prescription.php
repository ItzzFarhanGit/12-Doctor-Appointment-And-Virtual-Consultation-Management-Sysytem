<?php
// =============================================
// SAJEEFA - Create Prescription (doctor/prescription.php)
// =============================================
require_once '../includes/doctor_auth.php';
require_once '../includes/db.php';

$success = '';
$error   = '';

// SAJEEFA FIX: only show patients THIS doctor has approved, not every doctor's patients.
$stmt = $conn->prepare(
    "SELECT DISTINCT patient_name, patient_id FROM appointments WHERE doctor_approval='approved' AND doctor_id=? ORDER BY patient_name"
);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$approved_patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pat_name     = trim($_POST['patientName']);
    $pat_phone    = trim($_POST['phone']);
    $diagnosis    = trim($_POST['diagnosis']);
    $medicines    = trim($_POST['medicines']);
    $instructions = trim($_POST['instructions']);
    $presc_date   = $_POST['presc_date'];

    $stmt = $conn->prepare("INSERT INTO prescriptions (doctor_name, doctor_id, patient_name, patient_phone, diagnosis, medicines, instructions, prescription_date) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $doctor_name, $doctor_id, $pat_name, $pat_phone, $diagnosis, $medicines, $instructions, $presc_date);

    if ($stmt->execute()) {
        $success = "Prescription saved successfully for $pat_name!";
    } else {
        $error = "Failed to save. Please try again.";
    }
}

// SAJEEFA FIX: doctor could only CREATE prescriptions, never see what they'd
// already issued (only admin could, via a different page). Add that history here.
$hist = $conn->prepare("SELECT * FROM prescriptions WHERE doctor_id=? ORDER BY created_at DESC");
$hist->bind_param("s", $doctor_id);
$hist->execute();
$my_prescriptions = $hist->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Prescription</title>
<meta charset="UTF-8">
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.card{ background:white; padding:25px; border-radius:12px; max-width:550px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
input, textarea, select{ width:100%; padding:10px; margin:8px 0 15px; border:1px solid #ddd; border-radius:6px; box-sizing:border-box; font-family:Arial; }
textarea{ resize:vertical; min-height:80px; }
button{ width:100%; padding:12px; background:#0b2a4a; color:white; border:none; border-radius:6px; cursor:pointer; font-size:15px; }
button:hover{ background:#09304f; }
label{ font-size:13px; font-weight:bold; color:#444; }
</style>
</head>
<body>
<div class="container">
<?php doctorSidebar(); ?>
<div class="main">
<h2>💊 Create Prescription</h2>

<?php if ($success): ?>
<div style="background:#efe;padding:12px;border-radius:8px;margin-bottom:15px;color:#060;">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div style="background:#fee;padding:12px;border-radius:8px;margin-bottom:15px;color:#c00;">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
<form method="POST">
    <label>Doctor Name</label>
    <input type="text" value="<?= htmlspecialchars($doctor_name) ?>" readonly style="background:#f5f5f5;">

    <label>Prescription Date</label>
    <input type="date" name="presc_date" required value="<?= date('Y-m-d') ?>">

    <label>Patient Name</label>
    <?php if (!empty($approved_patients)): ?>
    <select name="patientName" required>
        <option value="">-- Select Approved Patient --</option>
        <?php foreach ($approved_patients as $p): ?>
        <option value="<?= htmlspecialchars($p['patient_name']) ?>"><?= htmlspecialchars($p['patient_name']) ?> (<?= $p['patient_id'] ?>)</option>
        <?php endforeach; ?>
    </select>
    <?php else: ?>
    <input type="text" name="patientName" placeholder="Patient Name" required>
    <?php endif; ?>

    <label>Patient Phone</label>
    <input type="tel" name="phone" placeholder="07XXXXXXXX" maxlength="10">

    <label>Diagnosis</label>
    <input type="text" name="diagnosis" placeholder="Enter diagnosis" required>

    <label>Medicines</label>
    <textarea name="medicines" placeholder="List medicines, dosage..." required></textarea>

    <label>Instructions</label>
    <textarea name="instructions" placeholder="Patient instructions..." required></textarea>

    <button type="submit">💾 Save Prescription</button>
</form>
</div>

<div class="card" style="max-width:750px;margin-top:25px;">
    <h3 style="margin-top:0;color:#0b2a4a;">📋 Prescriptions You've Issued (<?= count($my_prescriptions) ?>)</h3>
    <?php if (empty($my_prescriptions)): ?>
    <p style="color:#666;">You haven't issued any prescriptions yet.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#0b2a4a;color:white;">
                <th style="padding:10px;text-align:left;font-size:13px;">Date</th>
                <th style="padding:10px;text-align:left;font-size:13px;">Patient</th>
                <th style="padding:10px;text-align:left;font-size:13px;">Diagnosis</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($my_prescriptions as $mp): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:10px;font-size:13px;"><?= htmlspecialchars($mp['prescription_date']) ?></td>
                <td style="padding:10px;font-size:13px;"><?= htmlspecialchars($mp['patient_name']) ?></td>
                <td style="padding:10px;font-size:13px;"><?= htmlspecialchars($mp['diagnosis']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

</div>
</div>
</body>
</html>
