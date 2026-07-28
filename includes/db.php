<?php
// =============================================
// SAJEEFA - Database Connection (includes/db.php)
// =============================================
// XAMPP/WAMP default settings - change if needed

// SAJEEFA FIX: PHP 8.1+ (current XAMPP ships 8.1/8.2/8.3) makes mysqli throw an
// uncaught exception on any DB error by default, instead of the classic behaviour
// of returning false from execute()/query(). This whole project's error handling
// (duplicate-booking detection, "email already registered" checks, admin delete
// guards, etc.) is written expecting the classic "check the return value" style -
// without this line, all of that breaks into a fatal white-screen 500 error the
// moment anything goes wrong, instead of showing the friendly message it's supposed to.
mysqli_report(MYSQLI_REPORT_OFF);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP: empty | WAMP: empty
define('DB_NAME', 'online_clinic');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("<div style='font-family:Arial;padding:20px;background:#fee;border:1px solid red;margin:20px;border-radius:8px;'>
        <h3>❌ Database Connection Failed</h3>
        <p>" . $conn->connect_error . "</p>
        <p>Check: phpMyAdmin இல் <b>online_clinic</b> database இருக்கா? 
        <br><a href='../online_clinic.sql'>online_clinic.sql</a> import பண்ணிருக்கீங்களா?</p>
    </div>");
}

$conn->set_charset("utf8mb4");
?>
