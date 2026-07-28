<?php
// =============================================
// SAJEEFA - Manage Appointments (admin/appointments.php)
// =============================================
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$message = '';

// DELETE appointment
if (isset($_GET['delete'])) {
    $app_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("s", $app_id);
    $stmt->execute();
    $message = "🗑️ Appointment <b>$app_id</b> deleted.";
}

// Mark payment as paid/unpaid
if (isset($_GET['toggle_payment'])) {
    $app_id = $_GET['toggle_payment'];
    $conn->query("UPDATE appointments SET payment_status = IF(payment_status='paid','unpaid','paid') WHERE appointment_id = '" . $conn->real_escape_string($app_id) . "'");
    $message = "💳 Payment status updated for <b>$app_id</b>.";
}

$status_filter = $_GET['status'] ?? '';
$sql = "SELECT * FROM appointments";
if ($status_filter && in_array($status_filter, ['pending','approved','rejected'])) {
    $sql .= " WHERE doctor_approval = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY created_at DESC";
$appointments = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Appointments</title>
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.filters{ margin-bottom:20px; }
.filters a{ padding:8px 16px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:bold; margin-right:8px; background:white; color:#555; border:1px solid #ddd; }
.filters a.active{ background:#0b2a4a; color:white; border-color:#0b2a4a; }
.table-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:13px; }
table th{ background:#0b2a4a; color:white; }
.badge{ padding:4px 10px; border-radius:20px; font-size:11px; font-weight:bold; }
.badge-pending{ background:#fef7e0; color:#b06000; }
.badge-approved{ background:#e8f0fe; color:#1a73e8; }
.badge-rejected{ background:#fee; color:#c00; }
.pay-paid{ color:#10b981; font-weight:bold; cursor:pointer; text-decoration:underline; }
.pay-unpaid{ color:#ef4444; font-weight:bold; cursor:pointer; text-decoration:underline; }
.action-link{ margin-right:10px; font-weight:bold; text-decoration:none; font-size:12px; }
.del-link{ color:#c00; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:900px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php adminSidebar(); ?>
<div class="main">
    <h1>Manage Appointments</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <div class="filters">
        <a href="appointments.php" class="<?= $status_filter === '' ? 'active' : '' ?>">All</a>
        <a href="?status=pending" class="<?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="?status=approved" class="<?= $status_filter === 'approved' ? 'active' : '' ?>">Approved</a>
        <a href="?status=rejected" class="<?= $status_filter === 'rejected' ? 'active' : '' ?>">Rejected</a>
    </div>

    <div class="table-box">
        <h2>Appointments (<?= count($appointments) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>App ID</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Fee</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="10" style="text-align:center;color:#666;">No appointments found</td></tr>
            <?php else: foreach ($appointments as $a): ?>
                <tr>
                    <td><b><?= htmlspecialchars($a['appointment_id']) ?></b></td>
                    <td><?= htmlspecialchars($a['patient_name']) ?></td>
                    <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($a['time_slot']) ?></td>
                    <td><?= strtoupper($a['consult_type']) ?></td>
                    <td>LKR <?= number_format($a['fee']) ?></td>
                    <td>
                        <a href="?toggle_payment=<?= urlencode($a['appointment_id']) ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                           class="<?= $a['payment_status']==='paid' ? 'pay-paid' : 'pay-unpaid' ?>">
                            <?= strtoupper($a['payment_status']) ?>
                        </a>
                    </td>
                    <td><span class="badge badge-<?= $a['doctor_approval'] ?>"><?= strtoupper($a['doctor_approval']) ?></span></td>
                    <td>
                        <a href="?delete=<?= urlencode($a['appointment_id']) ?>" class="action-link del-link" onclick="return confirm('Delete this appointment?')">Delete</a>
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
