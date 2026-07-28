<?php
// =============================================
// ROBUST FIX: Force-reset admin login (self-verifying)
// Place in online_clinic/ folder (same level as includes/)
// Open in browser: http://localhost/online_clinic/fix_admin_login.php
// DELETE THIS FILE after it says SUCCESS!
// =============================================

require_once 'includes/db.php';

$email = 'admin@gmail.com';
$new_password = 'admin123';

echo "<pre style='font-family:monospace;font-size:14px;'>";

// STEP 0: Make sure the admins table exists
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(10) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "Step 0: admins table OK\n";

// STEP 1: Show any existing row(s) for this email (trimmed/case-insensitive check too)
$check = $conn->prepare("SELECT id, email, password FROM admins WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))");
$check->bind_param("s", $email);
$check->execute();
$existing = $check->get_result();
echo "Step 1: Found " . $existing->num_rows . " existing row(s) for $email\n";
while ($row = $existing->fetch_assoc()) {
    echo "   -> id={$row['id']} email='{$row['email']}' stored_hash={$row['password']}\n";
}

// STEP 2: Delete ALL rows matching this email (avoids stale/duplicate/whitespace issues)
$del = $conn->prepare("DELETE FROM admins WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))");
$del->bind_param("s", $email);
$del->execute();
echo "Step 2: Deleted {$del->affected_rows} old row(s)\n";

// STEP 3: Generate a FRESH hash using THIS server's own PHP (guarantees compatibility)
$hash = password_hash($new_password, PASSWORD_DEFAULT);
echo "Step 3: Generated new hash: $hash\n";

// STEP 4: Insert clean new row
$admin_id = 'ADM-001';
$full_name = 'System Admin';
$ins = $conn->prepare("INSERT INTO admins (admin_id, full_name, email, password) VALUES (?, ?, ?, ?)");
$ins->bind_param("ssss", $admin_id, $full_name, $email, $hash);
$ins->execute();
echo "Step 4: Inserted new admin row (id={$conn->insert_id})\n";

// STEP 5: SELF-TEST - read it back and verify right here, right now
$verify = $conn->prepare("SELECT password FROM admins WHERE email = ?");
$verify->bind_param("s", $email);
$verify->execute();
$result = $verify->get_result()->fetch_assoc();
$stored_hash = $result['password'];

$test = password_verify($new_password, $stored_hash);

echo "Step 5: password_verify('$new_password', stored_hash) = " . ($test ? "TRUE ✅" : "FALSE ❌") . "\n";

echo "</pre>";

if ($test) {
    echo "<h2 style='color:green;font-family:Arial;'>✅ SUCCESS - login now works</h2>";
    echo "<p style='font-family:Arial;'>Email: <b>$email</b><br>Password: <b>$new_password</b></p>";
    echo "<p style='font-family:Arial;'>Go to <a href='admin/login.php'>admin/login.php</a> and log in. Then DELETE this file.</p>";
} else {
    echo "<h2 style='color:red;font-family:Arial;'>❌ Still failing - this points to a deeper issue</h2>";
    echo "<p style='font-family:Arial;'>This would mean your PHP/MySQL setup itself has a problem (e.g. wrong PHP version, or admin/login.php is pointing at a different database). Please share this full page output.</p>";
}
?>
