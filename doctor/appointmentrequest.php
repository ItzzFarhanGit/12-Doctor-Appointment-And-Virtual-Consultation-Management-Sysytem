<?php
// =============================================
// SAJEEFA - Appointment Requests (doctor/appointmentrequest.php)
// =============================================
require_once '../includes/doctor_auth.php';
require_once '../includes/db.php';

$message = '';

// Handle Approve / Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Real, working video call room (Jitsi Meet - no login needed, free).
        // Same room name is stored for both doctor and patient, so both land in the same call.
        $meet_link = 'https://meet.jit.si/SAJEEFA-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $app_id)) . '-' . substr(md5($app_id . 'sajeefa'), 0, 6);
        // SAJEEFA FIX: "AND doctor_id=?" added so a doctor can only approve/reject
        // THEIR OWN patients' bookings, never another doctor's appointment.
        $stmt = $conn->prepare("UPDATE appointments SET doctor_approval='approved', meeting_link=? WHERE appointment_id=? AND doctor_id=?");
        $stmt->bind_param("sss", $meet_link, $app_id, $doctor_id);
        $stmt->execute();
        $message = "✅ Appointment <b>$app_id</b> Approved Successfully!";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE appointments SET doctor_approval='rejected' WHERE appointment_id=? AND doctor_id=?");
        $stmt->bind_param("ss", $app_id, $doctor_id);
        $stmt->execute();
        $message = "❌ Appointment <b>$app_id</b> Rejected.";
    }
}

// SAJEEFA FIX: only load appointments booked with THIS logged-in doctor,
// so patients booking Dr. A never show up in Dr. B's request list.
// Also LEFT JOIN patients so the doctor can see full patient contact details
// (phone, email, gender) for every booking, not just the name typed at booking time.
$stmt = $conn->prepare(
    "SELECT a.*, p.phone AS patient_phone, p.email AS patient_email, p.gender AS patient_gender
     FROM appointments a
     LEFT JOIN patients p ON p.patient_id = a.patient_id
     WHERE a.doctor_id=?
     ORDER BY a.created_at DESC"
);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointment Requests</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.request-card{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); max-width:750px; margin-bottom:20px; border-left:6px solid #f59e0b; }
.request-card.approved{ border-left-color:#10b981; }
.request-card.rejected{ border-left-color:#ef4444; }
.card-header{ display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px; }
.badge{ padding:6px 14px; border-radius:50px; font-weight:bold; font-size:13px; }
.badge-pending{ background:#fef7e0; color:#b06000; }
.badge-approved{ background:#e8f0fe; color:#1a73e8; }
.badge-rejected{ background:#fee; color:#c00; }
.info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px; font-size:14px; }
.btn{ padding:10px 20px; border-radius:6px; font-weight:bold; cursor:pointer; border:none; display:inline-flex; align-items:center; gap:8px; font-size:14px; }
.btn-approve{ background:#10b981; color:white; }
.btn-reject{ background:#ef4444; color:white; }
.no-data{ background:white; padding:40px; text-align:center; border-radius:12px; max-width:750px; color:#666; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:750px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php doctorSidebar(); ?>
<div class="main">
    <h1>Patient Appointment Requests</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <?php if (empty($appointments)): ?>
    <div class="no-data"><h3>No Requests Found</h3><p>No appointments have been booked yet.</p></div>

    <?php else: foreach ($appointments as $a):
        $approval = $a['doctor_approval'];
        $is_pending = ($approval === 'pending');
        $is_virtual = in_array(strtolower($a['consult_type']), ['virtual','video','online']);
    ?>
    <div class="request-card <?= $approval ?>">
        <div class="card-header">
            <h3 style="margin:0;">Patient: <?= htmlspecialchars($a['patient_name']) ?></h3>
            <span class="badge badge-<?= $approval ?>">
                <i class="fas <?= $is_pending ? 'fa-hourglass-half' : ($approval==='approved' ? 'fa-check-circle' : 'fa-times-circle') ?>"></i>
                <?= strtoupper($approval) ?>
            </span>
        </div>

        <div class="info-grid">
            <p><b>Appointment ID:</b> <?= htmlspecialchars($a['appointment_id']) ?></p>
            <p><b>Patient ID:</b> <?= htmlspecialchars($a['patient_id']) ?></p>
            <p><b>Phone:</b> <?= htmlspecialchars($a['patient_phone'] ?? '—') ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($a['patient_email'] ?? '—') ?></p>
            <p><b>Gender:</b> <?= htmlspecialchars($a['patient_gender'] ?? '—') ?></p>
            <p><b>Date:</b> <?= htmlspecialchars($a['appointment_date']) ?></p>
            <p><b>Time Slot:</b> <?= htmlspecialchars($a['time_slot']) ?></p>
            <p><b>Type:</b> <?= strtoupper($a['consult_type']) ?></p>
            <p><b>Fee:</b> LKR <?= number_format($a['fee']) ?> 
               | <span style="color:<?= $a['payment_status']==='paid' ? '#10b981' : '#ef4444' ?>;font-weight:bold;">
                   <?= strtoupper($a['payment_status']) ?>
               </span>
            </p>
        </div>

        <?php if ($is_virtual && $approval === 'approved' && $a['meeting_link']): ?>
        <div style="margin-bottom:15px;">
            <a href="<?= htmlspecialchars($a['meeting_link']) ?>" target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:#2563eb;color:white;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
                <i class="fas fa-video"></i> Join Live Call (Doctor Link)
            </a>
        </div>
        <?php endif; ?>

        <?php if ($is_pending): ?>
        <form method="POST" style="display:flex;gap:10px;">
            <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
            <button type="submit" name="action" value="approve" class="btn btn-approve">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-reject"
                onclick="return confirm('Reject this appointment?')">
                <i class="fas fa-times"></i> Reject
            </button>
        </form>
        <?php else: ?>
        <p style="color:<?= $approval==='approved' ? '#10b981' : '#ef4444' ?>;font-weight:bold;margin:0;">
            <i class="fas <?= $approval==='approved' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
            <?= $approval==='approved' ? 'Approved by you' : 'Rejected by you' ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>
</div>
</body>
</html>
