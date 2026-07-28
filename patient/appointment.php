<?php
// =============================================
// SAJEEFA - Book Appointment (patient/appointment.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';
require_once '../includes/slots.php';
require_once '../includes/id_helper.php';

$success = '';
$error   = '';

// Doctor comes from the search page link (appointment.php?doctor=DOC-00X).
// Falls back to DOC-001 so old links / direct visits still work.
$requested_doctor_id = $_GET['doctor'] ?? ($_POST['doctor_id'] ?? 'DOC-001');

$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("s", $requested_doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    $doctor = $conn->query("SELECT * FROM doctors ORDER BY full_name LIMIT 1")->fetch_assoc();
}

$doctor_name  = $doctor ? $doctor['full_name'] : 'Dr. Sarah Johnson';
$doctor_id    = $doctor ? $doctor['doctor_id'] : 'DOC-001';
$virtual_fee  = $doctor ? $doctor['virtual_fee'] : 1500;
$physical_fee = $doctor ? $doctor['physical_fee'] : 2500;
$specialty    = $doctor ? $doctor['specialty'] : 'General';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_date      = $_POST['appDate'];
    $time_slot     = $_POST['timeslot'];
    $consult_type  = $_POST['consultType'];
    $pat_name      = trim($_POST['patientName']);
    $pat_email     = trim($_POST['patientEmail']);
    $pat_phone     = trim($_POST['patientPhone']);
    $pat_gender    = $_POST['patientGender'];

    $today = date('Y-m-d');

    if ($app_date < $today) {
        $error = 'Please pick today or a future date.';
    } else {
        $booked = getBookedSlots($conn, $doctor_id, $app_date);
        if (!isSlotBookable($time_slot, $app_date, $booked)) {
            $error = 'That time slot is no longer available (already booked or already passed). Please choose another slot.';
        }
    }

    if (!$error) {
        $fee = ($consult_type === 'virtual') ? $virtual_fee : $physical_fee;

        // SAJEEFA FIX: was COUNT(*)-based, which collided with existing IDs after any
        // appointment got deleted (admin can delete appointments). Now MAX-based.
        $app_id = generateNextId($conn, 'appointments', 'appointment_id', 'APP-', 4, 1001);

        $stmt = $conn->prepare("INSERT INTO appointments 
            (appointment_id, patient_id, doctor_id, patient_name, doctor_name, appointment_date, time_slot, consult_type, fee, payment_status, doctor_approval) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', 'pending')");
        $stmt->bind_param("ssssssssi",
            $app_id, $patient_id, $doctor_id,
            $pat_name, $doctor_name,
            $app_date, $time_slot, $consult_type, $fee
        );

        if ($stmt->execute()) {
            $upd = $conn->prepare("UPDATE patients SET phone=?, gender=? WHERE patient_id=?");
            $upd->bind_param("sss", $pat_phone, $pat_gender, $patient_id);
            $upd->execute();

            $_SESSION['pending_appointment_id'] = $app_id;
            $_SESSION['pending_fee'] = $fee;
            $_SESSION['last_booked_id'] = $app_id;

            header("Location: payment.php");
            exit();
        } elseif (isDuplicateSlotError($conn)) {
            // Another patient booked this exact doctor + date + time_slot a split-second
            // before this request finished - the DB's unique slot_lock caught it.
            $error = 'Sorry, another patient just booked that time slot. Please choose a different slot.';
            $slotAvailability = getSlotAvailability($conn, $doctor_id, $app_date);
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
}

if (!isset($slotAvailability)) {
    $display_date = $_POST['appDate'] ?? date('Y-m-d');
    $slotAvailability = getSlotAvailability($conn, $doctor_id, $display_date);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Book Appointment</title>
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
<h2>Book Appointment</h2>

<?php if ($error): ?>
<div style="background:#fee;padding:10px;border-radius:8px;margin-bottom:15px;color:#c00;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="info-box">
    <p><b>Doctor:</b> <?= htmlspecialchars($doctor_name) ?></p>
    <p><b>Specialty:</b> <?= htmlspecialchars($specialty) ?></p>
    <p><b>Physical Fee:</b> <span style="font-weight:bold;color:#137333;">LKR <?= $physical_fee ?></span> &nbsp;|&nbsp; 
       <b>Virtual Fee:</b> <span style="font-weight:bold;color:#1a73e8;">LKR <?= $virtual_fee ?></span></p>
    <p><b>Logged in as:</b> <?= htmlspecialchars($patient_name) ?> (<?= $patient_id ?>)</p>
</div>

<form method="POST" id="bookingForm">
<input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor_id) ?>">

<label>Select Date</label>
<input type="date" name="appDate" id="appDate" class="input-style" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['appDate'] ?? date('Y-m-d')) ?>">

<label>Consultation Type</label><br>
<label style="font-weight:normal;font-size:14px;margin-right:15px;">
    <input type="radio" name="consultType" value="physical" required> Physical (LKR <?= $physical_fee ?>)
</label>
<label style="font-weight:normal;font-size:14px;">
    <input type="radio" name="consultType" value="virtual"> Virtual (LKR <?= $virtual_fee ?>)
</label>

<h3 style="margin-top:20px;">Time Slots</h3>
<p style="font-size:12px;color:#888;margin-top:-8px;">Already booked or already-passed slots are grayed out automatically.</p>
<div class="slot-container" id="slotContainer">
    <?php foreach ($slotAvailability as $row): ?>
    <label class="slot <?= $row['available'] ? '' : 'slot-disabled' ?>" data-slot="<?= htmlspecialchars($row['slot']) ?>">
        <input type="radio" name="timeslot" value="<?= htmlspecialchars($row['slot']) ?>" <?= $row['available'] ? '' : 'disabled' ?> required>
        <span><?= htmlspecialchars($row['slot']) ?></span>
    </label>
    <?php endforeach; ?>
</div>

<h3>Patient Details</h3>
<input type="text" name="patientName" placeholder="Full Name" class="input-style" required>
<input type="email" name="patientEmail" placeholder="Email" class="input-style" required>
<input type="tel" name="patientPhone" placeholder="07XXXXXXXX" maxlength="10" pattern="[0-9]{10}" class="input-style" required>
<select name="patientGender" class="input-style" required>
    <option value="">-- Select Gender --</option>
    <option value="Male">Male</option>
    <option value="Female">Female</option>
    <option value="Other">Other</option>
</select>

<button type="submit" style="width:100%;padding:12px;background:#0b2a4a;color:white;font-weight:bold;border:none;border-radius:5px;cursor:pointer;margin-top:10px;font-size:16px;">
    Confirm & Proceed to Payment
</button>
</form>
</div>

<script>
const doctorId = <?= json_encode($doctor_id) ?>;
const dateInput = document.getElementById('appDate');
const slotContainer = document.getElementById('slotContainer');

function refreshSlots() {
    const date = dateInput.value;
    if (!date) return;
    fetch(`get_slots.php?doctor_id=${encodeURIComponent(doctorId)}&date=${encodeURIComponent(date)}`)
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
