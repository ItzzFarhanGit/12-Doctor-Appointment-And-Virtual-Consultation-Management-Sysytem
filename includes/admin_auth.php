<?php
// includes/admin_auth.php
// Indha file-a every admin page top-la include pannunga
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$admin_id   = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// Sidebar HTML - easy reuse
function adminSidebar() {
    $current = basename($_SERVER['PHP_SELF']);
    $links = [
        'dashboard.php'      => 'Dashboard',
        'doctors.php'        => 'Manage Doctors',
        'patients.php'       => 'Manage Patients',
        'appointments.php'   => 'Manage Appointments',
        'prescriptions.php'  => 'Prescriptions',
        'messages.php'       => 'Contact Messages',
    ];
    echo '
    <div style="width:270px;background:#0b2a4a;color:white;padding:20px;min-height:100vh;flex-shrink:0;">
        <h2 style="margin-bottom:25px;">Admin Panel</h2>';
    foreach ($links as $href => $label) {
        $active = ($current === $href) ? 'background:rgba(255,255,255,0.15);' : '';
        echo '<a href="' . $href . '" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:white;' . $active . '" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'' . ($active ? 'rgba(255,255,255,0.15)' : '') . '\'">' . $label . '</a>';
    }
    echo '<a href="logout.php" style="display:block;padding:12px;margin-bottom:10px;border-radius:10px;text-decoration:none;color:#ff9999;" onmouseover="this.style.background=\'rgba(255,255,255,0.15)\'" onmouseout="this.style.background=\'\'">Logout</a>
    </div>';
}
?>
