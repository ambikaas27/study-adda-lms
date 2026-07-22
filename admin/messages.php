<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle DELETE message
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM contact_messages WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $deleteId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: messages.php?msg=deleted");
    exit;
}

$successMsg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Fetch all messages
$messages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
$totalMessages = mysqli_num_rows($messages);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Admin | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/MyProject/css/style.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="/MyProject/index.php">
                    <img src="/MyProject/images/logo-dark.png" alt="Study Adda" height="40">
                </a>
            </div>
            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background:#ff6b6c;">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $_SESSION['user_name']; ?></p>
                <span class="sidebar-role" style="background:rgba(255,107,108,0.2);color:#ff6b6c;">Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link active">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">📩 Contact Messages</h4>
                    <p class="topbar-sub">Total: <strong><?php echo $totalMessages; ?></strong> messages</p>
                </div>
            </div>

            <?php if ($successMsg === 'deleted'): ?>
                <div class="auth-alert-success mb-4">
                    ✅ <strong>Message deleted successfully!</strong>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <?php if ($totalMessages === 0): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">📭</div>
                        <h5 class="mt-2">No messages yet</h5>
                        <p class="text-muted">Messages from your contact form will appear here</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                while ($msg = mysqli_fetch_assoc($messages)): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <p class="table-name"><?php echo htmlspecialchars($msg['name']); ?></p>
                                            <p class="table-email"><?php echo htmlspecialchars($msg['email']); ?></p>
                                        </td>
                                        <td><?php echo htmlspecialchars($msg['subject'] ?: '—'); ?></td>
                                        <td>
                                            <p class="table-email" style="max-width:200px;">
                                                <?php echo htmlspecialchars(substr($msg['message'], 0, 80)) . '...'; ?>
                                            </p>
                                        </td>
                                        <td class="table-date">
                                            <?php echo date("d M Y", strtotime($msg['created_at'])); ?>
                                        </td>
                                        <td>
                                            <a href="messages.php?delete=<?php echo $msg['id']; ?>"
                                                class="btn-action btn-delete"
                                                onclick="return confirm('Delete this message?')">
                                                🗑️ Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>