<?php
// includes/doctor_auth.php
// Every doctor page top-la include pannunga
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}
$doctor_id   = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'];

function doctorSidebar() {
    echo '
    <div style="width:270px;background:#0b2a4a;color:white;padding:20px;min-height:100vh;flex-shrink:0;">
        <h2 style="margin-bottom:25px;">Doctor Panel</h2>
        <a href="dashboard.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Dashboard</a>
        <a href="profile.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">My Profile</a>
        <a href="appointmentrequest.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Appointment Requests</a>
        <a href="patients.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Patient List</a>
        <a href="prescription.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Create Prescription</a>
        <a href="logout.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:#ff9999;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Logout</a>
    </div>';
}
?>
