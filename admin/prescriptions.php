<?php
// =============================================
// SAJEEFA - Prescriptions (admin/prescriptions.php)
// =============================================
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$message = '';

// DELETE prescription
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM prescriptions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "🗑️ Prescription deleted.";
}

$prescriptions = $conn->query("SELECT * FROM prescriptions ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Prescriptions</title>
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.table-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:13px; vertical-align:top; }
table th{ background:#0b2a4a; color:white; }
.action-link{ font-weight:bold; text-decoration:none; font-size:12px; color:#c00; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:900px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php adminSidebar(); ?>
<div class="main">
    <h1>Prescriptions</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <div class="table-box">
        <h2>All Prescriptions (<?= count($prescriptions) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Doctor</th>
                    <th>Patient</th>
                    <th>Diagnosis</th>
                    <th>Medicines</th>
                    <th>Instructions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($prescriptions)): ?>
                <tr><td colspan="7" style="text-align:center;color:#666;">No prescriptions yet</td></tr>
            <?php else: foreach ($prescriptions as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['prescription_date']) ?></td>
                    <td><?= htmlspecialchars($p['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($p['patient_name']) ?><br><small style="color:gray;"><?= htmlspecialchars($p['patient_phone']) ?></small></td>
                    <td><?= nl2br(htmlspecialchars($p['diagnosis'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($p['medicines'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($p['instructions'])) ?></td>
                    <td>
                        <a href="?delete=<?= $p['id'] ?>" class="action-link" onclick="return confirm('Delete this prescription?')">Delete</a>
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
