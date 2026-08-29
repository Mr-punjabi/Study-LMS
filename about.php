<?php
// about.php - Redesigned Enterprise About Page
$page_title = "About Study Point Academy";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Banner -->
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%); color: #ffffff; padding: 90px 0; position: relative; overflow: hidden;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center;">
            <div>
                <div class="hero-pill"><i class="fa-solid fa-graduation-cap"></i> Empowering Modern Developers</div>
                <h1 style="color: #ffffff; font-size: 3rem; line-height: 1.15; margin-bottom: 20px;">
                    We Are Building The Future Of <span style="background: linear-gradient(135deg, #60a5fa, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Technical Education</span>
                </h1>
                <p style="font-size: 1.15rem; color: #cbd5e1; line-height: 1.7; margin-bottom: 30px;">
                    Study Point Academy bridges the gap between basic tutorials and enterprise software engineering through structured learning paths, interactive sandboxes, and tamper-proof verified credentials.
                </p>
                <a href="courses.php" class="btn btn-primary btn-accent"><i class="fa-solid fa-rocket"></i> Explore Courses</a>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=700&q=80" alt="Team Collaboration" style="width: 100%; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); filter: drop-shadow(0 20px 30px rgba(0,0,0,0.4));">
            </div>
        </div>
    </div>
</section>

<!-- Stats Ribbon -->
<div class="container">
    <div class="stats-bar" style="margin-top: -30px;">
        <div class="stat-item">
            <h3><i class="fa-solid fa-graduation-cap"></i> 10,000+</h3>
            <p>Active Students</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-certificate"></i> 4,500+</h3>
            <p>Certificates Awarded</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-code"></i> 250+</h3>
            <p>Interactive Lessons</p>
        </div>
        <div class="stat-item">
            <h3><i class="fa-solid fa-star"></i> 4.9 / 5.0</h3>
            <p>Average Satisfaction</p>
        </div>
    </div>
</div>

<!-- Four Core Pillars -->
<section class="py-section">
    <div class="container">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 50px;">
            <h2 class="section-title">Our Core Learning Pillars</h2>
            <p class="section-subtitle">Designed to ensure complete concept mastery from day one.</p>
        </div>

        <div class="categories-grid" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <div class="category-card" style="padding: 30px;">
                <div class="category-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--primary-blue); font-size: 1.75rem;">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <h3>Structured Pathways</h3>
                <p>Curated course modules moving systematically from fundamental syntax to complex production applications.</p>
            </div>

            <div class="category-card" style="padding: 30px;">
                <div class="category-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent-emerald); font-size: 1.75rem;">
                    <i class="fa-solid fa-laptop-code"></i>
                </div>
                <h3>Hands-On Sandboxes</h3>
                <p>Interactive code snippets and live tutorial previews allowing you to experiment directly in your browser.</p>
            </div>

            <div class="category-card" style="padding: 30px;">
                <div class="category-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 1.75rem;">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <h3>Timed Assessments</h3>
                <p>Module quizzes and mock exams with instant feedback, scoring rules, and detailed answer explanations.</p>
            </div>

            <div class="category-card" style="padding: 30px;">
                <div class="category-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--accent-cyan); font-size: 1.75rem;">
                    <i class="fa-solid fa-award"></i>
                </div>
                <h3>Public Credentials</h3>
                <p>Tamper-proof completion certificates equipped with unique verification codes for public validation.</p>
            </div>
        </div>
    </div>
</section>

<!-- Instructors Showcase Section -->
<section class="py-section" style="background: #ffffff; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="text-align: center; max-width: 700px; margin: 0 auto 50px;">
            <h2 class="section-title">Meet Our Lead Instructors</h2>
            <p class="section-subtitle">Learn directly from senior industry engineers and educators.</p>
        </div>

        <div class="courses-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            <div style="background: var(--bg-light); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; text-align: center; padding-bottom: 24px;">
                <div style="height: 220px; overflow: hidden; margin-bottom: 20px;">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=500&q=80" alt="Sarah Connor" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px;">Sarah Connor</h3>
                <p style="color: var(--primary-blue); font-weight: 600; font-size: 0.9rem; margin-bottom: 12px;">Senior Frontend Lead & CSS Specialist</p>
                <p style="color: var(--text-muted); font-size: 0.85rem; padding: 0 20px;">8+ years of engineering experience architecting enterprise UI frameworks and responsive design systems.</p>
            </div>

            <div style="background: var(--bg-light); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; text-align: center; padding-bottom: 24px;">
                <div style="height: 220px; overflow: hidden; margin-bottom: 20px;">
                    <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=500&q=80" alt="Alex Johnson" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px;">Alex Johnson</h3>
                <p style="color: var(--primary-blue); font-weight: 600; font-size: 0.9rem; margin-bottom: 12px;">Full-Stack Engineer & Author</p>
                <p style="color: var(--text-muted); font-size: 0.85rem; padding: 0 20px;">Specializes in JavaScript ES6+, PHP backend systems, and database optimization for high-traffic web applications.</p>
            </div>

            <div style="background: var(--bg-light); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; text-align: center; padding-bottom: 24px;">
                <div style="height: 220px; overflow: hidden; margin-bottom: 20px;">
                    <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=500&q=80" alt="Elena Rostova" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px;">Elena Rostova</h3>
                <p style="color: var(--primary-blue); font-weight: 600; font-size: 0.9rem; margin-bottom: 12px;">Web Accessibility & UX Director</p>
                <p style="color: var(--text-muted); font-size: 0.85rem; padding: 0 20px;">Passionate about semantic HTML5 standards, ARIA specifications, and inclusive digital product design.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
