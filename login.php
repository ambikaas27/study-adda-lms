<?php
include "includes/dbconfig.php";

// If already logged in, redirect away from login page
// No point showing login to someone already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;
        case 'instructor':
            header("Location: instructor/dashboard.php");
            exit;
        default:
            header("Location: student/dashboard.php");
            exit;
    }
}

$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize login inputs too
    $email    = trim(htmlspecialchars($_POST['email']    ?? ''));
    $password = trim($_POST['password']                  ?? '');

    // Basic validation
    if (empty($email))    $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {

        // Fetch user by email using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // password_verify() checks plain password against hash
        // This is the reverse of password_hash() we used in register.php
        if ($user && password_verify($password, $user['password'])) {

            // Store user info in SESSION
            // SESSION is like a login badge — PHP remembers it across pages
            // until the user logs out or closes browser
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/dashboard.php");
                    exit;
                case 'instructor':
                    header("Location: instructor/dashboard.php");
                    exit;
                default:
                    header("Location: student/dashboard.php");
                    exit;
            }
        } else {
            // Vague error message on purpose!
            // Don't tell hackers whether email or password was wrong
            // "Invalid credentials" gives away less information
            $errors[] = "Invalid email or password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Study Adda</title>
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
                <h4 class="auth-title mt-2">Welcome Back!</h4>
                <p class="auth-subtitle">Login to continue your learning journey</p>
            </div>

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

            <!-- LOGIN FORM -->
            <form action="" method="POST">

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

                <!-- Password -->
                <div class="mb-2">
                    <label class="form-label auth-label">Password <span class="required">*</span></label>
                    <input type="password"
                        name="password"
                        class="form-control auth-input"
                        placeholder="Enter your password"
                        required>
                </div>

                <!-- Forgot Password -->
                <div class="text-end mb-4">
                    <a href="forgot-password.php" class="auth-link" style="font-size:0.85rem;">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn enroll-btn w-100">
                    Login →
                </button>

            </form>

            <!-- Register Link -->
            <p class="auth-switch text-center mt-3">
                Don't have an account? <a href="register.php" class="auth-link">Register here</a>
            </p>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>