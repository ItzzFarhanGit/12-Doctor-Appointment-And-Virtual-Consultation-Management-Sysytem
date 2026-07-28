<?php
// =============================================
// SAJEEFA - Services (home/services.php)
// =============================================
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services - Medi Plus</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>
.btn-hover:hover{
    transform: scale(1.05);
    transition: 0.3s;
}
</style>

</head>

<body class="bg-gray-50">

<!-- Navigation -->
<nav class="flex justify-between items-center px-20 py-6 bg-white sticky top-0 z-50 shadow-sm">

    <div class="flex items-center text-blue-900 font-bold text-2xl">
        <img src="heart.jpeg" alt="Medi Plus Logo" class="w-10 h-10 mr-2 rounded-full">
        Medi Plus
    </div>

    <div class="space-x-8 font-semibold text-gray-700">
        <a href="home.php" class="hover:text-blue-600">Home</a>
        <a href="about.php" class="hover:text-blue-600">About Us</a>
        <a href="services.php" class="text-blue-600 font-bold">Services</a>
        <a href="connect.php" class="hover:text-blue-600">Connect Us</a>
    </div>

    <div class="space-x-4 font-semibold text-blue-700">
        <a href="../patient/login.php">Patient Panel</a>
        <a href="../doctor/login.php">Doctor Panel</a>
        <a href="../admin/login.php">Admin Panel</a>
    </div>

</nav>

<!-- Header -->
<section class="bg-blue-50 py-20 text-center">
    <h1 class="text-5xl font-bold text-blue-900 mb-4">Our Services</h1>
    <p class="text-gray-600 text-lg max-w-3xl mx-auto">
        We provide modern healthcare services including booking, consultation and patient care.
    </p>
</section>

<!-- Services -->
<section class="px-20 py-16">

<div class="grid md:grid-cols-3 gap-8">

    <!-- Appointment -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <img src="appointment.jpg" class="w-full h-44 object-cover">
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-blue-700 mb-2">Appointment Booking</h3>
            <p class="text-gray-600">
                Book doctor appointments easily online anytime.
            </p>
        </div>
    </div>

    <!-- Virtual Consultation -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <img src="virtual.jpg" class="w-full h-44 object-cover">
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-blue-700 mb-2">Virtual Consultation</h3>
            <p class="text-gray-600">
                Consult doctors via online video or chat system.
            </p>
        </div>
    </div>

    <!-- Specialist Doctors -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <img src="specialist.jpg" class="w-full h-44 object-cover">
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-blue-700 mb-2">Specialist Doctors</h3>
            <p class="text-gray-600">
                Access experienced and qualified specialist doctors.
            </p>
        </div>
    </div>

    <!-- Patient Records -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <img src="record.jpg" class="w-full h-44 object-cover">
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-blue-700 mb-2">Patient Records</h3>
            <p class="text-gray-600">
                Secure management of patient health history.
            </p>
        </div>
    </div>

    <!-- Doctor Consultation -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <img src="doctor.webp" class="w-full h-44 object-cover">
        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-blue-700 mb-2">Doctor Consultation</h3>
            <p class="text-gray-600">
                Our experienced doctors provide medical examinations, accurate diagnosis, personalized treatment plans, prescriptions, and follow-up care to ensure the best healthcare for every patient.
            </p>
        </div>
    </div>

</div>

</section>

</body>
</html>
