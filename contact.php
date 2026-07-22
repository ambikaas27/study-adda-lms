<?php
$pageTitle = "Contact Us";
include "includes/dbconfig.php";
include "includes/header.php";

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim(htmlspecialchars($_POST['name']    ?? ''));
    $email   = trim(htmlspecialchars($_POST['email']   ?? ''));
    $subject = trim(htmlspecialchars($_POST['subject'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));

    if (empty($name))    $errors[] = "Name is required.";
    if (empty($email))   $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";
    if (empty($message)) $errors[] = "Message cannot be empty.";

    // Save contact message to database
    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);

        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!-- BANNER -->
<section class="contact-banner">
    <div class="contact-banner-overlay">
        <h1 class="contact-banner-title">Contact Us</h1>
        <p class="contact-banner-sub">We'd love to hear from you — reach out anytime</p>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="contact-section py-5">
    <div class="container">
        <div class="row g-5">

            <!-- LEFT: CONTACT INFO -->
            <div class="col-12 col-lg-4">
                <h3 class="contact-info-title">Get In Touch</h3>
                <p class="contact-info-desc">Have a question about a course or need help? Our team is here for you.</p>

                <?php
                $contactInfo = [
                    ["icon" => "📍", "label" => "Address", "value" => "Study Adda HQ, Varanasi, India"],
                    ["icon" => "📞", "label" => "Phone",   "value" => "+91 xxxxx xxxxx"],
                    ["icon" => "📧", "label" => "Email",    "value" => "support@studyadda.com"],
                    ["icon" => "🕐", "label" => "Hours",    "value" => "Mon – Sat: 9:00 AM – 6:00 PM"],
                ];
                foreach ($contactInfo as $info) {
                    echo '
                    <div class="contact-info-card">
                        <div class="contact-info-icon">' . $info["icon"] . '</div>
                        <div>
                            <p class="contact-info-label">' . $info["label"] . '</p>
                            <p class="contact-info-value">' . $info["value"] . '</p>
                        </div>
                    </div>';
                }
                ?>

                <div class="contact-socials mt-4">
                    <a href="#" class="social-pill">📘 Facebook</a>
                    <a href="#" class="social-pill">📸 Instagram</a>
                    <a href="#" class="social-pill">▶️ YouTube</a>
                </div>
            </div>

            <!-- RIGHT: CONTACT FORM -->
            <div class="col-12 col-lg-8">
                <div class="contact-form-card">
                    <h3 class="form-title">Send Us a Message</h3>

                    <?php if ($success): ?>
                        <div class="alert-success-custom">
                            <span class="alert-icon">✅</span>
                            <div>
                                <strong>Message sent successfully!</strong>
                                <p>Thank you <?php echo htmlspecialchars($name); ?>! We'll get back to you within 24 hours.</p>
                            </div>
                        </div>
                    <?php else: ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert-error-custom">
                                <span class="alert-icon">⚠️</span>
                                <ul class="error-list">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST" class="contact-form">
                            <div class="row g-3">

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Full Name <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control custom-input"
                                        placeholder="Your full name"
                                        value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email Address <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control custom-input"
                                        placeholder="your@email.com"
                                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" class="form-control custom-input"
                                        placeholder="What is this about?"
                                        value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Message <span class="required">*</span></label>
                                    <textarea name="message" class="form-control custom-input"
                                        rows="5" placeholder="Write your message here..."
                                        required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn enroll-btn w-100">
                                        Send Message 📨
                                    </button>
                                </div>

                            </div>
                        </form>

                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>