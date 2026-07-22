<?php
include "../includes/dbconfig.php";

// Admin guard — only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$adminName = $_SESSION['user_name'];

// Fetch real stats from database
// COUNT(*) counts total rows in each table
$totalStudents    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='student'"))['total'];
$totalInstructors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='instructor'"))['total'];
$totalCourses     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM courses"))['total'];
$totalMessages    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages"))['total'];

// Fetch recent users (last 5)
$recentUsers = mysqli_query($conn, "SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");

// Fetch recent courses (last 5)
$recentCourses = mysqli_query($conn, "SELECT title, instructor, class, price FROM courses ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="../index.php">
                    <img src="../images/logo-dark.png" alt="Study Adda" height="40">
                </a>
            </div>

            <div class="sidebar-user">
                <div class="sidebar-avatar" style="background:#ff6b6c;">
                    <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $adminName; ?></p>
                <span class="sidebar-role" style="background:rgba(255,107,108,0.2);color:#ff6b6c;">Admin</span>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="../logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <!-- Top Bar -->
            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">Admin Dashboard 🛠️</h4>
                    <p class="topbar-sub">Welcome back, <?php echo $adminName; ?>! Here's your platform overview.</p>
                </div>
                <div class="topbar-date"><?php echo date("D, d M Y"); ?></div>
            </div>

            <!-- STATS -->
            <div class="row g-4 mb-4">
                <?php
                $stats = [
                    ["icon" => "👨‍🎓", "label" => "Total Students",    "value" => $totalStudents,    "color" => "stat-teal"],
                    ["icon" => "👨‍🏫", "label" => "Total Instructors", "value" => $totalInstructors, "color" => "stat-green"],
                    ["icon" => "📚",  "label" => "Total Courses",     "value" => $totalCourses,     "color" => "stat-amber"],
                    ["icon" => "📩",  "label" => "Messages",          "value" => $totalMessages,    "color" => "stat-rose"],
                ];
                foreach ($stats as $stat):
                ?>
                    <div class="col-6 col-lg-3">
                        <div class="stat-widget <?php echo $stat['color']; ?>">
                            <div class="stat-widget-icon"><?php echo $stat['icon']; ?></div>
                            <div>
                                <h3 class="stat-widget-value"><?php echo $stat['value']; ?></h3>
                                <p class="stat-widget-label"><?php echo $stat['label']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h5 class="dashboard-card-title mb-3">⚡ Quick Actions</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="add-course.php" class="btn enroll-btn btn-sm">➕ Add New Course</a>
                            <a href="users.php" class="btn btn-outline-admin btn-sm">👥 View All Users</a>
                            <a href="messages.php" class="btn btn-outline-admin btn-sm">📩 View Messages</a>
                            <a href="../courses.php" class="btn btn-outline-admin btn-sm" target="_blank">🌐 View Site</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <!-- RECENT USERS -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">👥 Recent Users</h5>
                            <a href="users.php" class="btn-card-action">View All</a>
                        </div>

                        <?php if (mysqli_num_rows($recentUsers) > 0): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($user = mysqli_fetch_assoc($recentUsers)): ?>
                                            <tr>
                                                <td>
                                                    <div class="table-user">
                                                        <div class="table-avatar">
                                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <p class="table-name"><?php echo $user['name']; ?></p>
                                                            <p class="table-email"><?php echo $user['email']; ?></p>
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
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">No users yet</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RECENT COURSES -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">📚 Recent Courses</h5>
                            <a href="courses.php" class="btn-card-action">View All</a>
                        </div>

                        <?php if (mysqli_num_rows($recentCourses) > 0): ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Class</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($course = mysqli_fetch_assoc($recentCourses)): ?>
                                            <tr>
                                                <td>
                                                    <p class="table-name"><?php echo $course['title']; ?></p>
                                                    <p class="table-email"><?php echo $course['instructor']; ?></p>
                                                </td>
                                                <td>
                                                    <span class="class-badge">Class <?php echo $course['class']; ?></span>
                                                </td>
                                                <td class="table-price">₹<?php echo number_format($course['price']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No courses yet</p>
                                <a href="add-course.php" class="btn enroll-btn btn-sm">Add First Course</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>