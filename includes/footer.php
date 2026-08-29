<?php
// includes/footer.php - Global Enterprise Footer
?>
    </main> <!-- End .main-content -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="<?php echo BASE_URL; ?>" class="brand-logo">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>STUDY POINT ACADEMY</span>
                    </a>
                    <p>Production & Enterprise-Level Learning Platform designed to provide structured course journeys, assessments, notes, and public verified certificates.</p>
                </div>
                <div class="footer-col">
                    <h4>Explore Platform</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>courses.php">All Courses</a></li>
                        <li><a href="<?php echo BASE_URL; ?>tutorials.php">Free Tutorials</a></li>
                        <li><a href="<?php echo BASE_URL; ?>notes.php">Downloadable Notes</a></li>
                        <li><a href="<?php echo BASE_URL; ?>practice.php">Practice & Quizzes</a></li>
                        <li><a href="<?php echo BASE_URL; ?>certificates.php">Verify Certificate</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Top Categories</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>courses.php?cat=html5-semantics">HTML5 & Semantics</a></li>
                        <li><a href="<?php echo BASE_URL; ?>courses.php?cat=css3-modern-styling">CSS3 & Grid Layouts</a></li>
                        <li><a href="<?php echo BASE_URL; ?>courses.php?cat=javascript-es6">JavaScript ES6+</a></li>
                        <li><a href="<?php echo BASE_URL; ?>courses.php?cat=programming-fundamentals">Programming Basics</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Support & Legal</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>about.php">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>contact.php">Contact & Support</a></li>
                        <li><a href="<?php echo BASE_URL; ?>faq.php">Frequently Asked Questions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>privacy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo BASE_URL; ?>terms.php">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Study Point Academy. All rights reserved. Built with HTML, PHP, CSS & JavaScript.</p>
                <div style="display:flex; gap:16px;">
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-github"></i></a>
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" style="color:#94a3b8;"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
