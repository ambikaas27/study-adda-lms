<?php
include "includes/dbconfig.php";

$errors  = [];
$success = false;

// Determine which role should be pre-selected in the dropdown.
$allowedRoles = ['student', 'instructor'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedRole = $_POST['role'] ?? 'student';
} else {
    $selectedRole = $_GET['role'] ?? 'student';
}
if (!in_array($selectedRole, $allowedRoles)) {
    $selectedRole = 'student';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize every input — always!
    $name     = trim(htmlspecialchars($_POST['name']     ?? ''));
    $email    = trim(htmlspecialchars($_POST['email']    ?? ''));
    $password = trim($_POST['password']                  ?? '');
    $confirm  = trim($_POST['confirm']                   ?? '');
    $role     = trim(htmlspecialchars($_POST['role']     ?? 'student'));
    $securityQuestion = trim($_POST['security_question'] ?? '');
    $securityAnswer   = trim($_POST['security_answer']   ?? '');

    // Validate inputs
    if (empty($name))                                    $errors[] = "Full name is required.";
    if (empty($email))                                   $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))      $errors[] = "Enter a valid email address.";
    if (strlen($password) < 6)                           $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm)                          $errors[] = "Passwords do not match.";
    if (!in_array($role, ['student', 'instructor']))     $errors[] = "Invalid role selected.";
    if (empty($securityQuestion))                        $errors[] = "Please select a security question.";
    if (empty($securityAnswer))                           $errors[] = "Please provide an answer to your security question.";

    // Check if email already exists
    // We use prepared statements to prevent SQL injection
    // Never put user input directly into SQL queries!
    if (empty($errors)) {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $errors[] = "This email is already registered. Please login instead.";
        }
        mysqli_stmt_close($checkStmt);
    }

    // Hash password before saving
    // password_hash() uses bcrypt — very secure
    // NEVER save plain text passwords!
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $hashedAnswer   = password_hash(strtolower($securityAnswer), PASSWORD_DEFAULT); // lowercase for forgiving matching

        // Insert into database using prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $hashedPassword, $role, $securityQuestion, $hashedAnswer);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            // Redirect to login after 2 seconds
            header("refresh:2;url=login.php");
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
    <title>Register | Study Adda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">

    <div class="auth-wrapper">
        <div class="auth-card">

            <!-- LOGO -->
            <div class="auth-logo text-center mb-4">
                <a href="index.php">
                    <img src="images/logo.png" alt="Study Adda" height="50">
                </a>
                <h4 class="auth-title mt-2">Create Your Account</h4>
                <p class="auth-subtitle">Join thousands of students on Study Adda</p>
            </div>

            <?php if ($success): ?>
                <!-- SUCCESS MESSAGE -->
                <div class="auth-alert-success">
                    ✅ <strong>Account created successfully!</strong>
                    <p>Redirecting you to login page...</p>
                </div>

            <?php else: ?>

                <!-- ERROR MESSAGES -->
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

                <!-- REGISTER FORM -->
                <form action="" method="POST">

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label class="form-label auth-label">Full Name <span class="required">*</span></label>
                        <input type="text"
                            name="name"
                            class="form-control auth-input"
                            placeholder="Your full name"
                            value="<?php echo isset($name) ? $name : ''; ?>"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label auth-label">Email Address <span class="required">*</span></label>
                        <input type="email"
                            name="email"
                            class="form-control auth-input"
                            placeholder="your@email.com"
                            value="<?php echo isset($email) ? $email : ''; ?>"
                            required>
                    </div>

                    <!-- Role -->
                    <!-- Role selection — admin can only be created manually -->
                    <!-- We never let users register as admin from this form! -->
                    <div class="mb-3">
                        <label class="form-label auth-label">I am a <span class="required">*</span></label>
                        <select name="role" class="form-select auth-input" required>
                            <option value="student" <?php echo ($selectedRole === 'student')    ? 'selected' : ''; ?>>Student</option>
                            <option value="instructor" <?php echo ($selectedRole === 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                        </select>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label auth-label">Password <span class="required">*</span></label>
                        <input type="password"
                            name="password"
                            class="form-control auth-input"
                            placeholder="Minimum 6 characters"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label auth-label">Security Question <span class="required">*</span></label>
                        <select name="security_question" class="form-select auth-input" required>
                            <option value="">Select a question</option>
                            <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                            <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                            <option value="What is your favorite teacher's name?">What is your favorite teacher's name?</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label auth-label">Answer <span class="required">*</span></label>
                        <input type="text" name="security_answer" class="form-control auth-input" placeholder="Your answer" required>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="form-label auth-label">Confirm Password <span class="required">*</span></label>
                        <input type="password"
                            name="confirm"
                            class="form-control auth-input"
                            placeholder="Repeat your password"
                            required>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn enroll-btn w-100">
                        Create Account 🚀
                    </button>

                </form>

                <!-- Login Link -->
                <p class="auth-switch text-center mt-3">
                    Already have an account? <a href="login.php" class="auth-link">Login here</a>
                </p>

            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>