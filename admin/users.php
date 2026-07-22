<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ Handle DELETE user
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    // Prevent admin from deleting themselves
    if ($deleteId !== $_SESSION['user_id']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $deleteId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: users.php?msg=deleted");
    exit;
}

$successMsg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Fetch all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
$totalUsers = mysqli_num_rows($users);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin | Study Adda</title>
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
                <a href="users.php" class="sidebar-link active">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">👥 Manage Users</h4>
                    <p class="topbar-sub">Total: <strong><?php echo $totalUsers; ?></strong> users</p>
                </div>
            </div>

            <?php if ($successMsg === 'deleted'): ?>
                <div class="auth-alert-success mb-4">
                    ✅ <strong>User deleted successfully!</strong>
                </div>
            <?php endif; ?>

            <!-- Filter counts -->
            <?php
            $students    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='student'"))['t'];
            $instructors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='instructor'"))['t'];
            $admins      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='admin'"))['t'];
            ?>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="stat-widget stat-teal">
                        <div class="stat-widget-icon">👨‍🎓</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $students; ?></h3>
                            <p class="stat-widget-label">Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-widget stat-green">
                        <div class="stat-widget-icon">👨‍🏫</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $instructors; ?></h3>
                            <p class="stat-widget-label">Instructors</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-widget stat-rose">
                        <div class="stat-widget-icon">🛡️</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $admins; ?></h3>
                            <p class="stat-widget-label">Admins</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <?php if ($totalUsers === 0): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">👥</div>
                        <h5 class="mt-2">No users yet</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                while ($user = mysqli_fetch_assoc($users)): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <div class="table-user">
                                                <div class="table-avatar">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <p class="table-name"><?php echo htmlspecialchars($user['name']); ?></p>
                                                    <p class="table-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="table-date">
                                            <?php echo date("d M Y", strtotime($user['created_at'])); ?>
                                        </td>
                                        <td>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users.php?delete=<?php echo $user['id']; ?>"
                                                    class="btn-action btn-delete"
                                                    onclick="return confirm('Delete this user permanently?')">
                                                    🗑️ Delete
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size:0.8rem;">You</span>
                                            <?php endif; ?>
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