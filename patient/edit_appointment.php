<?php
// =============================================
// SAJEEFA - Edit Appointment (patient/edit_appointment.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';
require_once '../includes/slots.php';

$error = '';
$app_id = $_GET['id'] ?? ($_POST['appointment_id'] ?? '');

// Load the appointment - must belong to the logged in patient
$stmt = $conn->prepare("SELECT * FROM appointments WHERE appointment_id = ? AND patient_id = ?");
$stmt->bind_param("ss", $app_id, $patient_id);
$stmt->execute();
$appt = $stmt->get_result()->fetch_assoc();

if (!$appt) {
    header("Location: myappointment.php");
    exit();
}

// Only pending appointments can be edited (once a doctor approves/rejects, it's locked)
if ($appt['doctor_approval'] !== 'pending') {
    header("Location: myappointment.php");
    exit();
}

// Fetch doctor for fee lookup
$dstmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$dstmt->bind_param("s", $appt['doctor_id']);
$dstmt->execute();
$doctor = $dstmt->get_result()->fetch_assoc();
$virtual_fee  = $doctor ? $doctor['virtual_fee'] : $appt['fee'];
$physical_fee = $doctor ? $doctor['physical_fee'] : $appt['fee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_date     = $_POST['appDate'];
    $time_slot    = $_POST['timeslot'];
    $consult_type = $_POST['consultType'];
    $today = date('Y-m-d');

    if ($app_date < $today) {
        $error = 'Please pick today or a future date.';
    } else {
        $booked = getBookedSlots($conn, $appt['doctor_id'], $app_date, $appt['appointment_id']);
        if (!isSlotBookable($time_slot, $app_date, $booked)) {
            $error = 'That time slot is no longer available (already booked or already passed). Please choose another slot.';
        }
    }

    if (!$error) {
        $fee = ($consult_type === 'virtual') ? $virtual_fee : $physical_fee;
        $upd = $conn->prepare("UPDATE appointments SET appointment_date=?, time_slot=?, consult_type=?, fee=? WHERE appointment_id=? AND patient_id=?");
        $upd->bind_param("sssiss", $app_date, $time_slot, $consult_type, $fee, $app_id, $patient_id);
        if ($upd->execute()) {
            header("Location: myappointment.php");
            exit();
        } elseif (isDuplicateSlotError($conn)) {
            $error = 'Sorry, another patient just booked that time slot. Please choose a different slot.';
        } else {
            $error = 'Update failed. Please try again.';
        }
    }
    // keep the posted values on screen if there was an error
    $appt['appointment_date'] = $app_date;
    $appt['time_slot'] = $time_slot;
    $appt['consult_type'] = $consult_type;
}

$slotAvailability = getSlotAvailability($conn, $appt['doctor_id'], $appt['appointment_date'], $appt['appointment_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Edit Appointment</title>
<meta charset="UTF-8">
<style>
body{ margin:0; font-family:Arial, sans-serif; background:#f4f7f6; display:flex; min-height:100vh; }
.main-content{ flex:1; padding:25px; }
.slot-container{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:15px; }
.slot{ border:1px solid #0b2a4a; padding:10px 15px; border-radius:8px; cursor:pointer; background:white; }
.slot input{ margin-right:5px; }
.slot.slot-disabled{ border-color:#ccc; background:#f0f0f0; color:#999; cursor:not-allowed; }
.info-box{ background:white; padding:15px; border-radius:10px; margin-bottom:15px; box-shadow:0 2px 5px rgba(0,0,0,0.05); }
.input-style{ width:100%; padding:10px; margin-bottom:10px; border:1px solid #cbd5e1; border-radius:5px; box-sizing:border-box; }
label{ font-size:13px; color:#444; font-weight:bold; }
</style>
</head>
<body>
<?php patientSidebar(); ?>
<div class="main-content">
<h2>Edit Appointment <?= htmlspecialchars($appt['appointment_id']) ?></h2>

<?php if ($error): ?>
<div style="background:#fee;padding:10px;border-radius:8px;margin-bottom:15px;color:#c00;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="info-box">
    <p><b>Doctor:</b> <?= htmlspecialchars($appt['doctor_name']) ?></p>
    <p><b>Physical Fee:</b> <span style="font-weight:bold;color:#137333;">LKR <?= $physical_fee ?></span> &nbsp;|&nbsp; 
       <b>Virtual Fee:</b> <span style="font-weight:bold;color:#1a73e8;">LKR <?= $virtual_fee ?></span></p>
</div>

<form method="POST" id="editForm">
<input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appt['appointment_id']) ?>">

<label>Select Date</label>
<input type="date" name="appDate" id="appDate" class="input-style" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($appt['appointment_date']) ?>">

<label>Consultation Type</label><br>
<label style="font-weight:normal;font-size:14px;margin-right:15px;">
    <input type="radio" name="consultType" value="physical" <?= $appt['consult_type']==='physical' ? 'checked' : '' ?> required> Physical (LKR <?= $physical_fee ?>)
</label>
<label style="font-weight:normal;font-size:14px;">
    <input type="radio" name="consultType" value="virtual" <?= $appt['consult_type']==='virtual' ? 'checked' : '' ?>> Virtual (LKR <?= $virtual_fee ?>)
</label>

<h3 style="margin-top:20px;">Time Slots</h3>
<p style="font-size:12px;color:#888;margin-top:-8px;">Already booked or already-passed slots are grayed out automatically.</p>
<div class="slot-container" id="slotContainer">
    <?php foreach ($slotAvailability as $row): ?>
    <label class="slot <?= $row['available'] ? '' : 'slot-disabled' ?>" data-slot="<?= htmlspecialchars($row['slot']) ?>">
        <input type="radio" name="timeslot" value="<?= htmlspecialchars($row['slot']) ?>"
            <?= $row['available'] ? '' : 'disabled' ?>
            <?= $appt['time_slot'] === $row['slot'] ? 'checked' : '' ?> required>
        <span><?= htmlspecialchars($row['slot']) ?></span>
    </label>
    <?php endforeach; ?>
</div>

<div style="display:flex;gap:10px;margin-top:15px;">
<button type="submit" style="flex:1;padding:12px;background:#0b2a4a;color:white;font-weight:bold;border:none;border-radius:5px;cursor:pointer;font-size:16px;">
    Save Changes
</button>
<a href="myappointment.php" style="flex:1;padding:12px;background:#ccc;color:#333;text-align:center;border-radius:5px;text-decoration:none;font-weight:bold;">
    Cancel
</a>
</div>
</form>
</div>

<script>
const doctorId = <?= json_encode($appt['doctor_id']) ?>;
const appointmentId = <?= json_encode($appt['appointment_id']) ?>;
const dateInput = document.getElementById('appDate');
const slotContainer = document.getElementById('slotContainer');

function refreshSlots() {
    const date = dateInput.value;
    if (!date) return;
    fetch(`get_slots.php?doctor_id=${encodeURIComponent(doctorId)}&date=${encodeURIComponent(date)}&exclude=${encodeURIComponent(appointmentId)}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(item => {
                const label = slotContainer.querySelector(`.slot[data-slot="${item.slot}"]`);
                if (!label) return;
                const input = label.querySelector('input');
                if (item.available) {
                    label.classList.remove('slot-disabled');
                    input.disabled = false;
                } else {
                    label.classList.add('slot-disabled');
                    input.disabled = true;
                    input.checked = false;
                }
            });
        })
        .catch(() => {});
}

dateInput.addEventListener('change', refreshSlots);
</script>
</body>
</html>
