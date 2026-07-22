<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$studentId   = $_SESSION['user_id'];
$studentName = $_SESSION['user_name'];

// Handle progress update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $courseId  = intval($_POST['course_id']);
    $newStatus = in_array($_POST['status'], ['enrolled', 'in_progress', 'completed'])
        ? $_POST['status'] : 'enrolled';

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE progress SET status = ? WHERE student_id = ? AND course_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $studentId, $courseId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: progress.php?msg=updated");
    exit;
}

$successMsg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Fetch enrolled courses with progress
$stmt = mysqli_query(
    $conn,
    "SELECT c.id, c.title, c.instructor, c.duration, c.class, c.image, p.status, e.enrolled_at
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     JOIN progress p ON p.course_id = c.id AND p.student_id = e.student_id
     WHERE e.student_id = $studentId
     ORDER BY e.enrolled_at DESC"
);

$totalEnrolled   = mysqli_num_rows($stmt);
$completedCount  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM progress WHERE student_id=$studentId AND status='completed'"))['t'];
$inProgressCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM progress WHERE student_id=$studentId AND status='in_progress'"))['t'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress | Study Adda</title>
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
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($studentName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $studentName; ?></p>
                <span class="sidebar-role">Student</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 My Courses</a>
                <a href="progress.php" class="sidebar-link active">📈 My Progress</a>
                <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                <a href="/MyProject/courses.php" class="sidebar-link">🔍 Browse Courses</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">📈 My Progress</h4>
                    <p class="topbar-sub">Track your learning journey</p>
                </div>
                <div class="topbar-date"><?php echo date("D, d M Y"); ?></div>
            </div>

            <?php if ($successMsg === 'updated'): ?>
                <div class="auth-alert-success mb-4">✅ <strong>Progress updated successfully!</strong></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-teal">
                        <div class="stat-widget-icon">📚</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $totalEnrolled; ?></h3>
                            <p class="stat-widget-label">Enrolled</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-amber">
                        <div class="stat-widget-icon">⏳</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $inProgressCount; ?></h3>
                            <p class="stat-widget-label">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-green">
                        <div class="stat-widget-icon">✅</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $completedCount; ?></h3>
                            <p class="stat-widget-label">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-widget stat-rose">
                        <div class="stat-widget-icon">🏆</div>
                        <div>
                            <h3 class="stat-widget-value"><?php echo $completedCount; ?></h3>
                            <p class="stat-widget-label">Certificates</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Progress List -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">📚 My Enrolled Courses</h5>
                </div>

                <?php if ($totalEnrolled === 0): ?>
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">📭</div>
                        <h5 class="mt-2">No courses enrolled yet</h5>
                        <a href="/MyProject/courses.php" class="btn enroll-btn mt-2">Browse Courses</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php while ($row = mysqli_fetch_assoc($stmt)): ?>
                            <div class="col-12 col-md-6">
                                <div class="progress-card">
                                    <div class="progress-card-top">
                                        <img src="/MyProject/images/courses/<?php echo htmlspecialchars($row['image']); ?>"
                                            alt="<?php echo htmlspecialchars($row['title']); ?>"
                                            class="progress-card-img"
                                            onerror="this.src='/MyProject/images/courses/default.jpg'">
                                        <div class="progress-card-info">
                                            <h6 class="progress-card-title"><?php echo htmlspecialchars($row['title']); ?></h6>
                                            <p class="progress-card-meta">👨‍🏫 <?php echo htmlspecialchars($row['instructor']); ?></p>
                                            <p class="progress-card-meta">📅 Enrolled: <?php echo date("d M Y", strtotime($row['enrolled_at'])); ?></p>
                                            <!-- Status Badge -->
                                            <?php
                                            $statusColors = [
                                                'enrolled'    => 'role-student',
                                                'in_progress' => 'role-instructor',
                                                'completed'   => 'role-admin',
                                            ];
                                            $statusLabels = [
                                                'enrolled'    => '📚 Enrolled',
                                                'in_progress' => '⏳ In Progress',
                                                'completed'   => '✅ Completed',
                                            ];
                                            ?>
                                            <span class="role-badge <?php echo $statusColors[$row['status']]; ?>">
                                                <?php echo $statusLabels[$row['status']]; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <?php
                                    $progressPct = match ($row['status']) {
                                        'enrolled'    => 0,
                                        'in_progress' => 50,
                                        'completed'   => 100,
                                        default       => 0
                                    };
                                    ?>
                                    <div class="progress mt-3" style="height:8px; border-radius:4px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width:<?php echo $progressPct; ?>%; background:#004e64;"
                                            aria-valuenow="<?php echo $progressPct; ?>"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <p class="text-muted mt-1" style="font-size:0.78rem;"><?php echo $progressPct; ?>% complete</p>

                                    <!-- Update Status -->
                                    <form action="" method="POST" class="mt-2">
                                        <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <div class="d-flex gap-2 align-items-center">
                                            <select name="status" class="form-select form-select-sm auth-input" style="font-size:0.82rem;">
                                                <option value="enrolled" <?php echo $row['status'] === 'enrolled'    ? 'selected' : ''; ?>>📚 Enrolled</option>
                                                <option value="in_progress" <?php echo $row['status'] === 'in_progress' ? 'selected' : ''; ?>>⏳ In Progress</option>
                                                <option value="completed" <?php echo $row['status'] === 'completed'   ? 'selected' : ''; ?>>✅ Completed</option>
                                            </select>
                                            <button type="submit" class="btn enroll-btn btn-sm" style="white-space:nowrap;">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>