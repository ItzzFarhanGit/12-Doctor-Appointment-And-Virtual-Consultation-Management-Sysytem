<?php
// =============================================
// SAJEEFA - Manage Patients (admin/patients.php)
// =============================================
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$message = '';

// DELETE patient
if (isset($_GET['delete'])) {
    $patient_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
    $stmt->bind_param("s", $patient_id);
    if ($stmt->execute()) {
        $message = "🗑️ Patient <b>$patient_id</b> deleted.";
    } elseif ($conn->errno === 1451) {
        // SAJEEFA FIX: foreign key (RESTRICT) blocks deleting a patient who still has
        // appointment history, instead of silently failing while the page claimed success.
        $message = "❌ Cannot delete <b>$patient_id</b> - this patient still has appointment records. Delete those appointments first (Manage Appointments), then try again.";
    } else {
        $message = "❌ Delete failed: " . htmlspecialchars($conn->error);
    }
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM patients WHERE full_name LIKE ? OR email LIKE ? OR patient_id LIKE ? ORDER BY id DESC");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $patients = $conn->query("SELECT * FROM patients ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Patients</title>
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.search-box{ margin-bottom:20px; }
.search-box input{ padding:10px 14px; width:300px; border:1px solid #ddd; border-radius:8px; }
.search-box button{ padding:10px 18px; border:none; background:#0b2a4a; color:white; border-radius:8px; font-weight:bold; cursor:pointer; margin-left:8px; }
.table-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:14px; }
table th{ background:#0b2a4a; color:white; }
.action-link{ margin-right:12px; font-weight:bold; text-decoration:none; font-size:13px; }
.del-link{ color:#c00; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:750px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php adminSidebar(); ?>
<div class="main">
    <h1>Manage Patients</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search by name, email or Patient ID" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <div class="table-box">
        <h2>All Patients (<?= count($patients) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($patients)): ?>
                <tr><td colspan="7" style="text-align:center;color:#666;">No patients found</td></tr>
            <?php else: foreach ($patients as $p): ?>
                <tr>
                    <td><b><?= htmlspecialchars($p['patient_id']) ?></b></td>
                    <td><?= htmlspecialchars($p['full_name']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['gender'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['registered_date']) ?></td>
                    <td>
                        <a href="?delete=<?= urlencode($p['patient_id']) ?>" class="action-link del-link" onclick="return confirm('Delete this patient? This cannot be undone.')">Delete</a>
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
