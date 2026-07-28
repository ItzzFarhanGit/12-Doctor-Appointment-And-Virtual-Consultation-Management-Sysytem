<?php
// =============================================
// SAJEEFA - Home Page (home/home.php)
// =============================================
require_once '../includes/db.php';

// Fetch a few doctors to show on the home page
$doctors = [];
$result = $conn->query("SELECT full_name, specialty, profile_image FROM doctors ORDER BY id DESC LIMIT 4");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medi Plus Landing Page</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{
            margin:0;
            font-family:Segoe UI;
        }

        /* HERO */
        .hero-container{
            display:grid;
            grid-template-columns:1.1fr 0.9fr;
            align-items:center;
            padding:40px 8%;
            gap:40px;
        }

        .main-title{
            font-size:56px;
            font-weight:800;
            color:#1e3a8a;
            line-height:1.1;
        }

        .description{
            color:#6b7280;
            margin:20px 0;
        }

        .btn{
            padding:12px 28px;
            border-radius:8px;
            font-weight:700;
            text-decoration:none;
            display:inline-block;
        }

        .btn-primary{
            background:#1d63ed;
            color:white;
        }

        .btn-secondary{
            border:1px solid #ccc;
            color:#1e3a8a;
        }

        .hero-image img{
            width:100%;
            border-radius:20px;
        }

        @media(max-width:900px){
            .hero-container{
                grid-template-columns:1fr;
                text-align:center;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR (SERVICE STYLE + LOGO) -->
<nav class="flex justify-between items-center px-20 py-6 bg-white sticky top-0 z-50 shadow-sm">

    <!-- Logo -->
    <div class="flex items-center text-blue-900 font-bold text-2xl">
        <img src="heart.jpeg" alt="Medi Plus Logo" class="w-10 h-10 mr-2 rounded-full">
        Medi Plus
    </div>

    <!-- Navigation -->
    <div class="space-x-8 font-semibold text-gray-700">
        <a href="home.php" class="text-blue-600 font-bold">Home</a>
        <a href="about.php" class="hover:text-blue-600">About Us</a>
        <a href="services.php" class="hover:text-blue-600">Services</a>
        <a href="connect.php" class="hover:text-blue-600">Connect Us</a>
    </div>

    <!-- Panels -->
    <div class="space-x-4 font-semibold text-blue-700">
        <a href="../patient/login.php" class="hover:text-blue-900">Patient Panel</a>
        <a href="../doctor/login.php" class="hover:text-blue-900">Doctor Panel</a>
        <a href="../admin/login.php" class="hover:text-blue-900">Admin Panel</a>
    </div>

</nav>

<!-- HERO SECTION -->
<main class="hero-container">

    <div>
        <h1 class="main-title">
            Quality Healthcare At<br>
            Your Fingertips
        </h1>

        <p class="description">
            Book appointments with experienced doctors and attend virtual consultations from anywhere.
        </p>

        <div>
            <a href="../patient/login.php" class="btn btn-primary">Book Appointment</a>
            <a href="../patient/register.php" class="btn btn-secondary">Patient Register</a>
        </div>
    </div>

    <div class="hero-image">
        <img src="doctor.png" alt="Healthcare">
    </div>

</main>

<!-- OUR DOCTORS (dynamic from database) -->
<section class="px-20 py-16 bg-blue-50">
    <h2 class="text-4xl font-bold text-blue-900 text-center mb-10">Our Doctors</h2>

    <?php if (count($doctors) > 0): ?>
    <div class="grid md:grid-cols-4 gap-8">
        <?php foreach ($doctors as $doc): ?>
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden text-center p-6">
            <img src="../doctor/<?= htmlspecialchars($doc['profile_image']) ?>"
                 onerror="this.src='doctor.png'"
                 class="w-24 h-24 rounded-full object-cover mx-auto mb-4">
            <h3 class="font-bold text-lg text-blue-900"><?= htmlspecialchars($doc['full_name']) ?></h3>
            <p class="text-gray-500 text-sm"><?= htmlspecialchars($doc['specialty']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="text-center text-gray-500">No doctors available yet. Please check back soon.</p>
    <?php endif; ?>
</section>

</body>
</html>
