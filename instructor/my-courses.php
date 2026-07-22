<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructorName = $_SESSION['user_name'];

// Delete course functionality
if (isset($_GET['delete_id'])) {

    $courseId = intval($_GET['delete_id']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM courses
        WHERE id = ? AND instructor = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $courseId,
        $instructorName
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: my-courses.php");
    exit;
}

// Fetch courses taught by this instructor (matched by name)
$stmt = mysqli_prepare(
    $conn,
    "SELECT c.*,
        (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id) as lesson_count,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
     FROM courses c
     WHERE c.instructor = ?
     ORDER BY c.created_at DESC"
);
mysqli_stmt_bind_param($stmt, "s", $instructorName);
mysqli_stmt_execute($stmt);
$courses = mysqli_stmt_get_result($stmt);
$totalCourses = mysqli_num_rows($courses);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses | Instructor | Study Adda</title>
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

                    <h4 class="topbar-title">
                        📚 My Courses
                    </h4>

                    <p class="topbar-sub">
                        Total :
                        <strong>
                            <?php echo $totalCourses; ?>
                        </strong>
                        course(s)
                    </p>

                </div>


                <a href="add-course.php"
                    class="btn enroll-btn">

                    ➕ Add Course

                </a>

            </div>

            <?php if ($totalCourses === 0): ?>
                <div class="dashboard-card">

                    <div class="text-center py-5">

                        <div style="font-size:3rem;">
                            📭
                        </div>

                        <h5 class="mt-2">
                            No courses yet
                        </h5>

                        <p class="text-muted">

                            Click on "Add Course"
                            to create your first course.

                        </p>

                    </div>

                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="course-card h-100">
                                <div class="course-img-wrap">
                                    <img src="/MyProject/images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                        alt="<?php echo htmlspecialchars($course['title']); ?>"
                                        class="course-img"
                                        onerror="this.style.display='none'">
                                    <span class="course-class-badge">Class <?php echo $course['class']; ?></span>
                                </div>
                                <div class="course-body">

                                    <h5 class="course-title">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </h5>


                                    <p class="course-desc">

                                        <?php

                                        echo htmlspecialchars(
                                            substr(
                                                $course['description'],
                                                0,
                                                80
                                            )
                                        );

                                        ?>...

                                    </p>


                                    <div class="course-meta">

                                        <span class="meta-item">

                                            📖
                                            <?php echo $course['lesson_count']; ?>
                                            Lessons

                                        </span>


                                        <span class="meta-item">

                                            👨‍🎓
                                            <?php echo $course['student_count']; ?>
                                            Students

                                        </span>

                                    </div>


                                    <div class="course-meta mt-2">

                                        <span class="meta-item">

                                            ⏳
                                            <?php echo htmlspecialchars($course['duration']); ?>

                                        </span>


                                        <span class="meta-item">

                                            💰

                                            <?php

                                            if ($course['price'] == 0) {

                                                echo "FREE";
                                            } else {

                                                echo "₹" . number_format($course['price']);
                                            }

                                            ?>

                                        </span>

                                    </div>

                                </div>
                                <div class="course-footer">

                                    <div class="d-grid gap-2">

                                        <a href="manage-lessons.php?course_id=<?php echo $course['id']; ?>"
                                            class="btn enroll-btn btn-sm">

                                            📖 Manage Lessons

                                        </a>


                                        <a href="edit-course.php?course_id=<?php echo $course['id']; ?>"
                                            class="btn btn-outline-success btn-sm">

                                            ✏ Edit Course

                                        </a>


                                        <a href="my-students.php?course_id=<?php echo $course['id']; ?>"
                                            class="btn btn-outline-primary btn-sm">

                                            👨‍🎓 View Students

                                        </a>


                                        <a href="my-courses.php?delete_id=<?php echo $course['id']; ?>"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this course?');">

                                            🗑 Delete Course

                                        </a>

                                    </div>

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