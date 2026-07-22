<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructorName = $_SESSION['user_name'];

// ✅ Get course ID from URL
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if ($courseId === 0) {
    header("Location: my-courses.php");
    exit;
}

// ✅ Verify this course belongs to the logged-in instructor
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ? AND instructor = ?");
mysqli_stmt_bind_param($stmt, "is", $courseId, $instructorName);
mysqli_stmt_execute($stmt);
$courseResult = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($courseResult);
mysqli_stmt_close($stmt);

if (!$course) {
    header("Location: my-courses.php");
    exit;
}

$errors  = [];
$success = false;

// ✅ Handle DELETE lesson
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM lessons WHERE id = ? AND course_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $deleteId, $courseId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: manage-lessons.php?course_id=$courseId&msg=deleted");
    exit;
}

// ✅ Handle ADD lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lesson'])) {
    $title       = trim(htmlspecialchars($_POST['title']       ?? ''));
    $videoUrl    = trim(htmlspecialchars($_POST['video_url']   ?? ''));
    $notes       = trim(htmlspecialchars($_POST['notes']       ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));

    if (empty($title)) $errors[] = "Lesson title is required.";

    // ✅ Basic YouTube URL validation
    if (!empty($videoUrl) && !preg_match('/(youtube\.com|youtu\.be)/', $videoUrl)) {
        $errors[] = "Please enter a valid YouTube URL.";
    }

    if (empty($errors)) {
        // Get next order number
        $orderResult = mysqli_query($conn, "SELECT COUNT(*) as c FROM lessons WHERE course_id = $courseId");
        $nextOrder   = mysqli_fetch_assoc($orderResult)['c'] + 1;

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO lessons (course_id, title, video_url, notes, description, lesson_order) VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "issssi", $courseId, $title, $videoUrl, $notes, $description, $nextOrder);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

$successMsg = isset($_GET['msg']) ? $_GET['msg'] : '';

// Fetch all lessons for this course
$lessons = mysqli_query($conn, "SELECT * FROM lessons WHERE course_id = $courseId ORDER BY lesson_order ASC");
$totalLessons = mysqli_num_rows($lessons);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons | Instructor | Study Adda</title>
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
                <div class="sidebar-avatar" style="background:#10b981;">
                    <?php echo strtoupper(substr($instructorName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo htmlspecialchars($instructorName); ?></p>
                <span class="sidebar-role" style="background:rgba(16,185,129,0.2);color:#10b981;">Instructor</span>
            </div>
            <nav class="sidebar-nav">

                <a href="dashboard.php" class="sidebar-link">
                    📊 Dashboard
                </a>

                <a href="my-courses.php"
                    class="sidebar-link active">
                    📚 My Courses
                </a>

                <a href="add-course.php"
                    class="sidebar-link">
                    ➕ Add Course
                </a>

                <a href="my-students.php"
                    class="sidebar-link">
                    👨‍🎓 My Students
                </a>

                <a href="profile.php"
                    class="sidebar-link">
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

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">📖 Manage Lessons</h4>
                    <p class="topbar-sub">Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong> — <?php echo $totalLessons; ?> lessons</p>
                </div>
                <a href="my-courses.php" class="btn btn-outline-admin">← Back to My Courses</a>
            </div>

            <?php if ($successMsg === 'deleted'): ?>
                <div class="auth-alert-success mb-4">✅ <strong>Lesson deleted successfully!</strong></div>
            <?php endif; ?>

            <div class="row g-4">

                <!-- ADD LESSON FORM -->
                <div class="col-12 col-lg-5">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">➕ Add New Lesson</h5>
                        </div>

                        <?php if ($success): ?>
                            <div class="auth-alert-success mb-3">✅ <strong>Lesson added successfully!</strong></div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert-error mb-3">
                                ⚠️
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <input type="hidden" name="add_lesson" value="1">

                            <div class="mb-3">
                                <label class="form-label auth-label">Lesson Title <span class="required">*</span></label>
                                <input type="text" name="title" class="form-control auth-input"
                                    placeholder="e.g. Introduction to Fractions" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label auth-label">YouTube Video URL</label>
                                <input type="url" name="video_url" class="form-control auth-input"
                                    placeholder="https://youtube.com/watch?v=...">
                                <small class="text-muted">Paste the full YouTube video link</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label auth-label">Lesson Description</label>
                                <textarea name="description" class="form-control auth-input" rows="2"
                                    placeholder="Brief summary of this lesson..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label auth-label">Notes (Text)</label>
                                <textarea name="notes" class="form-control auth-input" rows="4"
                                    placeholder="Add lesson notes here — students will read this alongside the video"></textarea>
                            </div>

                            <button type="submit" class="btn enroll-btn w-100">➕ Add Lesson</button>
                        </form>
                    </div>
                </div>

                <!-- LESSONS LIST -->
                <div class="col-12 col-lg-7">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">📋 Course Lessons</h5>
                        </div>

                        <?php if ($totalLessons === 0): ?>
                            <div class="text-center py-4">
                                <div style="font-size:2.5rem;">📭</div>
                                <h6 class="mt-2">No lessons yet</h6>
                                <p class="text-muted" style="font-size:0.88rem;">Add your first lesson using the form</p>
                            </div>
                        <?php else: ?>
                            <?php $n = 1;
                            while ($lesson = mysqli_fetch_assoc($lessons)): ?>
                                <div class="lesson-item">
                                    <div class="lesson-item-number"><?php echo $n++; ?></div>
                                    <div class="lesson-item-info">
                                        <h6 class="lesson-item-title"><?php echo htmlspecialchars($lesson['title']); ?></h6>
                                        <?php if ($lesson['video_url']): ?>
                                            <span class="lesson-item-tag">🎥 Has video</span>
                                        <?php endif; ?>
                                        <?php if ($lesson['notes']): ?>
                                            <span class="lesson-item-tag">📝 Has notes</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="lesson-item-actions">
                                        <a href="edit-lesson.php?id=<?php echo $lesson['id']; ?>&course_id=<?php echo $courseId; ?>"
                                            class="btn-action btn-edit">✏️</a>
                                        <a href="manage-lessons.php?course_id=<?php echo $courseId; ?>&delete=<?php echo $lesson['id']; ?>"
                                            class="btn-action btn-delete"
                                            onclick="return confirm('Delete this lesson?')">🗑️</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>