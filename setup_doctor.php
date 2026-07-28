<?php
// =============================================
// SAJEEFA - Doctor Setup (setup_doctor.php)
// Run ONCE: http://localhost/SAJEEFA/setup_doctor.php
// After running, DELETE this file!
// =============================================
require_once 'includes/db.php';

// Check if doctor already exists
$check = $conn->query("SELECT COUNT(*) as c FROM doctors WHERE doctor_id='DOC-001'")->fetch_assoc()['c'];

if ($check > 0) {
    echo "<div style='font-family:Arial;padding:30px;'>
        <h2>✅ Doctor Already Setup!</h2>
        <p>Dr. Sarah Johnson already exists in the database.</p>
        <a href='doctor/login.php' style='padding:10px 20px;background:#0b2a4a;color:white;border-radius:8px;text-decoration:none;'>Go to Doctor Login</a>
        <p style='margin-top:20px;color:red;'><b>⚠️ DELETE this setup_doctor.php file now!</b></p>
    </div>";
    exit();
}

// Create doctor with properly hashed password
$hashed = password_hash('Sarah123', PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (?,?,?,?,?,?,?,?,?)");
$doctor_id    = 'DOC-001';
$full_name    = 'Dr. Sarah Johnson';
$email        = 'sarah@gmail.com';
$specialty    = 'Cardiology';
$virtual_fee  = 1500;
$physical_fee = 2500;
$avail_days   = 'Monday - Friday';
$avail_time   = '08:00 AM - 06:00 PM';

$stmt->bind_param("sssssiiiss", $doctor_id, $full_name, $email, $hashed, $specialty, $virtual_fee, $physical_fee, $avail_days, $avail_time);

// Fix bind (9 params)
$stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param("sssssiiss", $doctor_id, $full_name, $email, $hashed, $specialty, $virtual_fee, $physical_fee, $avail_days, $avail_time);

if ($stmt->execute()) {
    echo "
    <div style='font-family:Arial;padding:30px;max-width:500px;margin:50px auto;background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);text-align:center;'>
        <h2 style='color:#10b981;'>✅ Doctor Account Created!</h2>
        <div style='background:#f0f9ff;padding:15px;border-radius:8px;text-align:left;margin:15px 0;'>
            <b>Doctor Login Credentials:</b><br><br>
            📧 Email: <b>sarah@gmail.com</b><br>
            🔑 Password: <b>Sarah123</b>
        </div>
        <a href='doctor/login.php' style='display:inline-block;padding:12px 25px;background:#0b2a4a;color:white;border-radius:8px;text-decoration:none;font-weight:bold;'>
            👨‍⚕️ Go to Doctor Login
        </a>
        <br><br>
        <a href='patient/register.php' style='display:inline-block;padding:12px 25px;background:#10b981;color:white;border-radius:8px;text-decoration:none;font-weight:bold;'>
            🏥 Patient Register
        </a>
        <p style='margin-top:20px;color:red;font-size:13px;'><b>⚠️ Security: Delete setup_doctor.php after use!</b></p>
    </div>";
} else {
    echo "<div style='font-family:Arial;padding:30px;color:red;'><h2>❌ Setup Failed</h2><p>" . $conn->error . "</p></div>";
}
?>
