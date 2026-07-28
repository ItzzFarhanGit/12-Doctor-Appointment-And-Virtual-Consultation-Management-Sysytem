<?php
// =============================================
// SAJEEFA - Patient Register (patient/register.php)
// =============================================
session_start();
require_once '../includes/db.php';
require_once '../includes/id_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];

    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM patients WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            // Generate Patient ID (MAX-based, so it never collides after a deletion)
            $patient_id = generateNextId($conn, 'patients', 'patient_id', 'PID-', 4);

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO patients (patient_id, full_name, email, password, registered_date) VALUES (?, ?, ?, ?, CURDATE())");
            $stmt->bind_param("ssss", $patient_id, $full_name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = 'Registration Successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Account</title>
<meta charset="UTF-8">
</head>
<body style="margin:0;font-family:Arial;background:#f4f6f8;display:flex;align-items:center;justify-content:center;min-height:100vh;">

<div style="background:white;width:900px;max-width:100%;display:flex;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.1);">

<!-- LEFT SIDE FORM -->
<div style="width:50%;padding:40px;">
<h2 style="margin-bottom:5px;">Create Account</h2>
<p style="color:gray;font-size:13px;margin-bottom:20px;">Enter details to register</p>

<?php if ($error): ?>
<div style="background:#fee;border:1px solid #f88;padding:10px;border-radius:8px;margin-bottom:15px;color:#c00;font-size:13px;">
    ❌ <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div style="background:#efe;border:1px solid #8c8;padding:10px;border-radius:8px;margin-bottom:15px;color:#060;font-size:13px;">
    ✅ <?= htmlspecialchars($success) ?> <a href="login.php" style="color:#0B2545;font-weight:bold;">Login here</a>
</div>
<?php endif; ?>

<form method="POST">
<input type="text" name="full_name" placeholder="Full Name" required
value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;">

<input type="email" name="email" placeholder="Email" required
value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;">

<input type="password" name="password" placeholder="Password (min 6 chars)" required
style="width:100%;padding:12px;margin:10px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;">

<label style="font-size:12px;color:gray;">
<input type="checkbox" required> I agree to Terms & Conditions
</label>

<button type="submit"
style="width:100%;padding:12px;background:#0B2545;color:white;border:none;border-radius:10px;margin-top:15px;cursor:pointer;">
Register
</button>
</form>

<p style="font-size:12px;text-align:center;margin-top:15px;color:gray;">
Already have account? <a href="login.php" style="color:#0B2545;font-weight:bold;">Login</a>
</p>
</div>

<!-- RIGHT SIDE IMAGE -->
<div style="width:50%;position:relative;">
<img src="register.png" style="width:100%;height:100%;object-fit:cover;display:block;">
<div style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(11,37,69,0.6);"></div>
</div>

</div>
</body>
</html>
