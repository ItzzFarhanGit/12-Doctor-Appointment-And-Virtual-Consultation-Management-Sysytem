<?php
// =============================================
// SAJEEFA - My Appointments (patient/myappointment.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

$message = '';

// Handle Cancel (delete) - only the appointment's own patient can cancel it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $app_id = $_POST['appointment_id'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $stmt->bind_param("ss", $app_id, $patient_id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $message = "Appointment $app_id has been cancelled.";
    } else {
        $message = "Could not cancel that appointment.";
    }
}

// Load appointments for this patient
$stmt = $conn->prepare("SELECT * FROM appointments WHERE patient_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Appointments</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{ margin:0; font-family:Arial, sans-serif; background:#f4f7f6; display:flex; min-height:100vh; }
.main-content{ flex:1; padding:25px; }
.appointment-card{ background:white; padding:25px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); max-width:750px; margin-bottom:20px; border-left:6px solid #0b2a4a; }
.card-header{ display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; }
.badge{ padding:6px 14px; border-radius:50px; font-weight:bold; font-size:13px; }
.badge-paid{ background:#e6f4ea; color:#137333; }
.badge-pending{ background:#fef7e0; color:#b06000; }
.badge-approved{ background:#e8f0fe; color:#1a73e8; }
.badge-rejected{ background:#fee; color:#c00; }
.info-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px; }
.no-data{ background:white; padding:40px; text-align:center; border-radius:12px; max-width:750px; color:#666; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:750px; background:#e8f0fe; color:#1a73e8; }
.action-row{ display:flex; gap:10px; margin-top:15px; }
.btn{ padding:9px 18px; border-radius:6px; font-weight:bold; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:6px; font-size:13px; text-decoration:none; }
.btn-edit{ background:#f59e0b; color:white; }
.btn-cancel{ background:#ef4444; color:white; }
</style>
</head>
<body>
<?php patientSidebar(); ?>
<div class="main-content">
<h2>My Appointments</h2>
<p style="color:gray;">Welcome, <?= htmlspecialchars($patient_name) ?> (<?= $patient_id ?>)</p>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (empty($appointments)): ?>
<div class="no-data">
    <i class="fas fa-calendar-times" style="font-size:40px;color:#aaa;margin-bottom:15px;display:block;"></i>
    <h3>No Appointments Found</h3>
    <p>Book your first appointment with our doctor.</p>
    <a href="appointment.php" style="display:inline-block;padding:10px 20px;background:#0b2a4a;color:white;border-radius:8px;text-decoration:none;margin-top:10px;">Book Now</a>
</div>

<?php else: ?>
<?php foreach ($appointments as $a): 
    $approval_class = match($a['doctor_approval']) {
        'approved' => 'badge-approved',
        'rejected'  => 'badge-rejected',
        default     => 'badge-pending'
    };
    $is_virtual = in_array(strtolower($a['consult_type']), ['virtual', 'video', 'online']);
    $is_pending = ($a['doctor_approval'] === 'pending');
?>
<div class="appointment-card" style="border-left-color: <?= $a['doctor_approval']==='approved' ? '#10b981' : ($a['doctor_approval']==='rejected' ? '#ef4444' : '#f59e0b') ?>;">
    <div class="card-header">
        <h3 style="margin:0;"><?= htmlspecialchars($a['appointment_id']) ?></h3>
        <div>
            <span class="badge badge-<?= $a['payment_status']==='paid' ? 'paid' : 'pending' ?>">
                <?= strtoupper($a['payment_status']) ?>
            </span>
            <span class="badge <?= $approval_class ?>">
                <?= strtoupper($a['doctor_approval']) ?>
            </span>
        </div>
    </div>

    <div class="info-grid">
        <p><b>Doctor:</b> <?= htmlspecialchars($a['doctor_name']) ?></p>
        <p><b>Date:</b> <?= htmlspecialchars($a['appointment_date']) ?></p>
        <p><b>Time Slot:</b> <?= htmlspecialchars($a['time_slot']) ?></p>
        <p><b>Type:</b> <?= strtoupper($a['consult_type']) ?></p>
        <p><b>Fee:</b> <span style="color:#0b2a4a;font-weight:bold;">LKR <?= number_format($a['fee']) ?></span></p>
    </div>

    <?php if ($is_virtual && $a['doctor_approval'] === 'approved' && $a['meeting_link']): ?>
    <div style="background:#f0f9ff;padding:12px;border-radius:8px;border-left:4px solid #0ea5e9;">
        <b>Teleconsultation Link:</b><br>
        <a href="<?= htmlspecialchars($a['meeting_link']) ?>" target="_blank" 
           style="display:inline-flex;align-items:center;gap:8px;margin-top:8px;padding:8px 16px;background:#10b981;color:white;border-radius:6px;text-decoration:none;font-weight:bold;">
            <i class="fas fa-video"></i> Join Video Call
        </a>
    </div>
    <?php endif; ?>

    <div class="action-row">
        <?php if ($is_pending): ?>
        <a href="edit_appointment.php?id=<?= urlencode($a['appointment_id']) ?>" class="btn btn-edit">
            <i class="fas fa-edit"></i> Edit
        </a>
        <?php endif; ?>
        <form method="POST" onsubmit="return confirm('Cancel appointment <?= htmlspecialchars($a['appointment_id']) ?>? This cannot be undone.');" style="display:inline;">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($a['appointment_id']) ?>">
            <button type="submit" class="btn btn-cancel"><i class="fas fa-trash"></i> Cancel</button>
        </form>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

</div>
</body>
</html>
