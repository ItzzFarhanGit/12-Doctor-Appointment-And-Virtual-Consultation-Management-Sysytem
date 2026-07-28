<?php
// =============================================
// SAJEEFA - About Us (home/about.php)
// =============================================
require_once '../includes/db.php';
?>
<!DOCTYPE html><html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us - Medi Plus</title><script src="https://cdn.tailwindcss.com"></script><style>
.btn-hover:hover{
    transform: scale(1.05);
    transition: 0.3s;
}
</style></head>
<body class="bg-gray-50"><!-- Navigation --><nav class="flex justify-between items-center px-20 py-6 bg-white sticky top-0 z-50 shadow-sm"><div class="flex items-center text-blue-900 font-bold text-2xl">
    <img src="heart.jpeg" alt="Medi Plus Logo" class="w-10 h-10 mr-2 rounded-full">
    Medi Plus
</div>

<div class="space-x-8 font-semibold text-gray-700">
    <a href="home.php" class="hover:text-blue-600">Home</a>
    <a href="about.php" class="text-blue-600 font-bold">About Us</a>
    <a href="services.php" class="hover:text-blue-600">Services</a>
    <a href="connect.php" class="hover:text-blue-600">Connect Us</a>
</div>

<div class="space-x-4 font-semibold text-blue-700">
    <a href="../patient/login.php" class="hover:text-blue-900">Patient Panel</a>
    <a href="../doctor/login.php" class="hover:text-blue-900">Doctor Panel</a>
    <a href="../admin/login.php" class="hover:text-blue-900">Admin Panel</a>
</div>

</nav><!-- Hero Section --><section class="bg-blue-50 py-20 text-center">
    <h1 class="text-5xl font-bold text-blue-900 mb-4">
        About Medi Plus
    </h1><p class="text-gray-600 text-lg max-w-3xl mx-auto">
    Medi Plus is a Doctor Appointment and Virtual Consultation Management System that connects patients with qualified doctors and healthcare services.
</p>

</section><!-- Who We Are --><section class="px-20 py-16">
    <h2 class="text-4xl font-bold text-blue-900 mb-6">
        Who We Are
    </h2><p class="text-gray-600 text-lg leading-8">
    Medi Plus is a modern healthcare platform designed to simplify appointment booking, virtual consultations, patient record management and doctor-patient communication.
</p>

</section><!-- Mission & Vision --><section class="grid md:grid-cols-2 gap-10 px-20 py-10"><div class="bg-white p-8 rounded-2xl shadow-lg">
    <h3 class="text-3xl font-bold text-blue-700 mb-4">
        Our Mission
    </h3>

    <p class="text-gray-600">
        To provide accessible, affordable and quality healthcare services through innovative digital solutions.
    </p>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h3 class="text-3xl font-bold text-blue-700 mb-4">
        Our Vision
    </h3>

    <p class="text-gray-600">
        To become the most trusted digital healthcare platform for patients and doctors.
    </p>
</div>

</section><!-- Why Choose Us --><section class="px-20 py-16"><h2 class="text-4xl font-bold text-center text-blue-900 mb-12">
    Why Choose Medi Plus?
</h2>

<div class="grid md:grid-cols-4 gap-8">

    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
        <h3 class="font-bold text-xl mb-3">Expert Doctors</h3>
        <p>Highly qualified healthcare professionals.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
        <h3 class="font-bold text-xl mb-3">Easy Booking</h3>
        <p>Book appointments quickly and easily.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
        <h3 class="font-bold text-xl mb-3">Virtual Consultation</h3>
        <p>Consult doctors online from anywhere.</p>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg text-center">
        <h3 class="font-bold text-xl mb-3">Secure Records</h3>
        <p>Your medical data is stored safely in our database.</p>
    </div>

</div>

</section>

</body>
</html>
