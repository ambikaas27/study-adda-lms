<?php
include "../includes/dbconfig.php";

// Instructor Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$errors  = [];
$success = false;

// Logged in instructor name
$instructor = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize inputs
    $title       = trim(htmlspecialchars($_POST['title'] ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $duration    = trim(htmlspecialchars($_POST['duration'] ?? ''));
    $price       = floatval($_POST['price'] ?? 0);
    $class       = intval($_POST['class'] ?? 0);

    // Validation
    if (empty($title)) {
        $errors[] = "Course title is required.";
    }

    if ($class < 1 || $class > 12) {
        $errors[] = "Please select a valid class.";
    }

    if ($price < 0) {
        $errors[] = "Price cannot be negative.";
    }

    // Handle image upload
    $imageName = 'default.jpg';

    if (!empty($_FILES['image']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = $_FILES['image']['type'];
        $fileSize     = $_FILES['image']['size'];

        if (!in_array($fileType, $allowedTypes)) {

            $errors[] = "Only JPG, PNG and WEBP images are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) {

            $errors[] = "Image size must be less than 2 MB.";
        } else {

            $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid('course_') . "." . $ext;
            $uploadDir = "../images/courses/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $imageName
            );
        }
    }


    // Insert into database

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO courses
            (title, description, instructor, duration, price, class, image)
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
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Add Course | Instructor | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet">
    <link rel="stylesheet"
        href="/MyProject/css/style.css">
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
                    <?php echo strtoupper(substr($instructor, 0, 1)); ?>
                </div>
                <p class="sidebar-name">
                    <?php echo htmlspecialchars($instructor); ?>
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
                    class="sidebar-link">
                    📚 My Courses
                </a>
                <a href="add-course.php"
                    class="sidebar-link active">
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
                        ➕ Add New Course
                    </h4>
                    <p class="topbar-sub">
                        Create a new course for your students.
                    </p>
                </div>
                <a href="my-courses.php"
                    class="btn btn-outline-success">
                    ← Back to My Courses
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="dashboard-card">

                        <!-- Success Message -->
                        <?php if ($success): ?>
                            <div class="auth-alert-success">
                                ✅ <strong>Course added successfully!</strong>
                                <p>
                                    <a href="add-course.php">
                                        Add another course
                                    </a>
                                    or
                                    <a href="my-courses.php">
                                        view all courses
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert-error">
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                        <?php endif; ?>

                        <form action=""
                            method="POST"
                            enctype="multipart/form-data">
                            <div class="row g-3">

                            <!-- TITLE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Course Title
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text"
                                        name="title"
                                        class="form-control auth-input"
                                        placeholder="e.g. Mathematics Class 5"
                                        value="<?php echo isset($title) ? $title : ''; ?>"
                                        required>
                                </div>

                                <!-- DESCRIPTION -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Description
                                    </label>
                                    <textarea name="description"
                                        rows="3"
                                        class="form-control auth-input"
                                        placeholder="Brief description of the course..."><?php echo isset($description) ? $description : ''; ?></textarea>
                                </div>

                                <!-- DURATION -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">
                                        Duration
                                    </label>
                                    <input type="text"
                                        name="duration"
                                        class="form-control auth-input"
                                        placeholder="e.g. 3 Months"
                                        value="<?php echo isset($duration) ? $duration : ''; ?>">
                                </div>

                                <!-- CLASS -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">
                                        Class
                                        <span class="required">*</span>
                                    </label>
                                    <select name="class"
                                        class="form-select auth-input"
                                        required>
                                        <option value="">
                                            Select Class
                                        </option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>"
                                                <?php echo (isset($class) && $class == $i) ? 'selected' : ''; ?>>
                                                Class <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- PRICE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Price (₹)
                                    </label>
                                    <input type="number"
                                        name="price"
                                        min="0"
                                        class="form-control auth-input"
                                        placeholder="Enter 0 for free courses"
                                        value="<?php echo isset($price) ? $price : ''; ?>">
                                    <small class="text-muted">
                                        Enter 0 if you want to create a free course.
                                    </small>
                                </div>

                                <!-- IMAGE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Course Image
                                    </label>
                                    <input type="file"
                                        name="image"
                                        class="form-control auth-input"
                                        accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">
                                        JPG, PNG or WEBP - Max 2 MB.
                                    </small>
                                </div>
                                
                                <!-- SUBMIT -->
                                <div class="col-12">
                                    <button type="submit"
                                        class="btn enroll-btn w-100">
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