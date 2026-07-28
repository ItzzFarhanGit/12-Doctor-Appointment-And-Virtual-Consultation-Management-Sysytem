<?php
// =============================================
// SAJEEFA - Contact Us (home/connect.php)
// =============================================
require_once '../includes/db.php';

$success = '';
$error   = '';

// Create messages table automatically if it doesn't exist yet
$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        if ($stmt->execute()) {
            $success = 'Thank you! Your message has been sent.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - Medi Plus</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>
.btn-hover:hover{
    transform: scale(1.05);
    transition: 0.3s;
}
</style>

</head>

<body class="bg-gray-50">

<!-- NAVIGATION -->
<nav class="flex justify-between items-center px-20 py-6 bg-white sticky top-0 z-50 shadow-sm">

    <div class="flex items-center text-blue-900 font-bold text-2xl">
        <img src="heart.jpeg" alt="Medi Plus Logo" class="w-10 h-10 mr-2 rounded-full">
        Medi Plus
    </div>

    <div class="space-x-8 font-semibold text-gray-700">
        <a href="home.php" class="hover:text-blue-600">Home</a>
        <a href="about.php" class="hover:text-blue-600">About Us</a>
        <a href="services.php" class="hover:text-blue-600">Services</a>
        <a href="connect.php" class="text-blue-600 font-bold">Contact Us</a>
    </div>

    <div class="space-x-4 font-semibold text-blue-700">
        <a href="../patient/login.php">Patient Panel</a>
        <a href="../doctor/login.php">Doctor Panel</a>
        <a href="../admin/login.php">Admin Panel</a>
    </div>

</nav>

<!-- HEADER -->
<section class="bg-blue-50 py-20 text-center">
    <h1 class="text-5xl font-bold text-blue-900 mb-4">Contact Us</h1>
    <p class="text-gray-600 text-lg">We are here to help you anytime</p>
</section>

<!-- CONTACT SECTION -->
<section class="px-20 py-16 grid md:grid-cols-2 gap-10">

    <!-- CONTACT INFO CARD -->
    <div class="bg-white rounded-2xl shadow-lg p-8 border border-blue-100 hover:shadow-2xl transition duration-300">

        <h2 class="text-2xl font-bold text-blue-700 mb-6 flex items-center">
            📞 Get in Touch
        </h2>

        <div class="space-y-4 text-gray-600">

            <div class="flex items-center gap-3">
                <span class="text-blue-600 text-xl">📍</span>
                <p>Medi Plus Healthcare System</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-blue-600 text-xl">📞</span>
                <p>+94 77 123 4567</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-blue-600 text-xl">📧</span>
                <p>support@mediplus.com</p>
            </div>

        </div>

    </div>

    <!-- CONTACT FORM CARD (saves to database) -->
    <div class="bg-white rounded-2xl shadow-lg p-8 border border-blue-100 hover:shadow-2xl transition duration-300">

        <h2 class="text-2xl font-bold text-blue-700 mb-6">✉️ Send a Message</h2>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-300 text-green-700 text-sm rounded-lg p-3 mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-lg p-3 mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <input type="text" name="name" placeholder="Your Name" required
                value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <input type="email" name="email" placeholder="Your Email" required
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <textarea name="message" placeholder="Your Message" rows="4" required
                class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>

            <button type="submit"
                class="btn-hover w-full bg-blue-600 text-white font-bold py-3 rounded-lg">
                Send Message
            </button>
        </form>
    </div>

</section>

</body>
</html>
