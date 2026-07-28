<?php
// =============================================
// SAJEEFA - Payment (patient/payment.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

if (!isset($_SESSION['pending_appointment_id'])) {
    header("Location: appointment.php");
    exit();
}

$app_id = $_SESSION['pending_appointment_id'];
$fee    = $_SESSION['pending_fee'];
$success = '';
$error   = '';

// Get appointment details
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("s", $app_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mark payment as paid
    $upd = $conn->prepare("UPDATE appointments SET payment_status = 'paid' WHERE appointment_id = ?");
    $upd->bind_param("s", $app_id);
    if ($upd->execute()) {
        // Clear pending session
        unset($_SESSION['pending_appointment_id']);
        unset($_SESSION['pending_fee']);
        $_SESSION['last_booked_id'] = $app_id;
        header("Location: booking.php");
        exit();
    } else {
        $error = 'Payment update failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Payment</title>
<meta charset="UTF-8">
<style>
body{ margin:0;font-family:Arial;background:#f4f7f6; display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; display:flex; align-items:flex-start; }
.card-box{ max-width:500px; width:100%; background:white; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
input{ width:100%; padding:12px; margin-top:5px; margin-bottom:15px; border:1px solid #ccc; border-radius:8px; outline:none; box-sizing:border-box; }
.pay-btn{ width:100%; padding:12px; background:#0b2a4a; color:white; border:none; border-radius:8px; font-size:16px; cursor:pointer; }
.summary{ background:#f0f4ff; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px; }
</style>
</head>
<body>
<?php patientSidebar(); ?>
<div class="main">
<div class="card-box">
<h2>💳 Payment</h2>

<?php if ($appt): ?>
<div class="summary">
    <b>Appointment Summary</b><br>
    Doctor: <?= htmlspecialchars($appt['doctor_name']) ?><br>
    Date: <?= htmlspecialchars($appt['appointment_date']) ?> | <?= htmlspecialchars($appt['time_slot']) ?><br>
    Type: <?= strtoupper($appt['consult_type']) ?><br>
    <b style="color:#0b2a4a;">Total: LKR <?= number_format($appt['fee']) ?></b>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="background:#fee;padding:10px;border-radius:8px;margin-bottom:15px;color:#c00;">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <label>Card Holder Name</label>
    <input type="text" placeholder="Enter Name" required>

    <label>Card Number</label>
    <input type="text" maxlength="16" placeholder="1234 5678 9012 3456" required>

    <div style="display:flex;gap:10px;">
        <div style="flex:1;">
            <label>Expiry Date</label>
            <input type="text" id="expiry" maxlength="5" placeholder="MM/YY" required>
        </div>
        <div style="flex:1;">
            <label>CVV</label>
            <input type="password" maxlength="3" placeholder="123" required>
        </div>
    </div>

    <button type="submit" class="pay-btn">Pay LKR <?= number_format($fee) ?> Now</button>
</form>
</div>
</div>

<script>
document.getElementById("expiry").addEventListener("input", function(e) {
    let value = e.target.value.replace(/[^0-9]/g, "");
    if (value.length >= 3) { value = value.substring(0,2) + "/" + value.substring(2,4); }
    e.target.value = value;
});
</script>
</body>
</html>
