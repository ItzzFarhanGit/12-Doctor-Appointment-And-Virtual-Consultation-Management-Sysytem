<?php
// =============================================
// SAJEEFA - Patient List (doctor/patients.php)
// =============================================
require_once '../includes/doctor_auth.php';
require_once '../includes/db.php';

// SAJEEFA FIX: this used to list EVERY registered patient in the system.
// Now it only lists patients who have actually booked an appointment with
// THIS logged-in doctor, along with their latest booking status, so the
// doctor sees exactly who booked them and whether it's pending/approved/rejected.
$stmt = $conn->prepare(
    "SELECT p.patient_id, p.full_name, p.gender, p.phone, p.email, p.registered_date, p.created_at,
            a.appointment_id, a.appointment_date, a.time_slot, a.consult_type, a.doctor_approval
     FROM patients p
     INNER JOIN appointments a ON a.patient_id = p.patient_id
     WHERE a.doctor_id = ?
     ORDER BY a.created_at DESC"
);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient List</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.table-box{ margin-top:20px; background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; margin-top:15px; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:14px; }
table th{ background:#0b2a4a; color:white; }
.gender-badge{ padding:4px 10px; border-radius:4px; font-weight:bold; font-size:12px; text-transform:uppercase; }
.gender-male{ background:#e0f2fe; color:#0369a1; }
.gender-female{ background:#fce7f3; color:#be185d; }
.no-data{ text-align:center; padding:30px; color:#666; }
</style>
</head>
<body>
<div class="container">
<?php doctorSidebar(); ?>
<div class="main">
    <h1>Patient List</h1>
    <div class="table-box">
        <h2>Patients Who Booked With You (<?= count($patients) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Appointment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($patients)): ?>
                <tr><td colspan="7" class="no-data">No patients have booked with you yet.</td></tr>
            <?php else: foreach ($patients as $p):
                $g_class = (strtolower($p['gender'] ?? '') === 'male') ? 'gender-male' : 'gender-female';
                $approval = $p['doctor_approval'];
                $badge_class = $approval === 'approved' ? 'gender-male' : ($approval === 'rejected' ? '' : '');
            ?>
                <tr>
                    <td><b><?= htmlspecialchars($p['patient_id']) ?></b></td>
                    <td><?= htmlspecialchars($p['full_name']) ?></td>
                    <td>
                        <?php if ($p['gender']): ?>
                        <span class="gender-badge <?= $g_class ?>"><?= htmlspecialchars($p['gender']) ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= $p['phone'] ? '<i class="fas fa-phone-alt" style="color:#666;font-size:11px;margin-right:4px;"></i>' . htmlspecialchars($p['phone']) : '—' ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['appointment_date']) ?> (<?= htmlspecialchars($p['time_slot']) ?>) &middot; <?= strtoupper($p['consult_type']) ?></td>
                    <td>
                        <span style="padding:4px 10px;border-radius:20px;font-size:12px;font-weight:bold;
                            background:<?= $approval==='approved' ? '#e8f0fe' : ($approval==='rejected' ? '#fee' : '#fef7e0') ?>;
                            color:<?= $approval==='approved' ? '#1a73e8' : ($approval==='rejected' ? '#c00' : '#b06000') ?>;">
                            <?= strtoupper($approval) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
