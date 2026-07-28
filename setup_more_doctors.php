<?php
// =============================================
// SAJEEFA - Add More Doctors (setup_more_doctors.php)
// Run ONCE: http://localhost/SAJEEFA/setup_more_doctors.php
// After running, DELETE this file!
// =============================================
require_once 'includes/db.php';

$doctors = [
    ['DOC-002', 'Dr. Ravi Kumar',     'ravi.kumar@sajeefa.com',     'Dermatology',              1800, 2800, 'Monday - Saturday',           '09:00 AM - 05:00 PM'],
    ['DOC-003', 'Dr. Anjali Perera',  'anjali.perera@sajeefa.com',  'Pediatrics',               1200, 2000, 'Monday - Friday',             '08:00 AM - 04:00 PM'],
    ['DOC-004', 'Dr. Mohamed Farook', 'mohamed.farook@sajeefa.com', 'Orthopedics',              2000, 3000, 'Tuesday - Saturday',          '10:00 AM - 06:00 PM'],
    ['DOC-005', 'Dr. Nisha Fernando', 'nisha.fernando@sajeefa.com', 'ENT (Ear, Nose & Throat)', 1600, 2600, 'Monday - Friday',             '09:00 AM - 05:00 PM'],
    ['DOC-006', 'Dr. Arjun Patel',    'arjun.patel@sajeefa.com',    'Neurology',                2200, 3500, 'Monday, Wednesday, Friday',   '11:00 AM - 07:00 PM'],
];

$demo_password = 'Doctor@123';
$hashed = password_hash($demo_password, PASSWORD_DEFAULT);

$added = [];
$skipped = [];

$stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (?,?,?,?,?,?,?,?,?)");

foreach ($doctors as $d) {
    [$doctor_id, $full_name, $email, $specialty, $virtual_fee, $physical_fee, $avail_days, $avail_time] = $d;

    $exists = $conn->query("SELECT COUNT(*) as c FROM doctors WHERE doctor_id='$doctor_id' OR email='" . $conn->real_escape_string($email) . "'")->fetch_assoc()['c'];
    if ($exists > 0) {
        $skipped[] = $full_name;
        continue;
    }

    $stmt->bind_param("sssssiiss", $doctor_id, $full_name, $email, $hashed, $specialty, $virtual_fee, $physical_fee, $avail_days, $avail_time);
    if ($stmt->execute()) {
        $added[] = $full_name;
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Setup More Doctors</title></head>
<body style="font-family:Arial;padding:30px;max-width:600px;margin:40px auto;background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <h2 style="color:#10b981;">Doctor Setup Complete</h2>

    <?php if (!empty($added)): ?>
    <p><b>Added doctors:</b></p>
    <ul><?php foreach ($added as $n) echo "<li>$n</li>"; ?></ul>
    <div style="background:#f0f9ff;padding:15px;border-radius:8px;margin:15px 0;">
        All new doctor accounts use password: <b><?= htmlspecialchars($demo_password) ?></b>
    </div>
    <?php endif; ?>

    <?php if (!empty($skipped)): ?>
    <p><b>Already existed (skipped):</b></p>
    <ul><?php foreach ($skipped as $n) echo "<li>$n</li>"; ?></ul>
    <?php endif; ?>

    <a href="patient/doctor.php" style="display:inline-block;padding:12px 25px;background:#0b2a4a;color:white;border-radius:8px;text-decoration:none;font-weight:bold;">Go to Doctor Search</a>
    <p style="margin-top:20px;color:red;font-size:13px;"><b>Security: Delete setup_more_doctors.php after use!</b></p>
</body>
</html>
