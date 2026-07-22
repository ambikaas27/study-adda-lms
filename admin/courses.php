<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle DELETE course
// When admin clicks delete button — remove from DB
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $deleteId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: courses.php?msg=deleted");
    exit;
}

$successMsg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Fetch all courses
$courses = mysqli_query($conn, "SELECT * FROM courses ORDER BY created_at DESC");
$totalCourses = mysqli_num_rows($courses);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | Admin | Study Adda</title>
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
                <a href="courses.php" class="sidebar-link active">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">📚 Manage Courses</h4>
                    <p class="topbar-sub">Total: <strong><?php echo $totalCourses; ?></strong> courses</p>
                </div>
                <a href="add-course.php" class="btn enroll-btn">➕ Add New Course</a>
            </div>

            <!-- Success/Delete message -->
            <?php if ($successMsg === 'deleted'): ?>
                <div class="auth-alert-success mb-4">
                    ✅ <strong>Course deleted successfully!</strong>
                </div>
            <?php endif; ?>

            <div class="dashboard-card">
                <?php if ($totalCourses === 0): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">📭</div>
                        <h5 class="mt-2">No courses yet</h5>
                        <a href="add-course.php" class="btn enroll-btn mt-2">Add First Course</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Class</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                while ($course = mysqli_fetch_assoc($courses)): ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <p class="table-name"><?php echo htmlspecialchars($course['title']); ?></p>
                                            <p class="table-email"><?php echo htmlspecialchars(substr($course['description'], 0, 50)) . '...'; ?></p>
                                        </td>
                                        <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                                        <td><span class="class-badge">Class <?php echo $course['class']; ?></span></td>
                                        <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                        <td class="table-price">₹<?php echo number_format($course['price']); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <!-- Edit link passes course ID -->
                                                <a href="edit-course.php?id=<?php echo $course['id']; ?>"
                                                    class="btn-action btn-edit">✏️ Edit</a>

                                                <!-- Delete with confirmation -->
                                                <!-- onclick confirm() shows a popup before deleting -->
                                                <a href="courses.php?delete=<?php echo $course['id']; ?>"
                                                    class="btn-action btn-delete"
                                                    onclick="return confirm('Are you sure you want to delete this course?')">
                                                    🗑️ Delete
                                                </a>
                                            </div>
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