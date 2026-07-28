<?php
// =============================================
// SAJEEFA - Doctor Dashboard (doctor/dashboard.php)
// =============================================
require_once '../includes/doctor_auth.php';
require_once '../includes/db.php';

// Dashboard stats from DB
$today = date('Y-m-d');

// SAJEEFA FIX: every stat below is scoped to the logged-in doctor (doctor_id)
// so each doctor only sees their OWN patients' appointments, not every doctor's.
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM appointments WHERE doctor_id=?");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$total_appts = $stmt->get_result()->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM appointments WHERE doctor_id=? AND doctor_approval='pending'");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$pending = $stmt->get_result()->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM appointments WHERE doctor_id=? AND appointment_date=?");
$stmt->bind_param("ss", $doctor_id, $today); $stmt->execute();
$today_count = $stmt->get_result()->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM appointments WHERE doctor_id=? AND doctor_approval='approved'");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$approved = $stmt->get_result()->fetch_assoc()['c'];

// Distinct patients who have booked with THIS doctor
$stmt = $conn->prepare("SELECT COUNT(DISTINCT patient_id) as c FROM appointments WHERE doctor_id=?");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$total_patients = $stmt->get_result()->fetch_assoc()['c'];

$stmt = $conn->prepare("SELECT SUM(fee) as s FROM appointments WHERE doctor_id=? AND payment_status='paid'");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$total_revenue = $stmt->get_result()->fetch_assoc()['s'] ?? 0;

// Recent appointments booked with THIS doctor only
$stmt = $conn->prepare("SELECT * FROM appointments WHERE doctor_id=? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("s", $doctor_id); $stmt->execute();
$recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Doctor Dashboard</title>
<meta charset="UTF-8">
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.cards{ display:flex; flex-wrap:wrap; gap:20px; }
.card{ background:white; padding:20px; width:200px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.card h3{ margin:0 0 10px; font-size:16px; color:#555; }
.card p{ font-size:26px; font-weight:bold; color:#0b2a4a; margin:0; }
.table-box{ margin-top:30px; background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:14px; }
table th{ background:#0b2a4a; color:white; }
.badge{ padding:4px 10px; border-radius:20px; font-size:12px; font-weight:bold; }
.badge-pending{ background:#fef7e0; color:#b06000; }
.badge-approved{ background:#e8f0fe; color:#1a73e8; }
.badge-rejected{ background:#fee; color:#c00; }
</style>
</head>
<body>
<div class="container">
<?php doctorSidebar(); ?>
<div class="main">
    <h1>Doctor Dashboard</h1>
    <p style="color:gray;">Welcome, <?= htmlspecialchars($doctor_name) ?></p>

    <div class="cards">
        <div class="card"><h3>Total Appointments</h3><p><?= $total_appts ?></p></div>
        <div class="card"><h3>Pending Requests</h3><p style="color:#f59e0b;"><?= $pending ?></p></div>
        <div class="card"><h3>Today's Appointments</h3><p style="color:#0b6efd;"><?= $today_count ?></p></div>
        <div class="card"><h3>Approved</h3><p style="color:#10b981;"><?= $approved ?></p></div>
        <div class="card"><h3>Total Patients</h3><p><?= $total_patients ?></p></div>
        <div class="card"><h3>Revenue</h3><p style="font-size:18px;">LKR <?= number_format($total_revenue) ?></p></div>
    </div>

    <div class="table-box">
        <h2>Recent Appointments</h2>
        <table>
            <thead>
                <tr>
                    <th>App ID</th>
                    <th>Patient Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Fee</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="7" style="text-align:center;color:#666;">No appointments yet</td></tr>
            <?php else: foreach ($recent as $a): ?>
                <tr>
                    <td><b><?= htmlspecialchars($a['appointment_id']) ?></b></td>
                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($a['time_slot']) ?></td>
                    <td><?= strtoupper($a['consult_type']) ?></td>
                    <td>LKR <?= number_format($a['fee']) ?></td>
                    <td>
                        <span class="badge badge-<?= $a['doctor_approval'] ?>">
                            <?= strtoupper($a['doctor_approval']) ?>
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
