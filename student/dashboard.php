<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$studentName = $_SESSION['user_name'];
$studentId   = $_SESSION['user_id'];

// Fetch student info
$stmt = mysqli_prepare($conn, "SELECT name, email, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ✅ Real stats from database
$enrolledCount = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as t FROM enrollments WHERE student_id = $studentId"
))['t'];

$completedCount = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as t FROM progress WHERE student_id = $studentId AND status = 'completed'"
))['t'];

$inProgressCount = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT COUNT(*) as t FROM progress WHERE student_id = $studentId AND status = 'in_progress'"
))['t'];

// Certificates = completed courses (for now, 1:1)
$certificatesCount = $completedCount;

// ✅ Fetch recent enrolled courses (last 3) for the dashboard preview
$stmt = mysqli_prepare(
    $conn,
    "SELECT c.id, c.title, c.instructor, c.image, p.status
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     JOIN progress p ON p.course_id = c.id AND p.student_id = e.student_id
     WHERE e.student_id = ?
     ORDER BY e.enrolled_at DESC
     LIMIT 3"
);
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$recentCourses = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/MyProject/css/style.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="/MyProject/index.php">
                    <img src="/MyProject/images/logo.png" alt="Study Adda" height="40">
                </a>
            </div>

            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($studentName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo htmlspecialchars($studentName); ?></p>
                <span class="sidebar-role">Student</span>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 My Courses</a>
                <a href="progress.php" class="sidebar-link">📈 My Progress</a>
                <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                <a href="/MyProject/courses.php" class="sidebar-link">🔍 Browse Courses</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <!-- Top Bar -->
            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">Welcome back, <?php echo htmlspecialchars($studentName); ?>! 👋</h4>
                    <p class="topbar-sub">Here's what's happening with your learning today</p>
                </div>
                <div class="topbar-date">
                    <?php echo date("D, d M Y"); ?>
                </div>
            </div>

            <!-- ✅ STATS CARDS — now with real data -->
            <div class="row g-4 mb-4">
                <?php
                $stats = [
                    ["icon" => "📚", "label" => "Enrolled Courses", "value" => $enrolledCount,    "color" => "stat-teal"],
                    ["icon" => "✅", "label" => "Completed",         "value" => $completedCount,   "color" => "stat-green"],
                    ["icon" => "⏳", "label" => "In Progress",       "value" => $inProgressCount,  "color" => "stat-amber"],
                    ["icon" => "🏆", "label" => "Certificates",      "value" => $certificatesCount, "color" => "stat-rose"],
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

            <div class="row g-4">
                <!-- MY COURSES -->
                <div class="col-12 col-lg-8">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">📚 My Enrolled Courses</h5>
                            <a href="courses.php" class="btn-card-action">View All</a>
                        </div>

                        <?php if ($enrolledCount === 0): ?>
                            <div class="empty-dashboard text-center py-4">
                                <div style="font-size:3rem;">📭</div>
                                <h6 class="mt-2">No courses enrolled yet</h6>
                                <p class="text-muted" style="font-size:0.88rem;">Start learning by enrolling in a course</p>
                                <a href="/MyProject/courses.php" class="btn enroll-btn btn-sm mt-2">Browse Courses</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Instructor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $statusLabels = [
                                            'enrolled'    => '📚 Enrolled',
                                            'in_progress' => '⏳ In Progress',
                                            'completed'   => '✅ Completed',
                                        ];
                                        $statusColors = [
                                            'enrolled'    => 'role-student',
                                            'in_progress' => 'role-instructor',
                                            'completed'   => 'role-admin',
                                        ];
                                        while ($course = mysqli_fetch_assoc($recentCourses)):
                                        ?>
                                            <tr>
                                                <td>
                                                    <a href="/MyProject/course-detail.php?id=<?php echo $course['id']; ?>" class="table-name" style="text-decoration:none;">
                                                        <?php echo htmlspecialchars($course['title']); ?>
                                                    </a>
                                                </td>
                                                <td class="table-date"><?php echo htmlspecialchars($course['instructor']); ?></td>
                                                <td>
                                                    <span class="role-badge <?php echo $statusColors[$course['status']]; ?>">
                                                        <?php echo $statusLabels[$course['status']]; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
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
                            <div class="account-avatar">
                                <?php echo strtoupper(substr($studentName, 0, 1)); ?>
                            </div>
                            <div class="account-details">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                                <p><strong>Role:</strong> Student</p>
                                <p><strong>Joined:</strong> <?php echo date("d M Y", strtotime($student['created_at'])); ?></p>
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