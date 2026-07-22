<?php
$pageTitle = "Search Results";
include "includes/dbconfig.php";
include "includes/header.php";

// ✅ Get search query from URL (?q=...)
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

$results = [];
$totalResults = 0;

if (!empty($query)) {
    // ✅ Search across title, description, and instructor using LIKE
    // % wildcards match anything before/after the search term
    $searchTerm = "%" . $query . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM courses
         WHERE title LIKE ? OR description LIKE ? OR instructor LIKE ?
         ORDER BY created_at DESC"
    );
    mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $totalResults = mysqli_num_rows($result);
    mysqli_stmt_close($stmt);
}
?>

<!-- BANNER -->
<section class="courses-banner">
    <div class="courses-banner-overlay">
        <h1 class="courses-banner-title">🔍 Search Results</h1>
        <?php if (!empty($query)): ?>
            <p class="courses-banner-sub">Results for "<?php echo htmlspecialchars($query); ?>"</p>
        <?php else: ?>
            <p class="courses-banner-sub">Enter a search term to find courses</p>
        <?php endif; ?>
    </div>
</section>

<!-- SEARCH BAR (re-search) -->
<section class="py-4" style="background:#f3efef;">
    <div class="container">
        <form action="search.php" method="GET" class="d-flex justify-content-center">
            <div class="d-flex" style="max-width:500px; width:100%;">
                <input type="search" name="q" class="form-control me-2 search-input"
                    placeholder="Search by course, subject, or instructor..."
                    value="<?php echo htmlspecialchars($query); ?>"
                    style="width:100%;">
                <button type="submit" class="btn btn-search">Search</button>
            </div>
        </form>
    </div>
</section>

<!-- RESULTS -->
<section class="courses-section py-5">
    <div class="container">

        <?php if (empty($query)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-icon">🔍</div>
                <h4>Start searching</h4>
                <p class="text-muted">Type a course name, subject, or instructor above</p>
            </div>

        <?php elseif ($totalResults === 0): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-icon">📭</div>
                <h4>No results found for "<?php echo htmlspecialchars($query); ?>"</h4>
                <p class="text-muted">Try a different search term</p>
                <a href="courses.php" class="btn enroll-btn mt-2">Browse All Courses</a>
            </div>

        <?php else: ?>
            <p class="results-count mb-4">
                Found <strong><?php echo $totalResults; ?></strong> result(s) for "<strong><?php echo htmlspecialchars($query); ?></strong>"
            </p>

            <div class="row g-4">
                <?php while ($course = mysqli_fetch_assoc($result)): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="course-card h-100">
                            <div class="course-img-wrap">
                                <img src="images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                    alt="<?php echo htmlspecialchars($course['title']); ?>"
                                    class="course-img"
                                    onerror="this.style.display='none'">
                                <span class="course-class-badge">Class <?php echo $course['class']; ?></span>
                            </div>
                            <div class="course-body">
                                <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="course-desc"><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span class="meta-item">👨‍🏫 <?php echo htmlspecialchars($course['instructor']); ?></span>
                                    <span class="meta-item">⏱️ <?php echo htmlspecialchars($course['duration']); ?></span>
                                </div>
                            </div>
                            <div class="course-footer">
                                <span class="course-price">₹<?php echo number_format($course['price']); ?></span>
                                <a href="course-detail.php?id=<?php echo $course['id']; ?>" class="btn enroll-btn btn-sm">
                                    View Course
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