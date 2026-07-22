<!-- ============================================
         FOOTER
    ============================================ -->
<footer class="main-footer">
    <div class="container py-5">
        <div class="row g-4">

            <!-- Brand Column -->
            <div class="col-12 col-md-4">
                <img src="images/logo-dark.png" alt="Study Adda Logo" height="45" class="mb-3">
                <p class="footer-desc">Study Adda is your complete academic solution — learn from experts, track progress, and achieve your goals.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="social-link" aria-label="Facebook">📘</a>
                    <a href="#" class="social-link" aria-label="Instagram">📸</a>
                    <a href="#" class="social-link" aria-label="YouTube">▶️</a>
                    <a href="#" class="social-link" aria-label="Twitter">🐦</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-6 col-md-2">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>

            <!-- Classes -->
            <div class="col-6 col-md-2">
                <h6 class="footer-heading">Classes</h6>
                <ul class="footer-links">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <li><a href="courses.php?class=<?php echo $i; ?>">Class <?php echo $i; ?></a></li>
                    <?php endfor; ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-12 col-md-4">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="footer-links">
                    <li>📧 support@studyadda.com</li>
                    <li>📞 +91 xxxxx xxxxx</li>
                    <li>📍 Varansi, India</li>
                </ul>
            </div>

        </div>

        <!-- Bottom Bar -->
        <hr class="footer-divider mt-4">
        <div class="row">
            <div class="col-12 text-center">
                <!-- ✅ Dynamic year using PHP — never needs manual update -->
                <p class="footer-copy">© <?php echo date("Y"); ?> Study Adda. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================
         Bootstrap JS — always at bottom of footer
         JS loads last so page renders fast
         If JS was in <head>, page would wait for it before showing
    ============================================ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>

<?php include "includes/toast.php"; ?>

</body>

</html>