<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$studentId   = $_SESSION['user_id'];
$studentName = $_SESSION['user_name'];

// ✅ Fetch all enrolled courses with progress status
$stmt = mysqli_prepare(
    $conn,
    "SELECT c.id, c.title, c.description, c.instructor, c.duration, c.class, c.price, c.image,
            p.status, e.enrolled_at
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     JOIN progress p ON p.course_id = c.id AND p.student_id = e.student_id
     WHERE e.student_id = ?
     ORDER BY e.enrolled_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totalCourses = mysqli_num_rows($result);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses | Study Adda</title>
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
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link active">📚 My Courses</a>
                <a href="progress.php" class="sidebar-link">📈 My Progress</a>
                <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                <a href="/MyProject/courses.php" class="sidebar-link">🔍 Browse Courses</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">📚 My Courses</h4>
                    <p class="topbar-sub">Total enrolled: <strong><?php echo $totalCourses; ?></strong> courses</p>
                </div>
                <a href="/MyProject/courses.php" class="btn enroll-btn">🔍 Browse More Courses</a>
            </div>

            <?php if ($totalCourses === 0): ?>
                <!-- Empty state -->
                <div class="dashboard-card">
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">📭</div>
                        <h5 class="mt-2">You haven't enrolled in any courses yet</h5>
                        <p class="text-muted">Browse our course catalog and start learning today</p>
                        <a href="/MyProject/courses.php" class="btn enroll-btn mt-2">Browse Courses</a>
                    </div>
                </div>

            <?php else: ?>
                <div class="row g-4">
                    <?php while ($course = mysqli_fetch_assoc($result)): ?>
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
                        ?>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="course-card h-100">
                                <div class="course-img-wrap">
                                    <img src="/MyProject/images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                        alt="<?php echo htmlspecialchars($course['title']); ?>"
                                        class="course-img"
                                        onerror="this.style.display='none'">
                                    <span class="course-class-badge">Class <?php echo $course['class']; ?></span>
                                </div>
                                <div class="course-body">
                                    <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <p class="course-desc"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="course-meta mb-2">
                                        <span class="meta-item">👨‍🏫 <?php echo htmlspecialchars($course['instructor']); ?></span>
                                        <span class="meta-item">⏱️ <?php echo htmlspecialchars($course['duration']); ?></span>
                                    </div>
                                    <span class="role-badge <?php echo $statusColors[$course['status']]; ?>">
                                        <?php echo $statusLabels[$course['status']]; ?>
                                    </span>
                                </div>
                                <div class="course-footer">
                                    <span class="table-date">
                                        Enrolled <?php echo date("d M Y", strtotime($course['enrolled_at'])); ?>
                                    </span>
                                    <a href="lesson.php?course_id=<?php echo $course['id']; ?>" class="btn enroll-btn btn-sm">
                                        Continue Learning ▶️
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>