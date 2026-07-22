<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$studentId = $_SESSION['user_id'];

// ✅ Get course ID from URL
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($courseId === 0) {
    header("Location: courses.php");
    exit;
}

// ✅ Verify student is enrolled in this course
$stmt = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $studentId, $courseId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$isEnrolled = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$isEnrolled) {
    header("Location: /MyProject/course-detail.php?id=$courseId");
    exit;
}

// Fetch course info
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $courseId);
mysqli_stmt_execute($stmt);
$course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Fetch all lessons for this course
$allLessons = mysqli_query($conn, "SELECT * FROM lessons WHERE course_id = $courseId ORDER BY lesson_order ASC");
$lessonList = mysqli_fetch_all($allLessons, MYSQLI_ASSOC);
$totalLessons = count($lessonList);

// ✅ Get current lesson (from URL or default to first lesson)
$lessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
if ($lessonId === 0 && $totalLessons > 0) {
    $lessonId = $lessonList[0]['id'];
}

// Find the current lesson data
$currentLesson = null;
foreach ($lessonList as $l) {
    if ($l['id'] == $lessonId) {
        $currentLesson = $l;
        break;
    }
}

// ✅ Fetch completed lesson IDs for this student in this course
$completedIds = [];
$compResult = mysqli_query(
    $conn,
    "SELECT lc.lesson_id FROM lesson_completions lc
     JOIN lessons l ON lc.lesson_id = l.id
     WHERE lc.student_id = $studentId AND l.course_id = $courseId"
);
while ($row = mysqli_fetch_assoc($compResult)) {
    $completedIds[] = $row['lesson_id'];
}
$completedCount = count($completedIds);

// ✅ Handle "Mark as Complete"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $lid = intval($_POST['lesson_id']);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT IGNORE INTO lesson_completions (student_id, lesson_id) VALUES (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ii", $studentId, $lid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // ✅ LESSON: Auto-update course progress status based on lesson completion
    $newCompletedCount = count($completedIds) + (in_array($lid, $completedIds) ? 0 : 1);
    $newStatus = 'in_progress';
    if ($newCompletedCount >= $totalLessons && $totalLessons > 0) {
        $newStatus = 'completed';
    }
    $stmt = mysqli_prepare($conn, "UPDATE progress SET status = ? WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $studentId, $courseId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Lesson marked as complete! 🎉'];
    header("Location: lesson.php?course_id=$courseId&lesson_id=$lid");
    exit;
}

// ✅ Extract YouTube video ID for embedding
function getYoutubeEmbedUrl($url)
{
    if (empty($url)) return '';
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
    return isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : '';
}

$progressPct = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
$doneMsg = isset($_GET['done']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $currentLesson ? htmlspecialchars($currentLesson['title']) : 'Lessons'; ?> | Study Adda</title>
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
                <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                <p class="sidebar-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <span class="sidebar-role">Student</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 My Courses</a>
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
                    <h4 class="topbar-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                    <p class="topbar-sub"><?php echo $completedCount; ?> / <?php echo $totalLessons; ?> lessons completed — <?php echo $progressPct; ?>%</p>
                </div>
                <a href="courses.php" class="btn btn-outline-admin">← Back to My Courses</a>
            </div>

            <!-- Progress bar -->
            <div class="progress mb-4" style="height:10px; border-radius:6px;">
                <div class="progress-bar" style="width:<?php echo $progressPct; ?>%; background:#004e64;"></div>
            </div>

            <?php if ($doneMsg): ?>
                <div class="auth-alert-success mb-4">🎉 <strong>Lesson marked as complete!</strong></div>
            <?php endif; ?>

            <?php if ($totalLessons === 0): ?>
                <div class="dashboard-card">
                    <div class="text-center py-5">
                        <div style="font-size:3rem;">📭</div>
                        <h5 class="mt-2">No lessons available yet</h5>
                        <p class="text-muted">Your instructor hasn't added lessons for this course yet</p>
                    </div>
                </div>
            <?php else: ?>

                <div class="row g-4">

                    <!-- LESSON PLAYER -->
                    <div class="col-12 col-lg-8">
                        <div class="dashboard-card">
                            <?php if ($currentLesson): ?>
                                <h5 class="dashboard-card-title mb-3"><?php echo htmlspecialchars($currentLesson['title']); ?></h5>

                                <?php $embedUrl = getYoutubeEmbedUrl($currentLesson['video_url']); ?>
                                <?php if ($embedUrl): ?>
                                    <div class="lesson-video-wrap mb-3">
                                        <iframe src="<?php echo $embedUrl; ?>" allowfullscreen></iframe>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4" style="background:#f3efef; border-radius:8px;">
                                        <p class="text-muted mb-0">📹 No video available for this lesson</p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($currentLesson['description']): ?>
                                    <p class="text-muted mt-3"><?php echo htmlspecialchars($currentLesson['description']); ?></p>
                                <?php endif; ?>

                                <?php if ($currentLesson['notes']): ?>
                                    <div class="mt-3" style="background:#f3efef; border-radius:8px; padding:16px;">
                                        <p class="includes-title">📝 Lesson Notes</p>
                                        <p style="white-space:pre-wrap; font-size:0.9rem; color:#2f3e46; margin:0;"><?php echo htmlspecialchars($currentLesson['notes']); ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Mark Complete Button -->
                                <?php if (in_array($currentLesson['id'], $completedIds)): ?>
                                    <button class="btn enroll-btn mt-3" disabled>✅ Completed</button>
                                <?php else: ?>
                                    <form action="" method="POST" class="mt-3">
                                        <input type="hidden" name="lesson_id" value="<?php echo $currentLesson['id']; ?>">
                                        <button type="submit" name="mark_complete" class="btn enroll-btn">✅ Mark as Complete</button>
                                    </form>
                                <?php endif; ?>

                            <?php else: ?>
                                <p class="text-muted">Select a lesson from the list to begin.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- LESSON LIST SIDEBAR -->
                    <div class="col-12 col-lg-4">
                        <div class="dashboard-card">
                            <h6 class="dashboard-card-title mb-2">📋 Course Content</h6>
                            <?php foreach ($lessonList as $index => $l): ?>
                                <a href="lesson.php?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $l['id']; ?>"
                                    class="lesson-sidebar-item <?php echo $l['id'] == $lessonId ? 'active' : ''; ?>">
                                    <span class="lesson-sidebar-check <?php echo in_array($l['id'], $completedIds) ? 'completed' : ''; ?>">
                                        <?php echo in_array($l['id'], $completedIds) ? '✓' : ($index + 1); ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($l['title']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>