<?php
// =============================================
// SAJEEFA - Doctor Register (doctor/register.php)
// =============================================
session_start();

// Already logged in - redirect to dashboard
if (isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/db.php';
require_once '../includes/id_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = trim($_POST['full_name']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $specialty    = trim($_POST['specialty']);

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($specialty)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM doctors WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            // Generate Doctor ID (MAX-based, so it never collides after a deletion)
            $doctor_id = generateNextId($conn, 'doctors', 'doctor_id', 'DOC-', 3);

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into DB (other columns use table defaults)
            $stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, password, specialty) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $doctor_id, $full_name, $email, $hashed_password, $specialty);

            if ($stmt->execute()) {
                $success = 'Registration Successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Register</title>
<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
body{ background:#e6f7ff; display:flex; justify-content:center; align-items:center; min-height:100vh; padding:20px; }
.container{ width:900px; max-width:100%; background:#fff; display:flex; border-radius:15px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.2); }
.left{ width:50%; background:#0b6efd; display:flex; justify-content:center; align-items:center; flex-direction:column; color:white; padding:30px; text-align:center; }
.left img{ width:220px; height:220px; object-fit:cover; border-radius:50%; }
.left h2{ margin-top:20px; }
.left p{ margin-top:8px; font-size:13px; opacity:0.9; }
.right{ width:50%; display:flex; justify-content:center; align-items:center; padding:30px 0; }
.register-box{ width:80%; }
.register-box h1{ color:#0b6efd; margin-bottom:10px; }
.register-box p{ color:gray; margin-bottom:20px; font-size:14px; }
input, select{ width:100%; padding:12px; margin:10px 0; border:1px solid #ccc; border-radius:6px; font-size:15px; box-sizing:border-box; font-family:Arial,sans-serif; }
button{ width:100%; padding:12px; margin-top:15px; background:#0b6efd; color:white; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button:hover{ background:#084298; }
.error{ background:#fee; border:1px solid #f88; padding:10px; border-radius:6px; color:#c00; font-size:13px; margin-bottom:10px; }
.success{ background:#efe; border:1px solid #8c8; padding:10px; border-radius:6px; color:#060; font-size:13px; margin-bottom:10px; }
@media (max-width: 700px){
  .container{ flex-direction:column; }
  .left, .right{ width:100%; }
}
</style>
</head>
<body>
<div class="container">
    <div class="left">
        <img src="doctor1.webp" alt="Doctor">
        <h2>Join as a Doctor!</h2>
        <p>Create your doctor account to manage<br>appointments &amp; patients</p>
    </div>
    <div class="right">
        <div class="register-box">
            <h1>Doctor Register</h1>
            <p>Sign up for a new doctor account</p>

            <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success">✅ <?= htmlspecialchars($success) ?> <a href="login.php" style="color:#0b6efd;font-weight:bold;">Login here</a></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name (e.g. Dr. John Smith)" required
                value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">

                <input type="email" name="email" placeholder="Email" required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

                <input type="text" name="specialty" placeholder="Specialty (e.g. Cardiology)" required
                value="<?= isset($_POST['specialty']) ? htmlspecialchars($_POST['specialty']) : '' ?>">

                <input type="password" name="password" placeholder="Password (min 6 chars)" required>

                <button type="submit">Register</button>
            </form>

            <p style="font-size:12px;text-align:center;margin-top:15px;color:gray;">
                Already have an account? <a href="login.php" style="color:#0b6efd;font-weight:bold;">Login</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
