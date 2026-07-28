<?php
// =============================================
// SAJEEFA - Root entry point (index.php)
// SAJEEFA FIX: the project had no file at the root, so opening the base folder URL
// (e.g. http://localhost/online_clinic/) showed a blank directory listing / 403
// instead of the actual site. This just sends visitors to the real home page.
// =============================================
header("Location: home/home.php");
exit();
?>
