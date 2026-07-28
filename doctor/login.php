<?php
// =============================================
// SAJEEFA - Doctor Login (doctor/login.php)
// =============================================
session_start();
if (isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, doctor_id, full_name, password, profile_image FROM doctors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['doctor_id']    = $row['doctor_id'];
            $_SESSION['doctor_name']  = $row['full_name'];
            $_SESSION['doctor_image'] = $row['profile_image'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Login</title>
<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
body{ background:#e6f7ff; display:flex; justify-content:center; align-items:center; height:100vh; }
.container{ width:900px; height:500px; background:#fff; display:flex; border-radius:15px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.2); }
.left{ width:50%; background:#0b6efd; display:flex; justify-content:center; align-items:center; flex-direction:column; color:white; }
.left img{ width:280px; height:280px; object-fit:cover; border-radius:50%; }
.left h2{ margin-top:20px; }
.right{ width:50%; display:flex; justify-content:center; align-items:center; }
.login-box{ width:80%; }
.login-box h1{ color:#0b6efd; margin-bottom:10px; }
.login-box p{ color:gray; margin-bottom:20px; font-size:14px; }
input{ width:100%; padding:12px; margin:10px 0; border:1px solid #ccc; border-radius:6px; font-size:15px; box-sizing:border-box; }
button{ width:100%; padding:12px; margin-top:15px; background:#0b6efd; color:white; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button:hover{ background:#084298; }
.error{ background:#fee; border:1px solid #f88; padding:10px; border-radius:6px; color:#c00; font-size:13px; margin-bottom:10px; }
</style>
</head>
<body>
<div class="container">
    <div class="left">
        <img src="drprofile.jpeg" alt="Doctor">
        <h2>Welcome, Doctor!</h2>
    </div>
    <div class="right">
        <div class="login-box">
            <h1>Doctor Login</h1>
            <p>Sign in to your doctor account</p>

            <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="email" name="email" placeholder="Email" required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>

            <p style="font-size:12px;text-align:center;margin-top:15px;color:gray;">
                Don't have account? <a href="register.php" style="color:#0b6efd;font-weight:bold;">Register</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
