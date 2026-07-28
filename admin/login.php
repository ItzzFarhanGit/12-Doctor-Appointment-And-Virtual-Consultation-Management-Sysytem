<?php
// =============================================
// SAJEEFA - Admin Login (admin/login.php)
// =============================================
session_start();

// Already logged in - redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, admin_id, full_name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Login success - set session
                $_SESSION['admin_id']   = $row['admin_id'];
                $_SESSION['admin_name'] = $row['full_name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<meta charset="UTF-8">
</head>
<body style="margin:0;font-family:Arial;background:#f4f6f8;">
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">
<div style="width:900px;max-width:100%;display:flex;background:white;border-radius:20px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.1);">

<!-- LEFT LOGIN -->
<div style="width:50%;padding:40px;">
<h2 style="margin-bottom:5px;">Admin Login</h2>
<p style="color:gray;font-size:13px;margin-bottom:25px;">Login to the Medi Plus admin panel</p>

<?php if ($error): ?>
<div style="background:#fee;border:1px solid #f88;padding:10px;border-radius:8px;margin-bottom:15px;color:#c00;font-size:13px;">
    ❌ <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="POST">
<label style="font-size:12px;color:gray;">Email</label>
<input type="email" name="email" required
value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
style="width:100%;padding:12px;margin:8px 0 15px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;">

<div style="display:flex;justify-content:space-between;align-items:center;">
    <label style="font-size:12px;color:gray;">Password</label>
</div>
<input type="password" name="password" required
style="width:100%;padding:12px;margin:8px 0 15px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;">

<button type="submit"
style="width:100%;padding:12px;background:#0B2545;color:white;border:none;border-radius:10px;cursor:pointer;">
Login
</button>
</form>

<p style="font-size:12px;text-align:center;margin-top:20px;color:gray;">
Default admin: admin@gmail.com / admin123
</p>
</div>

<!-- RIGHT IMAGE -->
<div style="width:50%;position:relative;">
<img src="../patient/login.webp" style="width:100%;height:100%;object-fit:cover;display:block;">
<div style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(11,37,69,0.75);display:flex;align-items:center;justify-content:center;">
    <h2 style="color:white;text-align:center;padding:20px;">Medi Plus<br>Admin Control Panel</h2>
</div>
</div>

</div>
</div>
</body>
</html>
