<?php
// =============================================
// SAJEEFA - One-time migration for an EXISTING database
// Run this ONCE by opening: http://localhost/online_clinic/migrate_fixes.php
// Safe to run even if some parts are already applied (it checks first).
// DELETE THIS FILE after it says all steps are done.
// =============================================

require_once 'includes/db.php';

echo "<pre style='font-family:monospace;font-size:14px;'>";

// STEP 1: Add slot_lock generated column + unique index (stops double-booking at DB level)
$col_check = $conn->query("SHOW COLUMNS FROM appointments LIKE 'slot_lock'");
if ($col_check && $col_check->num_rows > 0) {
    echo "Step 1: slot_lock column already exists - skipped\n";
} else {
    $ok = $conn->query("ALTER TABLE appointments
        ADD COLUMN slot_lock VARCHAR(80) GENERATED ALWAYS AS (
            CASE WHEN doctor_approval <> 'rejected'
                 THEN CONCAT(doctor_id, '|', appointment_date, '|', time_slot)
                 ELSE NULL END
        ) STORED,
        ADD UNIQUE KEY ux_slot_lock (slot_lock)");
    echo $ok
        ? "Step 1: slot_lock column + unique index added - double-booking now impossible ✅\n"
        : "Step 1: FAILED - " . $conn->error . "\n   (If this says 'Duplicate entry', it means two active appointments already share the same doctor+date+time_slot in your data - reject/cancel one of them, then re-run this file.)\n";
}

// STEP 2: Make sure admin login is admin@gmail.com / admin123
$email = 'admin@gmail.com';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_DEFAULT);

$del = $conn->prepare("DELETE FROM admins WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))");
$del->bind_param("s", $email);
$del->execute();

$admin_id = 'ADM-001';
$full_name = 'System Admin';
$ins = $conn->prepare("INSERT INTO admins (admin_id, full_name, email, password) VALUES (?, ?, ?, ?)");
$ins->bind_param("ssss", $admin_id, $full_name, $email, $hash);
$ins->execute();
echo "Step 2: Admin login set to $email / $new_password ✅\n";

echo "</pre>";
echo "<h2 style='color:green;font-family:Arial;'>Migration complete</h2>";
echo "<p style='font-family:Arial;'>Go to <a href='admin/login.php'>admin/login.php</a> and log in with <b>admin@gmail.com / admin123</b>. Then delete this file (migrate_fixes.php) from your server.</p>";
?>
