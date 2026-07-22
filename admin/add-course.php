<?php
include "../includes/dbconfig.php";

// ✅ Admin guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize all inputs
    $title       = trim(htmlspecialchars($_POST['title']       ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $instructor  = trim(htmlspecialchars($_POST['instructor']  ?? ''));
    $duration    = trim(htmlspecialchars($_POST['duration']    ?? ''));
    $price       = floatval($_POST['price']                    ?? 0);
    $class       = intval($_POST['class']                      ?? 0);

    // Validate
    if (empty($title))      $errors[] = "Course title is required.";
    if (empty($instructor)) $errors[] = "Instructor name is required.";
    if ($class < 1 || $class > 12) $errors[] = "Please select a valid class.";
    if ($price < 0)         $errors[] = "Price cannot be negative.";

    // Handle image upload
    $imageName = 'default.jpg'; // fallback image
    if (!empty($_FILES['image']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = $_FILES['image']['type'];
        $fileSize     = $_FILES['image']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and WEBP images are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = "Image must be less than 2MB.";
        } else {
            // Create unique filename to avoid overwriting
            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('course_') . '.' . $ext;
            $uploadDir = "../images/courses/";

            // Create folder if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
    }

    // Save to database
    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO courses (title, description, instructor, duration, price, class, image)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssdis",
            $title,
            $description,
            $instructor,
            $duration,
            $price,
            $class,
            $imageName
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
    <title>Add Course | Admin | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="dashboard-body">

    <div class="dashboard-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <a href="../index.php">
                    <img src="../images/logo-dark.png" alt="Study Adda" height="40">
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
                <a href="courses.php" class="sidebar-link">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link active">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link">🔒 Change Password</a>
                <a href="../logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">➕ Add New Course</h4>
                    <p class="topbar-sub">Fill in the details below to add a new course</p>
                </div>
                <a href="courses.php" class="btn btn-outline-admin">← Back to Courses</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="dashboard-card">

                        <?php if ($success): ?>
                            <div class="auth-alert-success">
                                ✅ <strong>Course added successfully!</strong>
                                <p>
                                    <a href="add-course.php">Add another course</a> or
                                    <a href="courses.php">view all courses</a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert-error">
                                ⚠️
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- enctype needed for file uploads -->
                        <!-- Without enctype="multipart/form-data" images won't upload! -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">

                                <!-- Course Title -->
                                <div class="col-12">
                                    <label class="form-label auth-label">Course Title <span class="required">*</span></label>
                                    <input type="text"
                                        name="title"
                                        class="form-control auth-input"
                                        placeholder="e.g. Mathematics Class 5"
                                        value="<?php echo isset($title) ? $title : ''; ?>"
                                        required>
                                </div>

                                <!-- Description -->
                                <div class="col-12">
                                    <label class="form-label auth-label">Description</label>
                                    <textarea name="description"
                                        class="form-control auth-input"
                                        rows="3"
                                        placeholder="Brief description of the course..."><?php echo isset($description) ? $description : ''; ?></textarea>
                                </div>

                                <!-- Instructor -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Instructor Name <span class="required">*</span></label>
                                    <input type="text"
                                        name="instructor"
                                        class="form-control auth-input"
                                        placeholder="e.g. Mrs. Sharma"
                                        value="<?php echo isset($instructor) ? $instructor : ''; ?>"
                                        required>
                                </div>

                                <!-- Duration -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Duration</label>
                                    <input type="text"
                                        name="duration"
                                        class="form-control auth-input"
                                        placeholder="e.g. 3 months"
                                        value="<?php echo isset($duration) ? $duration : ''; ?>">
                                </div>

                                <!-- Class -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Class <span class="required">*</span></label>
                                    <select name="class" class="form-select auth-input" required>
                                        <option value="">Select Class</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>"
                                                <?php echo (isset($class) && $class == $i) ? 'selected' : ''; ?>>
                                                Class <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- Price -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Price (₹)</label>
                                    <input type="number"
                                        name="price"
                                        class="form-control auth-input"
                                        placeholder="e.g. 499"
                                        min="0"
                                        value="<?php echo isset($price) ? $price : ''; ?>">
                                </div>

                                <!-- Image Upload -->
                                <div class="col-12">
                                    <label class="form-label auth-label">Course Image</label>
                                    <input type="file"
                                        name="image"
                                        class="form-control auth-input"
                                        accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">JPG, PNG or WEBP — Max 2MB. Leave empty to use default image.</small>
                                </div>

                                <!-- Submit -->
                                <div class="col-12 mt-2">
                                    <button type="submit" class="btn enroll-btn w-100">
                                        ➕ Add Course
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