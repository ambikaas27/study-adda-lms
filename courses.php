<?php
$pageTitle = "Courses";
include "includes/dbconfig.php";
include "includes/header.php";

// Read ?class= from URL
$selectedClass = isset($_GET['class']) ? intval($_GET['class']) : 0;

// Fetch from database
if ($selectedClass > 0) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE class = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $selectedClass);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM courses ORDER BY created_at DESC");
}

$totalCourses = mysqli_num_rows($result);
?>

<!-- BANNER -->
<section class="courses-banner">
    <div class="courses-banner-overlay">
        <h1 class="courses-banner-title">Our Courses</h1>
        <p class="courses-banner-sub">Find the right course for your class and start learning today</p>
    </div>
</section>

<!-- FILTER BAR -->
<section class="filter-section py-4">
    <div class="container">
        <div class="filter-bar">

            <a href="courses.php" class="filter-btn <?php echo $selectedClass === 0 ? 'active' : ''; ?>">
                All Classes
            </a>

            <?php for ($i = 1; $i <= 12; $i++): ?>
                <a href="courses.php?class=<?php echo $i; ?>"
                    class="filter-btn <?php echo $selectedClass === $i ? 'active' : ''; ?>">
                    Class <?php echo $i; ?>
                </a>
            <?php endfor; ?>

        </div>
    </div>
</section>

<!-- COURSES GRID -->
<section class="courses-section py-5">
    <div class="container">

        <!-- Results count -->
        <p class="results-count mb-4">
            <?php if ($selectedClass > 0): ?>
                Showing <strong><?php echo $totalCourses; ?></strong> course(s) for <strong>Class <?php echo $selectedClass; ?></strong>
            <?php else: ?>
                Showing all <strong><?php echo $totalCourses; ?></strong> courses
            <?php endif; ?>
        </p>

        <?php if ($totalCourses === 0): ?>
            <!-- Empty state -->
            <div class="empty-state text-center py-5">
                <div class="empty-icon">📭</div>
                <h4>No courses found<?php echo $selectedClass > 0 ? " for Class $selectedClass" : ""; ?></h4>
                <p class="text-muted">Check back soon — we're adding more courses!</p>
                <?php if ($selectedClass > 0): ?>
                    <a href="courses.php" class="btn enroll-btn mt-2">View All Courses</a>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="row g-4">
                <?php while ($course = mysqli_fetch_assoc($result)): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="course-card h-100">

                            <!-- Course Image -->
                            <div class="course-img-wrap">
                                <img src="images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                    alt="<?php echo htmlspecialchars($course['title']); ?>"
                                    class="course-img"
                                    onerror="this.style.display='none'">
                                <span class="course-class-badge">Class <?php echo $course['class']; ?></span>
                            </div>

                            <!-- Course Body -->
                            <div class="course-body">
                                <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="course-desc"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span class="meta-item">👨‍🏫 <?php echo htmlspecialchars($course['instructor']); ?></span>
                                    <span class="meta-item">⏱️ <?php echo htmlspecialchars($course['duration']); ?></span>
                                </div>
                            </div>

                            <!-- Course Footer -->
                            <div class="course-footer">
                                <span class="course-price">₹<?php echo number_format($course['price']); ?></span>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn enroll-btn btn-sm">
                                    Enroll Now
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include "includes/footer.php"; ?>