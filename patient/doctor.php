<?php
// =============================================
// SAJEEFA - Doctor Search (patient/doctor.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE full_name LIKE ? OR specialty LIKE ? ORDER BY full_name");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $doctors = $conn->query("SELECT * FROM doctors ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Find a Doctor</title>
<meta charset="UTF-8">
<style>
body{ margin:0; font-family:Arial, sans-serif; background:#f4f7f6; display:flex; min-height:100vh; }
.main-content{ flex:1; padding:25px; }
.search-box{ display:flex; gap:10px; max-width:650px; margin-bottom:20px; }
.search-box input{ flex:1; padding:12px 15px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; box-sizing:border-box; }
.search-box button{ padding:12px 22px; background:#0b2a4a; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; font-size:14px; }
.search-box .clear-btn{ padding:12px 18px; background:#e2e8f0; color:#333; border:none; border-radius:8px; cursor:pointer; font-size:14px; text-decoration:none; display:inline-flex; align-items:center; }
.doctor-card{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); max-width:650px; margin-bottom:20px; display:flex; gap:20px; align-items:center; }
.doctor-card img{ width:80px; height:80px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.doctor-info h3{ margin:0 0 5px; color:#0b2a4a; }
.doctor-info p{ margin:3px 0; color:#555; font-size:13px; }
.book-btn{ padding:10px 20px; background:#0b2a4a; color:white; border:none; border-radius:8px; cursor:pointer; text-decoration:none; display:inline-block; font-size:14px; font-weight:bold; margin-top:10px; }
</style>
</head>
<body>
<?php patientSidebar(); ?>
<div class="main-content">
<h2>Find a Doctor</h2>
<p style="color:gray;">Welcome, <?= htmlspecialchars($patient_name) ?></p>

<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Search by doctor name or specialty..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
    <a href="doctor.php" class="clear-btn">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($doctors)): ?>
<div style="background:white;padding:30px;border-radius:12px;text-align:center;color:#666;">
    <h3><?= $search !== '' ? 'No doctors match "' . htmlspecialchars($search) . '".' : 'No doctors available yet.' ?></h3>
</div>
<?php else: foreach ($doctors as $d): ?>
<div class="doctor-card">
    <img src="../doctor/drprofile.jpeg" alt="Doctor" onerror="this.src='https://via.placeholder.com/80x80/0b2a4a/white?text=Dr'">
    <div class="doctor-info">
        <h3><?= htmlspecialchars($d['full_name']) ?></h3>
        <p><b>Specialty:</b> <?= htmlspecialchars($d['specialty']) ?></p>
        <p><b>Physical:</b> LKR <?= $d['physical_fee'] ?> &nbsp;|&nbsp; <b>Virtual:</b> LKR <?= $d['virtual_fee'] ?></p>
        <p><?= htmlspecialchars($d['available_days']) ?> | <?= htmlspecialchars($d['available_time']) ?></p>
        <a href="appointment.php?doctor=<?= urlencode($d['doctor_id']) ?>" class="book-btn">Book Appointment</a>
    </div>
</div>
<?php endforeach; endif; ?>
</div>
</body>
</html>
