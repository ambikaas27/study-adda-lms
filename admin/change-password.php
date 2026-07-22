<?php
include "../includes/dbconfig.php";

// Admin guard — only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$adminName = $_SESSION['user_name'];
$adminId   = $_SESSION['user_id'];

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword     = trim($_POST['new_password']      ?? '');
    $confirmPassword = trim($_POST['confirm_password']  ?? '');

    // Validate inputs
    if (empty($currentPassword))                     $errors[] = "Current password is required.";
    if (strlen($newPassword) < 6)                     $errors[] = "New password must be at least 6 characters.";
    if ($newPassword !== $confirmPassword)             $errors[] = "New passwords do not match.";

    // Verify current password matches what's in the DB
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row || !password_verify($currentPassword, $row['password'])) {
            $errors[] = "Current password is incorrect.";
        }
    }

    // Update password
    if (empty($errors)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $hashedPassword, $adminId);

        if (mysqli_stmt_execute($updateStmt)) {
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($updateStmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Study Adda</title>
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
                    <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                </div>
                <p class="sidebar-name"><?php echo $adminName; ?></p>
                <span class="sidebar-role" style="background:rgba(255,107,108,0.2);color:#ff6b6c;">Admin</span>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">📊 Dashboard</a>
                <a href="courses.php" class="sidebar-link">📚 Manage Courses</a>
                <a href="add-course.php" class="sidebar-link">➕ Add Course</a>
                <a href="users.php" class="sidebar-link">👥 Manage Users</a>
                <a href="messages.php" class="sidebar-link">📩 Messages</a>
                <a href="change-password.php" class="sidebar-link active">🔒 Change Password</a>
                <a href="../logout.php" class="sidebar-link logout-link">🚪 Logout</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">

            <!-- Top Bar -->
            <div class="dashboard-topbar">
                <div>
                    <h4 class="topbar-title">Change Password 🔒</h4>
                    <p class="topbar-sub">Update your admin account password.</p>
                </div>
                <div class="topbar-date"><?php echo date("D, d M Y"); ?></div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="dashboard-card">

                        <?php if ($success): ?>
                            <div class="auth-alert-success">
                                ✅ <strong>Password updated successfully!</strong>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="auth-alert-error">
                                ⚠️
                                <ul class="error-list mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">

                            <div class="mb-3">
                                <label class="form-label auth-label">Current Password <span class="required">*</span></label>
                                <div class="position-relative">
                                    <input type="password"
                                        id="current_password"
                                        name="current_password"
                                        class="form-control auth-input"
                                        placeholder="Enter current password"
                                        required>
                                    <span class="toggle-password" onclick="togglePassword('current_password', this)">👁️</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label auth-label">New Password <span class="required">*</span></label>
                                <div class="position-relative">
                                    <input type="password"
                                        id="new_password"
                                        name="new_password"
                                        class="form-control auth-input"
                                        placeholder="Minimum 6 characters"
                                        required>
                                    <span class="toggle-password" onclick="togglePassword('new_password', this)">👁️</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label auth-label">Confirm New Password <span class="required">*</span></label>
                                <div class="position-relative">
                                    <input type="password"
                                        id="confirm_password"
                                        name="confirm_password"
                                        class="form-control auth-input"
                                        placeholder="Repeat new password"
                                        required>
                                    <span class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</span>
                                </div>
                            </div>

                            <button type="submit" class="btn enroll-btn w-100">
                                Update Password
                            </button>

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