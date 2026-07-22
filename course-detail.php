<?php
$pageTitle = "Course Detail";
include "includes/dbconfig.php";
include "includes/header.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    header("Location: courses.php");
    exit;
}

// Fetch course from DB
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$course) {
    header("Location: courses.php");
    exit;
}

// Check if student is already enrolled
$isEnrolled = false;
$isLoggedIn = isset($_SESSION['user_id']);
$isStudent  = $isLoggedIn && $_SESSION['role'] === 'student';

if ($isStudent) {
    $stmt = mysqli_prepare($conn, "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $isEnrolled = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
}

// Handle enrollment
$enrollMsg   = '';
$enrollError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    if (!$isLoggedIn) {
        header("Location: /MyProject/login.php");
        exit;
    }
    if (!$isStudent) {
        $enrollError = "Only students can enroll in courses.";
    } elseif ($isEnrolled) {
        $enrollError = "You are already enrolled in this course.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $id);
        if (mysqli_stmt_execute($stmt)) {
            $stmt2 = mysqli_prepare($conn, "INSERT INTO progress (student_id, course_id, status) VALUES (?, ?, 'enrolled')");
            mysqli_stmt_bind_param($stmt2, "ii", $_SESSION['user_id'], $id);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
            $isEnrolled = true;
            $enrollMsg  = "🎉 Successfully enrolled! Go to your dashboard to start learning.";
        } else {
            $enrollError = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Count total enrollments
$enrollCount = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM enrollments WHERE course_id = $id")
)['total'];
?>

<!-- COURSE HERO -->
<section class="course-hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-12 col-lg-7">
                <span class="class-badge mb-3 d-inline-block">Class <?php echo $course['class']; ?></span>
                <h1 class="course-hero-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="course-hero-desc"><?php echo htmlspecialchars($course['description']); ?></p>
                <div class="course-hero-meta">
                    <span>👨‍🏫 <?php echo htmlspecialchars($course['instructor']); ?></span>
                    <span>⏱️ <?php echo htmlspecialchars($course['duration']); ?></span>
                    <span>👨‍🎓 <?php echo $enrollCount; ?> students enrolled</span>
                </div>
            </div>

            <!-- ENROLL CARD -->
            <div class="col-12 col-lg-5">
                <div class="enroll-card">
                    <img src="images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                        alt="<?php echo htmlspecialchars($course['title']); ?>"
                        class="enroll-card-img"
                        onerror="this.style.display='none'">
                    <div class="enroll-card-body">
                        <h2 class="enroll-price">₹<?php echo number_format($course['price']); ?></h2>

                        <?php if ($enrollMsg): ?>
                            <div class="auth-alert-success mb-3">✅ <?php echo $enrollMsg; ?></div>
                        <?php endif; ?>

                        <?php if ($enrollError): ?>
                            <div class="auth-alert-error mb-3">⚠️ <?php echo $enrollError; ?></div>
                        <?php endif; ?>

                        <?php if ($isEnrolled): ?>
                            <a href="/MyProject/student/dashboard.php" class="btn enroll-btn w-100 mb-2">
                                Go to Dashboard 📊
                            </a>
                            <p class="text-center text-muted" style="font-size:0.85rem;">✅ Already enrolled</p>

                        <?php elseif ($isStudent): ?>
                            <form action="" method="POST">
                                <input type="hidden" name="enroll" value="1">
                                <button type="submit" class="btn enroll-btn w-100 mb-2">Enroll Now 🚀</button>
                            </form>

                        <?php elseif (!$isLoggedIn): ?>
                            <a href="/MyProject/login.php" class="btn enroll-btn w-100 mb-2">Login to Enroll</a>
                            <p class="text-center text-muted" style="font-size:0.85rem;">
                                Don't have an account? <a href="/MyProject/register.php">Register free</a>
                            </p>

                        <?php else: ?>
                            <button class="btn enroll-btn w-100" disabled>Enrollment not available</button>
                        <?php endif; ?>

                        <div class="course-includes mt-3">
                            <p class="includes-title">This course includes:</p>
                            <ul class="includes-list">
                                <li>📖 Full course material</li>
                                <li>⏱️ <?php echo htmlspecialchars($course['duration']); ?> of content</li>
                                <li>📱 Access on all devices</li>
                                <li>🏆 Certificate on completion</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COURSE DETAILS -->
<section class="py-5" course-details-section>
    <div class="container">
        <div class="row g-5">
            <div class="col-12 col-lg-7">

                <!-- What you'll learn -->
                <div class="detail-card mb-4">
                    <h4 class="detail-card-title mb-3">📋 What You'll Learn</h4>
                    <div class="row g-2">
                        <?php
                        $learnings = [
                            "Core concepts explained simply",
                            "Practice exercises and examples",
                            "Step-by-step problem solving",
                            "Exam preparation strategies",
                            "Real-world applications",
                            "Assessment and quizzes",
                        ];
                        foreach ($learnings as $item):
                        ?>
                            <div class="col-12 col-md-6">
                                <div class="learning-item">✅ <?php echo $item; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Course Details Table -->
                <div class="detail-card">
                    <h4 class="detail-card-title mb-3">📌 Course Details</h4>
                    <table class="admin-table">
                        <tr>
                            <td><strong>Subject</strong></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class</strong></td>
                            <td>Class <?php echo $course['class']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Instructor</strong></td>
                            <td><?php echo htmlspecialchars($course['instructor']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Duration</strong></td>
                            <td><?php echo htmlspecialchars($course['duration']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Price</strong></td>
                            <td>₹<?php echo number_format($course['price']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Students</strong></td>
                            <td><?php echo $enrollCount; ?> enrolled</td>
                        </tr>
                    </table>
                </div>

            </div>

            <!-- Instructor Card -->
            <div class="col-12 col-lg-5">
                <div class="detail-card">
                    <h4 class="detail-card-title mb-3">👨‍🏫 Your Instructor</h4>
                    <div class="instructor-card">
                        <div class="instructor-avatar">
                            <?php echo strtoupper(substr($course['instructor'], 0, 1)); ?>
                        </div>
                        <div>
                            <h5 class="instructor-name"><?php echo htmlspecialchars($course['instructor']); ?></h5>
                            <p class="instructor-title">Expert Educator</p>
                            <p class="instructor-desc">Experienced instructor dedicated to making learning simple and effective for every student.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>