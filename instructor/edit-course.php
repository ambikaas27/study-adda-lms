<?php

include "../includes/dbconfig.php";

// Instructor Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: ../login.php");
    exit;
}

$errors = [];
$success = false;

$instructor = $_SESSION['user_name'];

// Check course id

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {

    header("Location: my-courses.php");
    exit;
}

$courseId = intval($_GET['course_id']);

// Fetch course

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM courses
     WHERE id = ? AND instructor = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $courseId,
    $instructor
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$course = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Invalid course
if (!$course) {
    header("Location: my-courses.php");
    exit;
}

// Existing values
$title       = $course['title'];
$description = $course['description'];
$duration    = $course['duration'];
$price       = $course['price'];
$class       = $course['class'];
$imageName   = $course['image'];

/*
|--------------------------------------------------------------------------
| UPDATE COURSE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim(htmlspecialchars($_POST['title'] ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $duration    = trim(htmlspecialchars($_POST['duration'] ?? ''));
    $price       = floatval($_POST['price'] ?? 0);
    $class       = intval($_POST['class'] ?? 0);

    // VALIDATIONS

    if (empty($title)) {
        $errors[] = "Course title is required.";
    }

    if ($class < 1 || $class > 12) {
        $errors[] = "Please select a valid class.";
    }

    if ($price < 0) {
        $errors[] = "Price cannot be negative.";
    }

    // IMAGE UPLOAD

    $newImageName = $imageName;

    if (!empty($_FILES['image']['name'])) {


        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size'];

        if (!in_array($fileType, $allowedTypes)) {

            $errors[] = "Only JPG, PNG and WEBP images are allowed.";
        } elseif ($fileSize > 2 * 1024 * 1024) {

            $errors[] = "Image size must be less than 2 MB.";
        } else {

            $ext = pathinfo(
                $_FILES['image']['name'],
                PATHINFO_EXTENSION
            );

            $newImageName = uniqid("course_") . "." . $ext;

            $uploadDir = "../images/courses/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                $uploadDir . $newImageName
            );

            // Delete old image

            if (
                !empty($imageName)
                && $imageName !== "default.jpg"
                && file_exists($uploadDir . $imageName)
            ) {

                unlink($uploadDir . $imageName);
            }
        }
    }

    // UPDATE DATABASE

    if (empty($errors)) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE courses
            SET title = ?,
                description = ?,
                duration = ?,
                price = ?,
                class = ?,
                image = ?
            WHERE id = ? AND instructor = ?"

        );

        mysqli_stmt_bind_param(

            $stmt,
            "sssdiiss",

            $title,
            $description,
            $duration,
            $price,
            $class,
            $newImageName,
            $courseId,
            $instructor

        );

        if (mysqli_stmt_execute($stmt)) {

            $success = true;
            $imageName = $newImageName;
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
    <meta name="viewport"content="width=device-width, initial-scale=1.0">
    <title>Edit Course | Instructor | Study Adda</title>
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
                    <?php echo strtoupper(substr($instructor, 0, 1)); ?>
                </div>

                <p class="sidebar-name">
                    <?php echo htmlspecialchars($instructor); ?>
                </p>

                <span class="sidebar-role" style="background:rgba(16,185,129,.2);color:#10b981;">Instructor</span>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="my-courses.php"class="sidebar-link active">📚 My Courses</a>
                <a href="add-course.php"class="sidebar-link">➕ Add Course</a>
                <a href="my-students.php"class="sidebar-link">👨‍🎓 My Students</a>
                <a href="profile.php" class="sidebar-link">👤 My Profile</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>

        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">
            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">✏️ Edit Course</h4>
                    <p class="topbar-sub">
                        Update your course details.
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
                        <!-- SUCCESS MESSAGE -->

                        <?php if ($success): ?>
                            <div class="auth-alert-success">
                                <strong>
                                    Course updated successfully!
                                </strong>
                                <p class="mt-2 mb-0">
                                    Your course details have been updated.
                                </p>
                            </div>

                        <?php endif; ?>

                        <!-- ERROR MESSAGES -->
                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert-error">
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                        <?php endif; ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">

                            <!-- COURSE TITLE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Course Title
                                        <span class="required">*</span>
                                    </label>

                                    <input type="text" name="title" class="form-control auth-input" value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>

                                <!-- DESCRIPTION -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Description
                                    </label>

                                    <textarea name="description"
                                        rows="3"
                                        class="form-control auth-input"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>

                                <!-- DURATION -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">
                                        Duration
                                    </label>

                                    <input type="text"
                                        name="duration"
                                        class="form-control auth-input"
                                        value="<?php echo htmlspecialchars($duration); ?>">
                                </div>

                                <!-- CLASS -->
                                <div class="col-12 col-md-6">
                                    <label class="form-label auth-label">Class <span class="required">*</span> </label>

                                    <select name="class"
                                        class="form-select auth-input"
                                        required>
                                        <option value="">
                                            Select Class
                                        </option>

                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>"
                                                <?php echo ($class == $i) ? "selected" : ""; ?>>
                                                Class <?php echo $i; ?>
                                            </option>

                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <!-- PRICE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">Price (₹)</label>
                                    <input type="number" name="price" min="0" class="form-control auth-input" value="<?php echo $price; ?>">
                                    <small class="text-muted"> Enter 0 if this is a free course.</small>
                                </div>

                                <!-- CURRENT IMAGE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">Current Course Image </label>

                                    <br>

                                    <img src="/MyProject/images/courses/<?php echo htmlspecialchars($imageName); ?>" alt="Course Image"
                                        style=" max-width:200px;
                                        border-radius:12px;
                                        margin-bottom:10px;
                                        border:1px solid #ddd;">
                                </div>

                                <!-- UPLOAD NEW IMAGE -->
                                <div class="col-12">
                                    <label class="form-label auth-label">
                                        Upload New Image
                                    </label>

                                    <input type="file"
                                        name="image"
                                        class="form-control auth-input"
                                        accept="image/jpeg,image/png,image/webp">

                                    <small class="text-muted">
                                        Leave this empty if you don't want to change the image.
                                        <br>
                                        JPG, PNG or WEBP - Max 2 MB.

                                    </small>
                                </div>

                                <!-- SUBMIT BUTTON -->
                                <div class="col-12">
                                    <button type="submit"
                                        class="btn enroll-btn w-100">
                                        ✏️ Update Course
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