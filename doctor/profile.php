<?php
// =============================================
// SAJEEFA - Doctor Profile (doctor/profile.php)
// =============================================
require_once '../includes/doctor_auth.php';
require_once '../includes/db.php';

$success = '';
$error   = '';

// Get doctor profile
$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

// ---- Handle Profile Update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name      = trim($_POST['full_name']);
    $specialty      = trim($_POST['specialty']);
    $virtual_fee    = intval($_POST['virtual_fee']);
    $physical_fee   = intval($_POST['physical_fee']);
    $available_days = trim($_POST['available_days']);
    $available_time = trim($_POST['available_time']);

    $upd = $conn->prepare("UPDATE doctors SET full_name=?, specialty=?, virtual_fee=?, physical_fee=?, available_days=?, available_time=? WHERE doctor_id=?");
    $upd->bind_param("ssiisss", $full_name, $specialty, $virtual_fee, $physical_fee, $available_days, $available_time, $doctor_id);

    if ($upd->execute()) {
        $_SESSION['doctor_name'] = $full_name;
        $doctor_name = $full_name;
        $success = 'Profile updated successfully!';
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
    } else {
        $error = 'Profile update failed.';
    }
}

// ---- Handle Password Change ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $doctor['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE doctors SET password=? WHERE doctor_id=?");
        $upd->bind_param("ss", $new_hash, $doctor_id);
        if ($upd->execute()) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Password change failed.';
        }
    }
}

// ---- Handle Email (Username) Change ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $new_email       = trim($_POST['new_email']);
    $confirm_password = $_POST['email_confirm_password'];

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!password_verify($confirm_password, $doctor['password'])) {
        $error = 'Password incorrect. Cannot change email.';
    } else {
        // Check if email already taken
        $chk = $conn->prepare("SELECT id FROM doctors WHERE email=? AND doctor_id != ?");
        $chk->bind_param("ss", $new_email, $doctor_id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = 'This email is already in use.';
        } else {
            $upd = $conn->prepare("UPDATE doctors SET email=? WHERE doctor_id=?");
            $upd->bind_param("ss", $new_email, $doctor_id);
            if ($upd->execute()) {
                $success = 'Email (username) updated to: ' . htmlspecialchars($new_email);
                // Reload doctor data
                $stmt->execute();
                $doctor = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Email update failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Doctor Profile</title>
<meta charset="UTF-8">
<style>
body{ margin:0; font-family:Arial; background:#f4f7f6; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.card{ width:450px; background:white; padding:25px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:25px; }
.card img{ width:100px; height:100px; border-radius:50%; object-fit:cover; }
input{ width:100%; padding:10px; margin:6px 0 12px; border-radius:8px; border:1px solid #ccc; box-sizing:border-box; font-size:14px; }
input:focus{ border-color:#0b2a4a; outline:none; }
label{ font-size:13px; font-weight:bold; color:#444; display:block; }
.btn{ padding:11px; width:100%; color:white; border:none; border-radius:8px; cursor:pointer; font-size:15px; font-weight:bold; }
.btn-blue{ background:#0b2a4a; }
.btn-orange{ background:#f59e0b; }
.btn-green{ background:#10b981; }
.btn:hover{ opacity:0.9; }
.view-row{ display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f0f0f0; font-size:14px; }
.section-title{ font-size:16px; font-weight:bold; color:#0b2a4a; margin:0 0 15px; padding-bottom:8px; border-bottom:2px solid #e0e7f0; }
.alert-success{ background:#efe; padding:12px; border-radius:8px; margin-bottom:15px; color:#060; border-left:4px solid #10b981; }
.alert-error{ background:#fee; padding:12px; border-radius:8px; margin-bottom:15px; color:#c00; border-left:4px solid #ef4444; }
.current-email{ background:#f0f4ff; padding:10px; border-radius:8px; margin-bottom:12px; font-size:13px; color:#0b2a4a; }
</style>
</head>
<body>
<div class="container">
<?php doctorSidebar(); ?>
<div class="main">
<h2>👨‍⚕️ Doctor Profile</h2>

<?php if ($success): ?>
<div class="alert-success" style="max-width:450px;">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert-error" style="max-width:450px;">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($doctor): ?>

<!-- ===== PROFILE VIEW ===== -->
<div class="card">
    <img src="drprofile.jpeg" alt="Doctor">
    <h3 style="margin:10px 0 3px;"><?= htmlspecialchars($doctor['full_name']) ?></h3>
    <p style="color:gray;margin:0 0 15px;font-size:13px;"><?= htmlspecialchars($doctor['specialty']) ?></p>

    <div class="view-row"><span><b>Email (Username):</b></span><span><?= htmlspecialchars($doctor['email']) ?></span></div>
    <div class="view-row"><span><b>Virtual Fee:</b></span><span>LKR <?= $doctor['virtual_fee'] ?></span></div>
    <div class="view-row"><span><b>Physical Fee:</b></span><span>LKR <?= $doctor['physical_fee'] ?></span></div>
    <div class="view-row"><span><b>Days:</b></span><span><?= htmlspecialchars($doctor['available_days']) ?></span></div>
    <div class="view-row"><span><b>Time:</b></span><span><?= htmlspecialchars($doctor['available_time']) ?></span></div>
</div>

<!-- ===== EDIT PROFILE ===== -->
<div class="card">
    <p class="section-title">✏️ Edit Profile Info</p>
    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($doctor['full_name']) ?>" required>

        <label>Specialty</label>
        <input type="text" name="specialty" value="<?= htmlspecialchars($doctor['specialty']) ?>" required>

        <label>Virtual Fee (LKR)</label>
        <input type="number" name="virtual_fee" value="<?= $doctor['virtual_fee'] ?>" required>

        <label>Physical Fee (LKR)</label>
        <input type="number" name="physical_fee" value="<?= $doctor['physical_fee'] ?>" required>

        <label>Available Days</label>
        <input type="text" name="available_days" value="<?= htmlspecialchars($doctor['available_days']) ?>" placeholder="Monday - Friday">

        <label>Available Time</label>
        <input type="text" name="available_time" value="<?= htmlspecialchars($doctor['available_time']) ?>" placeholder="08:00 AM - 06:00 PM">

        <button type="submit" name="update_profile" class="btn btn-blue">💾 Update Profile</button>
    </form>
</div>

<!-- ===== CHANGE EMAIL (USERNAME) ===== -->
<div class="card">
    <p class="section-title">📧 Change Email (Username)</p>
    <div class="current-email">Current Email: <b><?= htmlspecialchars($doctor['email']) ?></b></div>
    <form method="POST">
        <label>New Email Address</label>
        <input type="email" name="new_email" placeholder="Enter new email" required>

        <label>Confirm with Current Password</label>
        <input type="password" name="email_confirm_password" placeholder="Enter your password to confirm" required>

        <button type="submit" name="change_email" class="btn btn-orange">📧 Update Email</button>
    </form>
</div>

<!-- ===== CHANGE PASSWORD ===== -->
<div class="card">
    <p class="section-title">🔑 Change Password</p>
    <form method="POST">
        <label>Current Password</label>
        <input type="password" name="current_password" placeholder="Enter current password" required>

        <label>New Password <span style="color:gray;font-weight:normal;">(min 6 characters)</span></label>
        <input type="password" name="new_password" placeholder="Enter new password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" placeholder="Re-enter new password" required>

        <button type="submit" name="change_password" class="btn btn-green">🔑 Change Password</button>
    </form>
</div>

<?php endif; ?>
</div>
</div>
</body>
</html>
