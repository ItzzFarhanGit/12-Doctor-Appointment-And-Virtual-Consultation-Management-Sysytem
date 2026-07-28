<?php
// =============================================
// SAJEEFA - Contact Messages (admin/messages.php)
// SAJEEFA FIX: home/connect.php saves every "Contact Us" form submission into a
// contact_messages table, but there was no admin page anywhere to actually read
// them - they just piled up invisibly. This adds that missing page.
// =============================================
require_once '../includes/admin_auth.php';
require_once '../includes/db.php';

$message = '';

// Make sure the table exists even if no one has submitted the contact form yet
$conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// DELETE message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "🗑️ Message deleted.";
}

$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Messages</title>
<style>
body{ margin:0; font-family:Arial,sans-serif; background:#f4f6f8; }
.container{ display:flex; min-height:100vh; }
.main{ flex:1; padding:25px; }
.table-box{ background:white; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); overflow-x:auto; }
table{ width:100%; border-collapse:collapse; }
table th, table td{ padding:12px; border-bottom:1px solid #ddd; text-align:left; font-size:13px; vertical-align:top; }
table th{ background:#0b2a4a; color:white; }
.action-link{ font-weight:bold; text-decoration:none; font-size:12px; color:#c00; }
.msg{ padding:12px; border-radius:8px; margin-bottom:20px; max-width:900px; background:#e8f0fe; color:#1a73e8; }
</style>
</head>
<body>
<div class="container">
<?php adminSidebar(); ?>
<div class="main">
    <h1>Contact Messages</h1>

    <?php if ($message): ?>
    <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <div class="table-box">
        <h2>Messages Received (<?= count($messages) ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($messages)): ?>
                <tr><td colspan="5" style="text-align:center;color:#666;">No messages yet</td></tr>
            <?php else: foreach ($messages as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['created_at']) ?></td>
                    <td><?= htmlspecialchars($m['name']) ?></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                    <td>
                        <a href="?delete=<?= $m['id'] ?>" class="action-link" onclick="return confirm('Delete this message?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
