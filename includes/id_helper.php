<?php
// =============================================
// SAJEEFA - Robust ID Generator (includes/id_helper.php)
// =============================================
// SAJEEFA FIX: the old code did "SELECT COUNT(*) FROM table" then used cnt+1 as the
// next ID number (e.g. PID-0001, DOC-001, APP-1001). That breaks the moment ANY row
// is ever deleted (admin can delete doctors/patients/appointments) - COUNT(*) goes
// down, so the "next" ID collides with one that already exists, and the INSERT fails
// with a duplicate-key error (shown to the user as a generic "failed, try again").
//
// Fix: look at the actual MAX numeric suffix already used, and go one past that.
// Deleting rows in the middle no longer causes new IDs to collide with old ones.

function generateNextId($conn, $table, $idColumn, $prefix, $padLength, $startNumber = 1) {
    $substrStart = strlen($prefix) + 1;
    $likePattern = $prefix . '%';

    $stmt = $conn->prepare(
        "SELECT MAX(CAST(SUBSTRING($idColumn, ?) AS UNSIGNED)) AS max_num
         FROM $table WHERE $idColumn LIKE ?"
    );
    $stmt->bind_param("is", $substrStart, $likePattern);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $next = ($row && $row['max_num'] !== null) ? ((int)$row['max_num'] + 1) : $startNumber;

    return $prefix . str_pad($next, $padLength, '0', STR_PAD_LEFT);
}
?>
