<?php
include "../includes/dbconfig.php";

// Instructor Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$success = false;

$instructorName = $_SESSION['user_name'];

// Validate IDs
if (!isset($_GET['id']) || !isset($_GET['course_id'])) {
    header("Location: my-courses.php");
    exit;
}

$lessonId = intval($_GET['id']);
$courseId = intval($_GET['course_id']);


// Verify Lesson
$stmt = mysqli_prepare(
    $conn,
    "SELECT l.*, c.instructor
     FROM lessons l
     INNER JOIN courses c ON l.course_id = c.id
     WHERE l.id = ? AND l.course_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $lessonId,
    $courseId
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: my-courses.php");
    exit;
}

$lesson = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Security Check
if ($lesson['instructor'] !== $instructorName) {
    header("Location: my-courses.php");
    exit;
}

// Existing Values
$title        = $lesson['title'];
$video_url    = $lesson['video_url'];
$notes        = $lesson['notes'];
$description  = $lesson['description'];
$lesson_order = $lesson['lesson_order'];

// Update Lesson

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $video_url    = trim($_POST['video_url'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $lesson_order = intval($_POST['lesson_order'] ?? 1);

    // Validation
    if (empty($title)) {
        $errors[] = "Lesson title is required.";
    }

    if ($lesson_order < 1) {
        $errors[] = "Lesson order must be greater than zero.";
    }

    // Update Database
    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE lessons
             SET title = ?,
                 video_url = ?,
                 notes = ?,
                 description = ?,
                 lesson_order = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssii",
            $title,
            $video_url,
            $notes,
            $description,
            $lesson_order,
            $lessonId
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Edit Lesson | Instructor | Study Adda </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/MyProject/css/style.css">
</head>

<body class="dashboard-body">
    <div class="dashboard-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="/MyProject/index.php">
                    <img src="/MyProject/images/logo-dark.png"
                        alt="Study Adda"
                        height="40">

                </a>

            </div>


            <div class="sidebar-user">

                <div class="sidebar-avatar"
                    style="background:#10b981;">

                    <?php echo strtoupper(substr($instructorName, 0, 1)); ?>

                </div>


                <p class="sidebar-name">

                    <?php echo htmlspecialchars($instructorName); ?>

                </p>


                <span class="sidebar-role"
                    style="background:rgba(16,185,129,.2);color:#10b981;">

                    Instructor

                </span>

            </div>



            <nav class="sidebar-nav">

                <a href="dashboard.php"
                    class="sidebar-link">

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


            <!-- TOPBAR -->

            <div class="dashboard-topbar">

                <div>

                    <h4 class="topbar-title">

                        ✏️ Edit Lesson

                    </h4>


                    <p class="topbar-sub">

                        Update your lesson details.

                    </p>

                </div>


                <a href="manage-lessons.php?course_id=<?php echo $courseId; ?>"
                    class="btn btn-outline-success">

                    ← Back to Lessons

                </a>

            </div>



            <div class="row justify-content-center">

                <div class="col-12 col-lg-8">

                    <div class="dashboard-card">


                        <!-- SUCCESS MESSAGE -->

                        <?php if ($success): ?>

                            <div class="auth-alert-success">

                                ✅ <strong>Lesson updated successfully!</strong>

                            </div>

                        <?php endif; ?>



                        <!-- ERROR MESSAGE -->

                        <?php if (!empty($errors)): ?>

                            <div class="auth-alert-error">

                                <ul class="error-list">

                                    <?php foreach ($errors as $error): ?>

                                        <li><?php echo $error; ?></li>

                                    <?php endforeach; ?>

                                </ul>

                            </div>

                        <?php endif; ?>
                        <form method="POST">

                            <div class="row g-3">


                                <!-- LESSON TITLE -->

                                <div class="col-12">

                                    <label class="form-label auth-label">

                                        Lesson Title
                                        <span class="required">*</span>

                                    </label>

                                    <input type="text"
                                        name="title"
                                        class="form-control auth-input"
                                        value="<?php echo htmlspecialchars($title); ?>"
                                        required>

                                </div>



                                <!-- VIDEO URL -->

                                <div class="col-12">

                                    <label class="form-label auth-label">

                                        Video URL

                                    </label>

                                    <input type="text"
                                        name="video_url"
                                        class="form-control auth-input"
                                        placeholder="https://youtube.com/..."
                                        value="<?php echo htmlspecialchars($video_url); ?>">

                                </div>



                                <!-- NOTES -->

                                <div class="col-12">

                                    <label class="form-label auth-label">

                                        Notes

                                    </label>

                                    <textarea
                                        name="notes"
                                        rows="4"
                                        class="form-control auth-input"
                                        placeholder="Enter lesson notes here..."><?php echo htmlspecialchars($notes); ?></textarea>

                                </div>



                                <!-- DESCRIPTION -->

                                <div class="col-12">

                                    <label class="form-label auth-label">

                                        Description

                                    </label>

                                    <textarea
                                        name="description"
                                        rows="4"
                                        class="form-control auth-input"
                                        placeholder="Enter lesson description here..."><?php echo htmlspecialchars($description); ?></textarea>

                                </div>



                                <!-- LESSON ORDER -->

                                <div class="col-12">

                                    <label class="form-label auth-label">

                                        Lesson Order

                                    </label>

                                    <input type="number"
                                        name="lesson_order"
                                        min="1"
                                        class="form-control auth-input"
                                        value="<?php echo $lesson_order; ?>"
                                        required>

                                    <small class="text-muted">

                                        Use lesson order to arrange lessons in sequence.

                                    </small>

                                </div>



                                <!-- SUBMIT BUTTON -->

                                <div class="col-12">

                                    <button type="submit"
                                        class="btn enroll-btn w-100">

                                        ✏️ Update Lesson

                                    </button>

                                </div>


                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </main>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>