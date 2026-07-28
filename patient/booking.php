<?php
// =============================================
// SAJEEFA - Booking Success (patient/booking.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

if (!isset($_SESSION['last_booked_id'])) {
    header("Location: appointment.php");
    exit();
}

$app_id = $_SESSION['last_booked_id'];
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("s", $app_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Booking Successful</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{ margin:0; font-family:sans-serif; background:#f4f7f6; display:flex; justify-content:center; align-items:center; min-height:100vh; }
.success-container{ background:#fff; max-width:650px; width:100%; padding:40px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.08); text-align:center; }
.success-icon{ width:70px; height:70px; background:#10b981; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:32px; margin:auto; margin-bottom:20px; }
.receipt-card{ border:1px dashed #cbd5e1; border-radius:12px; padding:25px; text-align:left; }
.receipt-row, .total-row{ display:flex; justify-content:space-between; margin-bottom:12px; }
.total-row{ margin-top:15px; padding-top:15px; border-top:1px dashed #cbd5e1; font-size:18px; color:#0b2a4a; font-weight:bold; }
.btn-group{ margin-top:25px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
.btn{ padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; border:none; }
</style>
</head>
<body>
<div class="success-container">
    <div class="success-icon"><i class="fas fa-check"></i></div>
    <h1>Booking Successful! 🎉</h1>
    <p style="color:gray;">Your appointment has been confirmed. Doctor will review shortly.</p>

    <?php if ($appt): ?>
    <div class="receipt-card">
        <h3>Receipt Summary</h3>
        <div class="receipt-row"><span>Appointment ID</span><span><b><?= htmlspecialchars($appt['appointment_id']) ?></b></span></div>
        <div class="receipt-row"><span>Patient Name</span><span><?= htmlspecialchars($appt['patient_name']) ?></span></div>
        <div class="receipt-row"><span>Doctor</span><span><?= htmlspecialchars($appt['doctor_name']) ?></span></div>
        <div class="receipt-row"><span>Date</span><span><?= htmlspecialchars($appt['appointment_date']) ?></span></div>
        <div class="receipt-row"><span>Time Slot</span><span><?= htmlspecialchars($appt['time_slot']) ?></span></div>
        <div class="receipt-row"><span>Consultation</span><span><?= strtoupper($appt['consult_type']) ?></span></div>
        <div class="receipt-row"><span>Payment Status</span><span style="color:#10b981;font-weight:bold;">✅ PAID</span></div>
        <div class="total-row"><span>Total Paid</span><span>LKR <?= number_format($appt['fee']) ?></span></div>
    </div>
    <?php endif; ?>

    <div class="btn-group">
        <a href="myappointment.php" class="btn" style="background:#0b2a4a;color:white;">
            <i class="fas fa-calendar-alt"></i> My Appointments
        </a>
        <button class="btn" style="background:#10b981;color:white;" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <a href="appointment.php" class="btn" style="background:#ccc;color:#333;">Book Another</a>
    </div>
</div>
</body>
</html>
