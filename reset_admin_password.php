<?php
// =============================================
// ONE-TIME SCRIPT: Reset admin password
// Place this in the online_clinic/ folder (same level as includes/)
// Open in browser: http://localhost/online_clinic/reset_admin_password.php
// DELETE THIS FILE after running it once!
// =============================================

require_once 'includes/db.php';

$email = 'admin@gmail.com';
$new_password = 'admin123';   // <-- login with this after running

$hash = password_hash($new_password, PASSWORD_DEFAULT);

// Try updating existing row first
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Password updated. Login with: $email / $new_password";
} else {
    // No existing row - insert a fresh admin
    $stmt2 = $conn->prepare("INSERT INTO admins (admin_id, full_name, email, password) VALUES (?, ?, ?, ?)");
    $admin_id = 'ADM-001';
    $full_name = 'System Admin';
    $stmt2->bind_param("ssss", $admin_id, $full_name, $email, $hash);
    if ($stmt2->execute()) {
        echo "✅ New admin created. Login with: $email / $new_password";
    } else {
        echo "❌ Something went wrong: " . $conn->error;
    }
}
?>
