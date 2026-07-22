<?php
include "includes/dbconfig.php";

$errors = [];

// Determine which step we're on based on session state (not user input,
// so someone can't skip ahead by tampering with a hidden field)
if (isset($_SESSION['reset_verified']) && $_SESSION['reset_verified'] === true) {
    $step = 3;
} elseif (isset($_SESSION['reset_user_id'])) {
    $step = 2;
} else {
    $step = 1;
}

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---------- STEP 1: Email lookup ----------
    if ($step === 1) {
        $email = trim(htmlspecialchars($_POST['email'] ?? ''));

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Enter a valid email address.";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, security_question FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user   = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$user || empty($user['security_question'])) {
                // Deliberately vague — avoids confirming which emails exist
                $errors[] = "We couldn't verify that account. Please check the email or contact support.";
            } else {
                $_SESSION['reset_user_id']  = $user['id'];
                $_SESSION['reset_question'] = $user['security_question'];
                header("Location: forgot-password.php");
                exit;
            }
        }
    }

    // ---------- STEP 2: Security question ----------
    if ($step === 2) {
        $answer = trim($_POST['security_answer'] ?? '');

        if (empty($answer)) {
            $errors[] = "Please provide an answer.";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT security_answer FROM users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['reset_user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row    = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($row && password_verify(strtolower($answer), $row['security_answer'])) {
                $_SESSION['reset_verified'] = true;
                header("Location: forgot-password.php");
                exit;
            } else {
                $errors[] = "That answer doesn't match our records.";
            }
        }
    }

    // ---------- STEP 3: Set new password ----------
    if ($step === 3) {
        $newPassword     = trim($_POST['new_password']     ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (strlen($newPassword) < 6)              $errors[] = "Password must be at least 6 characters.";
        if ($newPassword !== $confirmPassword)      $errors[] = "Passwords do not match.";

        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $_SESSION['reset_user_id']);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                // Clear reset session data — flow is complete
                unset($_SESSION['reset_user_id'], $_SESSION['reset_question'], $_SESSION['reset_verified']);
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-logo text-center mb-4">
                <a href="index.php">
                    <img src="images/logo.png" alt="Study Adda" height="50">
                </a>
                <h4 class="auth-title mt-2">Reset Your Password</h4>
                <p class="auth-subtitle">
                    <?php
                    if ($success) {
                        echo "Password updated!";
                    } elseif ($step === 1) {
                        echo "Enter your registered email to begin";
                    } elseif ($step === 2) {
                        echo "Answer your security question";
                    } else {
                        echo "Choose a new password";
                    }
                    ?>
                </p>
            </div>

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

            <?php if ($success): ?>

                <div class="auth-alert-success">
                    ✅ <strong>Your password has been reset.</strong>
                    <p>You can now log in with your new password.</p>
                </div>
                <p class="auth-switch text-center mt-3">
                    <a href="login.php" class="auth-link">Go to Login</a>
                </p>

            <?php elseif ($step === 1): ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label auth-label">Email Address <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control auth-input" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="btn enroll-btn w-100">Continue →</button>
                </form>

            <?php elseif ($step === 2): ?>

                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label auth-label"><?php echo $_SESSION['reset_question']; ?> <span class="required">*</span></label>
                        <input type="text" name="security_answer" class="form-control auth-input" placeholder="Your answer" required>
                    </div>
                    <button type="submit" class="btn enroll-btn w-100">Verify →</button>
                </form>

            <?php elseif ($step === 3): ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label auth-label">New Password <span class="required">*</span></label>
                        <input type="password" name="new_password" class="form-control auth-input" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label auth-label">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-control auth-input" placeholder="Repeat new password" required>
                    </div>
                    <button type="submit" class="btn enroll-btn w-100">Update Password</button>
                </form>

            <?php endif; ?>

            <p class="auth-switch text-center mt-3">
                Remembered your password? <a href="login.php" class="auth-link">Login here</a>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>