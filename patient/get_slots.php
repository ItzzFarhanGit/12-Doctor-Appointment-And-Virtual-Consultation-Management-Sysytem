<?php
// =============================================
// SAJEEFA - AJAX Slot Availability (patient/get_slots.php)
// =============================================
require_once '../includes/patient_auth.php';
require_once '../includes/db.php';
require_once '../includes/slots.php';

header('Content-Type: application/json');

$doctor_id = $_GET['doctor_id'] ?? '';
$date      = $_GET['date'] ?? '';
$exclude   = $_GET['exclude'] ?? null; // appointment_id being edited, so its own slot doesn't block itself

if ($doctor_id === '' || $date === '') {
    echo json_encode([]);
    exit();
}

echo json_encode(getSlotAvailability($conn, $doctor_id, $date, $exclude ?: null));
