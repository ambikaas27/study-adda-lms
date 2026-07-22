<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get course ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) {
    header("Location: courses.php");
    exit;
}

// Fetch existing course data to pre-fill the form
$stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// If course not found — redirect back
if (!$course) {
    header("Location: courses.php");
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim(htmlspecialchars($_POST['title']       ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $instructor  = trim(htmlspecialchars($_POST['instructor']  ?? ''));
    $duration    = trim(htmlspecialchars($_POST['duration']    ?? ''));
    $price       = floatval($_POST['price']                    ?? 0);
    $class       = intval($_POST['class']                      ?? 0);

    if (empty($title))      $errors[] = "Course title is required.";
    if (empty($instructor)) $errors[] = "Instructor name is required.";
    if ($class < 1 || $class > 8) $errors[] = "Please select a valid class.";

    // Handle image update
    // Keep old image if no new one uploaded
    $imageName = $course['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = $_FILES['image']['type'];
        $fileSize     = $_FILES['image']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and WEBP images are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = "Image must be less than 2MB.";
        } else {
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('course_') . '.' . $ext;
            $uploadDir = "../images/courses/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    // UPDATE query instead of INSERT
    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE courses SET title=?, description=?, instructor=?, duration=?, price=?, class=?, image=? WHERE id=?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssdisi",
            $title,
            $description,
            $instructor,
            $duration,
            $price,
            $class,
            $imageName,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            // Refresh course data after update
            $course['title']       = $title;
            $course['description'] = $description;
            $course['instructor']  = $instructor;
            $course['duration']    = $duration;
            $course['price']       = $price;
            $course['class']       = $class;
            $course['image']       = $imageName;
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
    <title>Edit Course | Admin | Study Adda</title>
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
                <div class="sidebar-avatar" style="background:#ff6b6c;">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $_SESSION['user_name']; ?></p>
                <span class="sidebar-role" style="background:rgba(255,107,108,0.2);color:#ff6b6c;">Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link active">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">✏️ Edit Course</h4>
                    <p class="topbar-sub">Editing: <strong><?php echo htmlspecialchars($course['title']); ?></strong></p>
                </div>
                <a href="courses.php" class="btn btn-outline-admin">← Back to Courses</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="dashboard-card">

                        <?php if ($success): ?>
                            <div class="auth-alert-success mb-3">
                                ✅ <strong>Course updated successfully!</strong>
                                <p><a href="courses.php">Back to all courses</a></p>
                            </div>
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

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label auth-label">Course Title <span class="required">*</span></label>
                                    <input type="text" name="title" class="form-control auth-input"
                                        value="<?php echo htmlspecialchars($course['title']); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label auth-label">Description</label>
                                    <textarea name="description" class="form-control auth-input" rows="3"><?php echo htmlspecialchars($course['description']); ?></textarea>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Instructor <span class="required">*</span></label>
                                    <input type="text" name="instructor" class="form-control auth-input"
                                        value="<?php echo htmlspecialchars($course['instructor']); ?>" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Duration</label>
                                    <input type="text" name="duration" class="form-control auth-input"
                                        value="<?php echo htmlspecialchars($course['duration']); ?>">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Class <span class="required">*</span></label>
                                    <select name="class" class="form-select auth-input" required>
                                        <option value="">Select Class</option>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>"
                                                <?php echo $course['class'] == $i ? 'selected' : ''; ?>>
                                                Class <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Price (₹)</label>
                                    <input type="number" name="price" class="form-control auth-input"
                                        min="0" value="<?php echo $course['price']; ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label auth-label">Course Image</label>
                                    <?php if ($course['image']): ?>
                                        <div class="mb-2">
                                            <img src="/MyProject/images/courses/<?php echo htmlspecialchars($course['image']); ?>"
                                                alt="Current image" height="80"
                                                style="border-radius:8px; object-fit:cover;"
                                                onerror="this.style.display='none'">
                                            <small class="text-muted d-block mt-1">Current image — upload new to replace</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control auth-input"
                                        accept="image/jpeg,image/png,image/webp">
                                </div>

                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn enroll-btn w-100">
                                        ✅ Update Course
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