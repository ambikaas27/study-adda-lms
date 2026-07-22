<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructorName = $_SESSION['user_name'];
$instructorId   = $_SESSION['user_id'];

// Fetch instructor info
$stmt = mysqli_prepare($conn, "SELECT name, email, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $instructorId);
mysqli_stmt_execute($stmt);
$result     = mysqli_stmt_get_result($stmt);
$instructor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Fetch courses by this instructor
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE instructor = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "s", $instructorName);
mysqli_stmt_execute($stmt);
$coursesResult = mysqli_stmt_get_result($stmt);
$totalCourses  = mysqli_num_rows($coursesResult);
mysqli_stmt_close($stmt);
// Fetch total enrolled students

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total_students
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     WHERE c.instructor = ?"
);

mysqli_stmt_bind_param($stmt, "s", $instructorName);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$totalStudents = $row['total_students'];

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard | Study Adda</title>
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
                <div class="sidebar-avatar" style="background:#10b981;">
                    <?php echo strtoupper(substr($instructorName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $instructorName; ?></p>
                <span class="sidebar-role" style="background:rgba(16,185,129,0.2);color:#10b981;">Instructor</span>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a>
                <a href="my-courses.php" class="sidebar-link">
                    📚 My Courses
                </a>
                <a href="add-course.php" class="sidebar-link">
                    ➕ Add Course
                </a>
                <a href="my-students.php" class="sidebar-link">
                    👨‍🎓 My Students
                </a>
                <a href="profile.php" class="sidebar-link">
                    👤 My Profile
                </a>
                <a href="/MyProject/logout.php"
                    class="sidebar-link logout-link">
                    🚪 Logout
                </a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <!-- Top Bar -->
            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">
                        Welcome, <?php echo $instructorName; ?>! 👋
                    </h4>
                    <p class="topbar-sub">
                        Here's an overview of your teaching activity.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="add-course.php"class="btn enroll-btn">
                        ➕ Add Course
                    </a>

                    <div class="topbar-date">
                        <?php echo date("D, d M Y"); ?>
                    </div>
                </div>
            </div>

            <!-- STATS -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-teal">
                        <div class="stat-widget-icon">📚</div>
                        
                        <div>
                            <h3 class="stat-widget-value"><?php echo $totalCourses; ?></h3>
                            <p class="stat-widget-label">My Courses</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-green">
                        <div class="stat-widget-icon">👨‍🎓</div>
                        <div>
                            <h3 class="stat-widget-value">
                                <?php echo $totalStudents; ?>
                            </h3>
                            <p class="stat-widget-label">Total Students</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-amber">
                        <div class="stat-widget-icon">⭐</div>
                        <div>
                            <h3 class="stat-widget-value">N/A</h3>
                            <p class="stat-widget-label">Avg Rating</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-rose">
                        <div class="stat-widget-icon">💰</div>
                        <div>
                            <h3 class="stat-widget-value">Coming Soon</h3>
                            <p class="stat-widget-label">Earnings</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">

                <!-- MY COURSES -->
                <div class="col-12 col-lg-8">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">📚 My Courses</h5>
                            <a href="my-courses.php" class="btn-card-action">View All</a>
                        </div>

                        <?php if ($totalCourses === 0): ?>
                            <div class="text-center py-4">
                                <div style="font-size:3rem;">📭</div>
                                <h6 class="mt-2">No courses yet</h6>
                                <p class="text-muted" style="font-size:0.88rem;">
                                    Click on "Add Course" to create your first course.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Class</th>
                                            <th>Price</th>
                                            <th>Duration</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                        // Reset result pointer
                                        $stmt2 = mysqli_prepare($conn, "SELECT * FROM courses WHERE instructor = ? ORDER BY created_at DESC LIMIT 5");
                                        mysqli_stmt_bind_param($stmt2, "s", $instructorName);
                                        mysqli_stmt_execute($stmt2);
                                        $recentCourses = mysqli_stmt_get_result($stmt2);
                                        while ($course = mysqli_fetch_assoc($recentCourses)):
                                        ?>
                                            <tr>
                                                <td>
                                                    <p class="table-name"><?php echo htmlspecialchars($course['title']); ?></p>
                                                    <p class="table-email"><?php echo htmlspecialchars(substr($course['description'], 0, 50)); ?>...</p>
                                                </td>
                                                <td><span class="class-badge">Class <?php echo $course['class']; ?></span></td>
                                                <td class="table-price">

                                                    <?php if ($course['price'] == 0): ?>
                                                        FREE
                                                    <?php else: ?>

                                                        ₹<?php echo number_format($course['price']); ?>

                                                    <?php endif; ?>
                                                </td>
                                                <td class="table-date"><?php echo htmlspecialchars($course['duration']); ?></td>
                                            </tr>
                                        <?php endwhile;
                                        mysqli_stmt_close($stmt2); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ACCOUNT INFO -->
                <div class="col-12 col-lg-4">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">👤 Account Info</h5>
                        </div>
                        <div class="account-info">
                            <div class="account-avatar" style="background:#10b981;">
                                <?php echo strtoupper(substr($instructorName, 0, 1)); ?>
                            </div>
                            <div class="account-details">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($instructor['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($instructor['email']); ?></p>
                                <p><strong>Role:</strong> Instructor</p>
                                <p><strong>Joined:</strong> <?php echo date("d M Y", strtotime($instructor['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>