<?php
include "../includes/dbconfig.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$studentId = $_SESSION['user_id'];
$errors    = [];
$success   = false;

// Fetch current student data
$stmt = mysqli_prepare($conn, "SELECT name, email, created_at FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $studentId);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ✅ Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name  = trim(htmlspecialchars($_POST['name']  ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));

    if (empty($name))  $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";

    // Check if new email is already used by someone else
    if (empty($errors)) {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($check, "si", $email, $studentId);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) > 0) {
            $errors[] = "This email is already used by another account.";
        }
        mysqli_stmt_close($check);
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $studentId);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['user_name'] = $name; // update session too
            $student['name']  = $name;
            $student['email'] = $email;
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// ✅ Handle password change
$pwErrors  = [];
$pwSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword     = trim($_POST['new_password']     ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $studentId);
    mysqli_stmt_execute($stmt);
    $res  = mysqli_stmt_get_result($stmt);
    $row  = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!password_verify($currentPassword, $row['password'])) {
        $pwErrors[] = "Current password is incorrect.";
    }
    if (strlen($newPassword) < 6) {
        $pwErrors[] = "New password must be at least 6 characters.";
    }
    if ($newPassword !== $confirmPassword) {
        $pwErrors[] = "New passwords do not match.";
    }

    if (empty($pwErrors)) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $hashed, $studentId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $pwSuccess = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Study Adda</title>
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
                <div class="sidebar-avatar">
                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo htmlspecialchars($student['name']); ?></p>
                <span class="sidebar-role">Student</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 My Courses</a>
                <a href="progress.php" class="sidebar-link">📈 My Progress</a>
                <a href="profile.php" class="sidebar-link active">👤 My Profile</a>
                <a href="/MyProject/courses.php" class="sidebar-link">🔍 Browse Courses</a>
                <a href="/MyProject/logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">👤 My Profile</h4>
                    <p class="topbar-sub">Manage your account details</p>
                </div>
            </div>

            <div class="row g-4">

                <!-- PROFILE INFO FORM -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">✏️ Edit Profile</h5>
                        </div>

                        <?php if ($success): ?>
                            <div class="auth-alert-success mb-3">✅ <strong>Profile updated successfully!</strong></div>
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

                        <form action="" method="POST">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="mb-3">
                                <label class="form-label auth-label">Full Name</label>
                                <input type="text" name="name" class="form-control auth-input"
                                    value="<?php echo htmlspecialchars($student['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label auth-label">Email Address</label>
                                <input type="email" name="email" class="form-control auth-input"
                                    value="<?php echo htmlspecialchars($student['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label auth-label">Role</label>
                                <input type="text" class="form-control auth-input" value="Student" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label auth-label">Member Since</label>
                                <input type="text" class="form-control auth-input"
                                    value="<?php echo date('d M Y', strtotime($student['created_at'])); ?>" disabled>
                            </div>
                            <button type="submit" class="btn enroll-btn w-100">💾 Save Changes</button>
                        </form>
                    </div>
                </div>

                <!-- CHANGE PASSWORD FORM -->
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <h5 class="dashboard-card-title">🔒 Change Password</h5>
                        </div>

                        <?php if ($pwSuccess): ?>
                            <div class="auth-alert-success mb-3">✅ <strong>Password changed successfully!</strong></div>
                        <?php endif; ?>

                        <?php if (!empty($pwErrors)): ?>
                            <div class="auth-alert-error mb-3">
                                ⚠️
                                <ul class="error-list">
                                    <?php foreach ($pwErrors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <input type="hidden" name="change_password" value="1">
                            <!-- Current Password -->
                            <div class="mb-3">
                                <label class="form-label auth-label">Current Password</label>

                                <div class="position-relative">
                                    <input type="password"
                                        id="current_password"
                                        name="current_password"
                                        class="form-control auth-input"
                                        required>

                                    <span class="toggle-password"
                                        onclick="togglePassword('current_password', this)">
                                        👁️
                                    </span>
                                </div>
                            </div>


                            <!-- New Password -->
                            <div class="mb-3">
                                <label class="form-label auth-label">New Password</label>

                                <div class="position-relative">
                                    <input type="password"
                                        id="new_password"
                                        name="new_password"
                                        class="form-control auth-input"
                                        required>

                                    <span class="toggle-password"
                                        onclick="togglePassword('new_password', this)">
                                        👁️
                                    </span>
                                </div>
                            </div>


                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label class="form-label auth-label">Confirm Password</label>

                                <div class="position-relative">
                                    <input type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        class="form-control auth-input"
                                        required>

                                    <span class="toggle-password"
                                        onclick="togglePassword('confirm_password', this)">
                                        👁️
                                    </span>
                                </div>
                            </div>
                            <button type="submit" class="btn enroll-btn w-100">🔑 Update Password</button>
                        </form>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);

            if (field.type === 'password') {
                field.type = 'text';
                icon.textContent = '🙈';
            } else {
                field.type = 'password';
                icon.textContent = '👁️';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>