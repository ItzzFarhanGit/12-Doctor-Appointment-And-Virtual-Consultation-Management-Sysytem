<?php
// =============================================
// SAJEEFA - Reset Doctor Credentials (setup_credentials.php)
// Run ONCE in browser: http://localhost/SAJEEFA/setup_credentials.php
// What it does:
//   1. Sets EVERY doctor's login password to: 123456
//   2. Sets EVERY doctor's login email to: <theirname>@gmail.com
//      (built from each doctor's own full_name, e.g. "Dr. Sarah Johnson" -> sarahjohnson@gmail.com)
// After running, DELETE this file!
// =============================================
require_once 'includes/db.php';

$new_password = '123456';
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

// 1) Reset password for ALL doctors
$stmt = $conn->prepare("UPDATE doctors SET password=?");
$stmt->bind_param("s", $hashed);
$pw_ok = $stmt->execute();
$affected = $conn->affected_rows;

// 2) Build "<name>@gmail.com" for every doctor, from their own full_name
function nameToEmailLocalPart($full_name) {
    $name = preg_replace('/^\s*Dr\.?\s*/i', '', $full_name); // drop "Dr." prefix
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]/', '', $name);          // keep letters/numbers only
    return $name !== '' ? $name : 'doctor';
}

$doctors = $conn->query("SELECT doctor_id, full_name FROM doctors ORDER BY doctor_id")->fetch_all(MYSQLI_ASSOC);

$used_emails = [];   // track emails we assign in this run, to avoid collisions
$email_log   = [];   // for the summary table below

foreach ($doctors as $d) {
    $base = nameToEmailLocalPart($d['full_name']);
    $email = $base . '@gmail.com';

    // If two doctors have the same name-based email, make it unique with the doctor_id
    $suffix = 2;
    while (in_array($email, $used_emails)) {
        $email = $base . $suffix . '@gmail.com';
        $suffix++;
    }
    $used_emails[] = $email;

    $upd = $conn->prepare("UPDATE doctors SET email=? WHERE doctor_id=?");
    $upd->bind_param("ss", $email, $d['doctor_id']);
    $ok = $upd->execute();

    $email_log[] = ['doctor_id' => $d['doctor_id'], 'full_name' => $d['full_name'], 'email' => $email, 'ok' => $ok];
}

// Reload final state to display
$doctors_final = $conn->query("SELECT doctor_id, full_name, email FROM doctors ORDER BY doctor_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Reset Doctor Credentials</title></head>
<body style="font-family:Arial;padding:30px;max-width:650px;margin:40px auto;background:white;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">

    <?php if ($pw_ok): ?>
        <h2 style="color:#10b981;">✅ All Doctor Passwords Reset</h2>
        <p><?= $affected ?> doctor account(s) updated. New password for every doctor: <b><?= htmlspecialchars($new_password) ?></b></p>
    <?php else: ?>
        <h2 style="color:red;">❌ Password reset failed</h2>
        <p><?= htmlspecialchars($conn->error) ?></p>
    <?php endif; ?>

    <div style="background:#f0f9ff;padding:15px;border-radius:8px;margin:15px 0;">
        Every doctor's login email has been set to their own name @gmail.com.
    </div>

    <h3 style="margin-top:25px;">Current Doctor Logins</h3>
    <table style="width:100%;border-collapse:collapse;margin-top:10px;">
        <thead>
            <tr style="background:#0b2a4a;color:white;">
                <th style="padding:8px;text-align:left;">Doctor ID</th>
                <th style="padding:8px;text-align:left;">Name</th>
                <th style="padding:8px;text-align:left;">Login Email</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($doctors_final as $d): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:8px;"><?= htmlspecialchars($d['doctor_id']) ?></td>
                <td style="padding:8px;"><?= htmlspecialchars($d['full_name']) ?></td>
                <td style="padding:8px;"><?= htmlspecialchars($d['email']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top:15px;color:#555;">Every doctor above now logs in with the email shown + password <b><?= htmlspecialchars($new_password) ?></b>.</p>

    <a href="doctor/login.php" style="display:inline-block;margin-top:20px;padding:12px 25px;background:#0b2a4a;color:white;border-radius:8px;text-decoration:none;font-weight:bold;">
        👨‍⚕️ Go to Doctor Login
    </a>

    <p style="margin-top:20px;color:red;font-size:13px;"><b>⚠️ Security: Delete setup_credentials.php after use!</b></p>
</body>
</html>
