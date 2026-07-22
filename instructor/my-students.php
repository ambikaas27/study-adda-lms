<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$instructorName = $_SESSION['user_name'];

// Fetch all students enrolled in instructor's courses
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        u.name,
        u.email,
        c.title AS course_title,
        e.enrolled_at

    FROM enrollments e

    INNER JOIN users u
        ON e.student_id = u.id

    INNER JOIN courses c
        ON e.course_id = c.id

    WHERE c.instructor = ?

    ORDER BY e.enrolled_at DESC"
);

mysqli_stmt_bind_param($stmt, "s", $instructorName);
mysqli_stmt_execute($stmt);

$students = mysqli_stmt_get_result($stmt);
$totalStudents = mysqli_num_rows($students);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students | Instructor | Study Adda</title>

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
                    style="background:rgba(16,185,129,.2); color:#10b981;">

                    Instructor

                </span>

            </div>


            <nav class="sidebar-nav">

                <a href="dashboard.php"
                    class="sidebar-link">
                    📊 Dashboard
                </a>

                <a href="my-courses.php"
                    class="sidebar-link">
                    📚 My Courses
                </a>

                <a href="add-course.php"
                    class="sidebar-link">
                    ➕ Add Course
                </a>

                <a href="my-students.php"
                    class="sidebar-link active">
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
                        👨‍🎓 My Students
                    </h4>

                    <p class="topbar-sub">

                        Total Students :
                        <strong>
                            <?php echo $totalStudents; ?>
                        </strong>

                    </p>

                </div>

            </div>


            <!-- STUDENTS LIST -->

            <?php if ($totalStudents == 0): ?>

                <div class="dashboard-card">

                    <div class="text-center py-5">

                        <div style="font-size:3rem;">
                            👨‍🎓
                        </div>

                        <h5 class="mt-3">
                            No Students Yet
                        </h5>

                        <p class="text-muted">
                            Students enrolled in your courses will appear here.
                        </p>

                    </div>

                </div>

            <?php else: ?>

                <div class="dashboard-card">

                    <div class="dashboard-card-header">

                        <h5 class="dashboard-card-title">
                            Student List
                        </h5>

                    </div>

                    <div class="table-responsive">

                        <table class="admin-table">

                            <thead>

                                <tr>
                                    <th>Student Name</th>
                                    <th>Course Name</th>
                                    <th>Email</th>
                                    <th>Enrolled On</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php while ($student = mysqli_fetch_assoc($students)): ?>

                                    <tr>

                                        <td>

                                            <p class="table-name mb-0">
                                                <?php echo htmlspecialchars($student['name']); ?>
                                            </p>

                                        </td>

                                        <td>

                                            <?php echo htmlspecialchars($student['course_title']); ?>

                                        </td>

                                        <td>

                                            <?php echo htmlspecialchars($student['email']); ?>

                                        </td>

                                        <td>

                                            <?php echo date(
                                                "d M Y",
                                                strtotime($student['enrolled_at'])
                                            ); ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php endif; ?>

        </main>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>