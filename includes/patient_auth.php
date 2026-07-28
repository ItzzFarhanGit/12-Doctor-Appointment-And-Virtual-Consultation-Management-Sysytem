<?php
// includes/patient_auth.php
// Indha file-a every patient page top-la include pannunga
session_start();
if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}
$patient_id   = $_SESSION['patient_id'];
$patient_name = $_SESSION['patient_name'];

// Sidebar HTML - easy reuse
function patientSidebar() {
    echo '
    <div style="width:270px;background:#0b2a4a;color:white;padding:20px;min-height:100vh;flex-shrink:0;">
        <h2 style="margin-bottom:25px;">Patient Panel</h2>
        <a href="doctor.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;transition:0.3s;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Search Doctor</a>
        <a href="appointment.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Book Appointment</a>
        <a href="myappointment.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">My Appointments</a>
        <a href="prescription.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Prescription</a>
        <a href="logout.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:#ff9999;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Logout</a>
    </div>';
}
?>
