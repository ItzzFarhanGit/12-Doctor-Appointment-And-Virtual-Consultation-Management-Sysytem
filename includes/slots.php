<?php
// =============================================
// SAJEEFA - Shared Time Slot Helper (includes/slots.php)
// Ella booking/edit pages um indha logic-a use pannanum
// =============================================

function getAllTimeSlots() {
    // Morning: 08:00 AM - 01:00 PM, Lunch break: 01:00 PM - 03:00 PM, Evening: 03:00 PM - 08:00 PM (night 8 mani)
    // Clean back-to-back 30-min slots, no gaps/overlaps - fixes the broken "11:00-12:30" slot.
    return [
        '08:00-08:30','08:30-09:00','09:00-09:30','09:30-10:00','10:00-10:30',
        '10:30-11:00','11:00-11:30','11:30-12:00','12:00-12:30','12:30-13:00',
        '15:00-15:30','15:30-16:00','16:00-16:30','16:30-17:00','17:00-17:30',
        '17:30-18:00','18:00-18:30','18:30-19:00','19:00-19:30','19:30-20:00',
    ];
}

// Already booked slots for a doctor on a date (rejected ones don't block the slot)
function getBookedSlots($conn, $doctor_id, $date, $exclude_appointment_id = null) {
    $sql = "SELECT time_slot FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND doctor_approval != 'rejected'";
    if ($exclude_appointment_id) {
        $sql .= " AND appointment_id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $doctor_id, $date, $exclude_appointment_id);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $doctor_id, $date);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $booked = [];
    while ($row = $res->fetch_assoc()) {
        $booked[] = $row['time_slot'];
    }
    return $booked;
}

// A slot is bookable only if: not already booked, and (if date = today) start time is after current time,
// and the date itself is not in the past.
function isSlotBookable($slot, $date, $booked) {
    $today = date('Y-m-d');
    if ($date < $today) return false;
    if (in_array($slot, $booked, true)) return false;
    if ($date === $today) {
        $start = explode('-', $slot)[0];
        if ($start <= date('H:i')) return false; // already passed / current time
    }
    return true;
}

// Returns array of ['slot' => '08:00-08:30', 'available' => true/false]
function getSlotAvailability($conn, $doctor_id, $date, $exclude_appointment_id = null) {
    $booked = getBookedSlots($conn, $doctor_id, $date, $exclude_appointment_id);
    $out = [];
    foreach (getAllTimeSlots() as $s) {
        $out[] = ['slot' => $s, 'available' => isSlotBookable($s, $date, $booked)];
    }
    return $out;
}

// True if a DB write failed because the unique slot_lock index blocked it
// (i.e. another patient grabbed the SAME doctor + date + time_slot a split second earlier).
// This is the final safety net on top of the isSlotBookable() check above, so two patients
// can never end up booked into the exact same doctor's exact same time slot.
function isDuplicateSlotError($conn) {
    return $conn->errno === 1062; // MySQL "Duplicate entry" error code
}
?>
