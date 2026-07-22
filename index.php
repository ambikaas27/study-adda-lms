<?php
include "includes/header.php";
?>

<!-- ============================================
     SECTION 1: HERO CAROUSEL
     Bootstrap carousel with 3 banner images
     data-bs-interval controls auto-slide speed (ms)
============================================ -->
<section class="hero-section">
    <div id="heroCarousel" class="carousel carousel-dark slide hero-carousel" data-bs-ride="carousel">

        <div class="carousel-indicators hero-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="5000">
                <div class="hero-img-wrap">
                    <img src="images/banner-1.png" class="d-block w-100 hero-img" alt="Study Adda - Learn Anytime Anywhere">
                </div>
                <div class="hero-caption">
                    <span class="hero-eyebrow">Study Adda</span>
                    <h2 class="hero-title">Learn Anytime, Anywhere</h2>
                    <p class="hero-text">Flexible courses built around your schedule, not the other way around.</p>
                    <a href="#" class="hero-btn">Start Learning</a>
                </div>
            </div>

            <div class="carousel-item" data-bs-interval="5000">
                <div class="hero-img-wrap">
                    <img src="images/banner-2.png" class="d-block w-100 hero-img" alt="Expert Tutors at Study Adda">
                </div>
                <div class="hero-caption">
                    <span class="hero-eyebrow">Meet the Mentors</span>
                    <h2 class="hero-title">Expert Tutors, Real Guidance</h2>
                    <p class="hero-text">Learn from instructors who've actually worked in the field.</p>
                    <a href="#" class="hero-btn">Meet Our Tutors</a>
                </div>
            </div>

            <div class="carousel-item" data-bs-interval="5000">
                <div class="hero-img-wrap">
                    <img src="images/banner-3.png" class="d-block w-100 hero-img" alt="Get Certified with Study Adda">
                </div>
                <div class="hero-caption">
                    <span class="hero-eyebrow">Proof of Progress</span>
                    <h2 class="hero-title">Get Certified. Get Ahead.</h2>
                    <p class="hero-text">Industry-recognized certificates that actually open doors.</p>
                    <a href="#" class="hero-btn">View Certifications</a>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon hero-nav-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon hero-nav-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- ============================================
     SECTION 2: WELCOME / CTA SECTION
============================================ -->
<section class="welcome-section">
    <div class="welcome-bg">
        <span class="welcome-shape shape-1"></span>
        <span class="welcome-shape shape-2"></span>
        <span class="welcome-shape shape-3"></span>

        <div class="container py-5 position-relative">
            <div class="row justify-content-center text-center">
                <div class="col-12 col-lg-8">
                    <span class="welcome-badge">🎓 10,000+ Students Learning Right Now</span>
                    <h1 class="welcome-title">Everything You Need <br class="d-none d-md-block">to Learn, Grow & Succeed</h1>
                    <h4 class="welcome-subtitle">Join the ultimate platform to learn, grow, and achieve your academic goals with expert support — anytime, anywhere.</h4>
                    <div class="welcome-cta-group mt-4">
                        <a href="register.php" class="btn enroll-btn">Enroll Now</a>
                        <a href="courses.php" class="btn explore-btn">Explore Courses</a>
                    </div>
                </div>
            </div>

            <div class="row welcome-stats justify-content-center mt-5 pt-3">
                <div class="col-6 col-md-3 stat-item">
                    <h3 class="stat-number">10K+</h3>
                    <p class="stat-label">Active Students</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3 class="stat-number">500+</h3>
                    <p class="stat-label">Courses Available</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3 class="stat-number">150+</h3>
                    <p class="stat-label">Expert Tutors</p>
                </div>
                <div class="col-6 col-md-3 stat-item">
                    <h3 class="stat-number">4.8★</h3>
                    <p class="stat-label">Average Rating</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 3: FEATURES
============================================ -->
<section class="features-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="section-eyebrow">What You Get</span>
            <h2 class="section-title">Why Choose Study Adda?</h2>
        </div>
        <?php
        $features = [
            ["icon" => "🕐", "title" => "Learn Anytime",     "desc" => "Study at your own pace, anytime anywhere"],
            ["icon" => "👨‍🏫", "title" => "Expert Tutors",     "desc" => "Learn from experienced educators"],
            ["icon" => "🏆", "title" => "Get Certificates",  "desc" => "Earn certificates on completion"],
            ["icon" => "📚", "title" => "Easy Courses",      "desc" => "Simple and structured course content"],
            ["icon" => "🎥", "title" => "Live Classes",      "desc" => "Attend live interactive sessions"],
            ["icon" => "📊", "title" => "Track Progress",    "desc" => "Monitor your learning journey"],
        ];
        ?>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
            <?php foreach ($features as $feature): ?>
                <div class="col">
                    <div class="feature-card text-center h-100">
                        <div class="feature-icon-wrap">
                            <span class="feature-emoji"><?php echo $feature["icon"]; ?></span>
                        </div>
                        <h6 class="feature-title"><?php echo $feature["title"]; ?></h6>
                        <p class="feature-desc"><?php echo $feature["desc"]; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 4: TEACHER REGISTRATION
============================================ -->
<section class="teacher-section">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-12 col-md-5">
                <div class="teacher-img-wrap">
                    <span class="teacher-img-shape"></span>
                    <img src="images/teachers.jpg" class="img-fluid teacher-img" alt="Teachers at Study Adda">
                    <div class="teacher-stat-card">
                        <span class="teacher-stat-number">150+</span>
                        <span class="teacher-stat-label">Active Instructors</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-7">
                <span class="badge-intro">Introducing</span>
                <h3 class="teacher-title">Study Adda — Teacher's Registration</h3>
                <p class="teacher-desc">Are you an expert in your subject? Join Study Adda as an instructor and help thousands of students achieve their academic goals.</p>
                <ul class="teacher-list">
                    <li>
                        <span class="teacher-list-icon">✓</span>
                        <div>
                            <strong>Create and manage your own courses</strong>
                            <p>Full control over content, pricing, and pace</p>
                        </div>
                    </li>
                    <li>
                        <span class="teacher-list-icon">✓</span>
                        <div>
                            <strong>Reach students across the country</strong>
                            <p>Your course, available to thousands nationwide</p>
                        </div>
                    </li>
                    <li>
                        <span class="teacher-list-icon">✓</span>
                        <div>
                            <strong>Earn by sharing your knowledge</strong>
                            <p>Get paid for every student who enrolls</p>
                        </div>
                    </li>
                </ul>
                <a href="register.php?role=instructor" class="btn enroll-btn">Signup as Instructor</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 5: CLASS SELECTOR
============================================ -->
<section class="class-section">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="section-eyebrow">Get Started</span>
            <h2 class="section-title">Select Your Class</h2>
            <p class="section-subtitle">Choose your class and explore available courses</p>
        </div>

        <div class="row g-3 justify-content-center">
            <?php
            for ($i = 1; $i <= 12; $i++) {
                echo '
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="courses.php?class=' . $i . '" class="text-decoration-none class-card-link">
                        <div class="class-card text-center">
                            <span class="class-number">' . $i . '</span>
                            <div class="class-icon">📚</div>
                            <h5 class="class-title">Class ' . $i . '</h5>
                        </div>
                    </a>
                </div>';
            }
            ?>
        </div>

        <div class="text-center mt-5">
            <a href="courses.php" class="btn enroll-btn">View All Courses</a>
        </div>
    </div>
</section>

<?php
include "includes/footer.php";
?>