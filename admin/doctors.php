<?php
// =============================================
// SAJEEFA - Manage Doctors (admin/doctors.php)
// =============================================
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$message = '';

// DELETE doctor
if (isset($_GET['delete'])) {
    $doctor_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $message = "🗑️ Doctor <b>$doctor_id</b> deleted.";
}

// ADD or EDIT doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name     = trim($_POST['full_name']);
    $email         = trim($_POST['email']);
    $specialty     = trim($_POST['specialty']);
    $virtual_fee   = (int)$_POST['virtual_fee'];
    $physical_fee  = (int)$_POST['physical_fee'];
    $available_days = trim($_POST['available_days']);
    $available_time = trim($_POST['available_time']);
    $edit_id       = $_POST['edit_id'] ?? '';

    if (empty($full_name) || empty($email) || empty($specialty)) {
        $message = '❌ Full name, email and specialty are required.';
    } elseif ($edit_id) {
        // Update existing doctor
        if (!empty($_POST['password'])) {
            $hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE doctors SET full_name=?, email=?, specialty=?, virtual_fee=?, physical_fee=?, available_days=?, available_time=?, password=? WHERE doctor_id=?");
            $stmt->bind_param("sssiissss", $full_name, $email, $specialty, $virtual_fee, $physical_fee, $available_days, $available_time, $hashed, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE doctors SET full_name=?, email=?, specialty=?, virtual_fee=?, physical_fee=?, available_days=?, available_time=? WHERE doctor_id=?");
            $stmt->bind_param("sssiisss", $full_name, $email, $specialty, $virtual_fee, $physical_fee, $available_days, $available_time, $edit_id);
        }
        if ($stmt->execute()) {
            $message = "✅ Doctor <b>$edit_id</b> updated.";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
    } else {
        // Add new doctor
        $password = !empty($_POST['password']) ? $_POST['password'] : 'Doctor@123';

        $check = $conn->prepare("SELECT id FROM doctors WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = '❌ A doctor with this email already exists.';
        } else {
            $count_row = $conn->query("SELECT COUNT(*) as cnt FROM doctors")->fetch_assoc();
            $doctor_id = 'DOC-' . str_pad($count_row['cnt'] + 1, 3, '0', STR_PAD_LEFT);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO doctors (doctor_id, full_name, email, password, specialty, virtual_fee, physical_fee, available_days, available_time) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssiiss", $doctor_id, $full_name, $email, $hashed, $specialty, $virtual_fee, $physical_fee, $available_days, $available_time);

            if ($stmt->execute()) {
                $message = "✅ Doctor <b>$doctor_id</b> added. Login password: <b>" . htmlspecialchars($password) . "</b>";
            } else {
                $message = "❌ Error: " . $conn->error;
            }
        }
    }
}

// Load doctor to edit
$edit_doctor = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("s", $_GET['edit']);
    $stmt->execute();
    $edit_doctor = $stmt->get_result()->fetch_assoc();
}

$doctors = $conn->query("SELECT * FROM doctors ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Doctors</title>
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.form-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); max-width:750px; margin-bottom:25px; }
.form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:15px; }
.form-grid label{ font-size:12px; color:gray; display:block; margin-bottom:5px; }
.form-grid input{ width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box; }
.btn{ padding:10px 22px; border-radius:8px; font-weight:bold; cursor:pointer; border:none; margin-top:10px; }
.btn-primary{ background:#0b2a4a; color:white; }
.btn-cancel{ background:#eee; color:#333; text-decoration:none; display:inline-block; padding:10px 22px; border-radius:8px; font-weight:bold; margin-top:10px; margin-left:10px; }
.table-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:14px; }
table th{ background:#0b2a4a; color:white; }
.action-link{ margin-right:12px; font-weight:bold; text-decoration:none; font-size:13px; }
.edit-link{ color:#1a73e8; }
.del-link{ color:#c00; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:750px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php adminSidebar(); ?>
<div class="main">
    <h1>Manage Doctors</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2><?= $edit_doctor ? 'Edit Doctor: ' . htmlspecialchars($edit_doctor['doctor_id']) : 'Add New Doctor' ?></h2>
        <form method="POST">
            <?php if ($edit_doctor): ?>
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_doctor['doctor_id']) ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div>
                    <label>Full Name</label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($edit_doctor['full_name'] ?? '') ?>">
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($edit_doctor['email'] ?? '') ?>">
                </div>
                <div>
                    <label>Specialty</label>
                    <input type="text" name="specialty" required value="<?= htmlspecialchars($edit_doctor['specialty'] ?? '') ?>">
                </div>
                <div>
                    <label>Password <?= $edit_doctor ? '(leave blank to keep current)' : '(default: Doctor@123)' ?></label>
                    <input type="text" name="password" placeholder="<?= $edit_doctor ? 'Leave blank to keep current' : 'Doctor@123' ?>">
                </div>
                <div>
                    <label>Virtual Fee (LKR)</label>
                    <input type="number" name="virtual_fee" value="<?= htmlspecialchars($edit_doctor['virtual_fee'] ?? 1500) ?>">
                </div>
                <div>
                    <label>Physical Fee (LKR)</label>
                    <input type="number" name="physical_fee" value="<?= htmlspecialchars($edit_doctor['physical_fee'] ?? 2500) ?>">
                </div>
                <div>
                    <label>Available Days</label>
                    <input type="text" name="available_days" value="<?= htmlspecialchars($edit_doctor['available_days'] ?? 'Monday - Friday') ?>">
                </div>
                <div>
                    <label>Available Time</label>
                    <input type="text" name="available_time" value="<?= htmlspecialchars($edit_doctor['available_time'] ?? '08:00 - 06:00 PM') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?= $edit_doctor ? 'Update Doctor' : 'Add Doctor' ?></button>
            <?php if ($edit_doctor): ?>
            <a href="doctors.php" class="btn-cancel">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-box">
        <h2>All Doctors</h2>
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Specialty</th>
                    <th>Fees (V/P)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($doctors)): ?>
                <tr><td colspan="6" style="text-align:center;color:#666;">No doctors yet</td></tr>
            <?php else: foreach ($doctors as $d): ?>
                <tr>
                    <td><b><?= htmlspecialchars($d['doctor_id']) ?></b></td>
                    <td><?= htmlspecialchars($d['full_name']) ?></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td><?= htmlspecialchars($d['specialty']) ?></td>
                    <td>LKR <?= number_format($d['virtual_fee']) ?> / <?= number_format($d['physical_fee']) ?></td>
                    <td>
                        <a href="?edit=<?= urlencode($d['doctor_id']) ?>" class="action-link edit-link">Edit</a>
                        <a href="?delete=<?= urlencode($d['doctor_id']) ?>" class="action-link del-link" onclick="return confirm('Delete this doctor? This cannot be undone.')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
