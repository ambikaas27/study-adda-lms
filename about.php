<?php
// ✅ Set page title BEFORE including header
// header.php reads this variable to set the <title> tag dynamically
$pageTitle = "About Us";
include "includes/header.php";
?>

<!-- ============================================
     BANNER SECTION
============================================ -->
<section class="about-banner">
    <div class="about-banner-overlay">
        <h1 class="about-banner-title">About Us</h1>
        <p class="about-banner-sub">Empowering students across India</p>
    </div>
</section>

<!-- ============================================
     WHO WE ARE SECTION
============================================ -->
<section class="about-who py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-12 col-md-6">
                <span class="about-badge">Our Story</span>
                <h2 class="about-heading mt-2">Who We Are</h2>
                <p class="about-text">At Study Adda, we're passionate about empowering students with the knowledge and resources they need to succeed. Our mission is to provide an innovative and engaging learning environment that supports learners of all levels.</p>
                <p class="about-text">Founded on the belief that education should be accessible and effective, Study Adda combines expert tutors, interactive tools, and a supportive community to help students reach their goals.</p>
                <p class="about-text">Whether you're a school student, college aspirant, or professional looking to upskill — Study Adda is the perfect platform to unlock your potential.</p>
                <a href="courses.php" class="btn enroll-btn mt-2">Explore Courses</a>
            </div>
            <div class="col-12 col-md-6 text-center">
                <img src="images/about-us.png" alt="About Study Adda" class="img-fluid about-img rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     STATS SECTION
============================================ -->
<section class="about-stats py-5">
    <div class="container">
        <div class="row g-4 text-center">

            <?php
            // Use PHP arrays for repeated UI blocks
            $stats = [
                ["number" => "10,000+", "label" => "Students Enrolled",  "icon" => "👨‍🎓"],
                ["number" => "200+",    "label" => "Expert Instructors",  "icon" => "👨‍🏫"],
                ["number" => "500+",    "label" => "Courses Available",   "icon" => "📚"],
                ["number" => "98%",     "label" => "Student Satisfaction", "icon" => "⭐"],
            ];

            foreach ($stats as $stat) {
                echo '
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">' . $stat["icon"] . '</div>
                        <h3 class="stat-number">' . $stat["number"] . '</h3>
                        <p class="stat-label">' . $stat["label"] . '</p>
                    </div>
                </div>';
            }
            ?>

        </div>
    </div>
</section>

<!-- ============================================
     MISSION & VALUES SECTION
============================================ -->
<section class="about-values py-5">
    <div class="container">
        <h2 class="section-title text-center mb-2">Our Core Values</h2>
        <p class="text-center text-muted mb-5">What drives everything we do at Study Adda</p>

        <div class="row g-4">

            <?php
            $values = [
                [
                    "icon"  => "🎯",
                    "title" => "Excellence",
                    "desc"  => "We hold ourselves to the highest standards in every course, every tutorial, and every interaction with our students."
                ],
                [
                    "icon"  => "🌍",
                    "title" => "Accessibility",
                    "desc"  => "Education should be available to everyone. We keep our platform affordable and easy to use for all learners."
                ],
                [
                    "icon"  => "🤝",
                    "title" => "Community",
                    "desc"  => "Learning is better together. We foster a supportive community where students and instructors grow side by side."
                ],
                [
                    "icon"  => "🚀",
                    "title" => "Innovation",
                    "desc"  => "We constantly evolve our platform with new tools, features, and content to keep learning engaging and effective."
                ],
            ];

            foreach ($values as $value) {
                echo '
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="value-card text-center h-100">
                        <div class="value-icon">' . $value["icon"] . '</div>
                        <h5 class="value-title">' . $value["title"] . '</h5>
                        <p class="value-desc">' . $value["desc"] . '</p>
                    </div>
                </div>';
            }
            ?>

        </div>
    </div>
</section>

<!-- ============================================
     CTA SECTION
============================================ -->
<section class="about-cta py-5">
    <div class="container text-center">
        <h2 class="cta-title">Ready to Start Learning?</h2>
        <p class="cta-subtitle">Join thousands of students already learning on Study Adda</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
            <a href="register.php" class="btn enroll-btn">Get Started Free</a>
            <a href="contact.php" class="btn btn-outline-about">Contact Us</a>
        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>