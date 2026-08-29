// assets/js/main.js - Study Point Academy Enterprise LMS Client Script

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Menu Drawer Toggle
    const menuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function () {
            navMenu.classList.toggle('active');
        });
    }

    // 2. Global Search Keyword Click Handler
    const searchBadges = document.querySelectorAll('.tag-badge');
    const searchInput = document.getElementById('globalSearchInput');

    searchBadges.forEach(badge => {
        badge.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = this.innerText;
                const searchForm = searchInput.closest('form');
                if (searchForm) searchForm.submit();
            }
        });
    });

    // 3. Lesson Progress AJAX Tracker
    const completeBtn = document.getElementById('markLessonCompleteBtn');
    if (completeBtn) {
        completeBtn.addEventListener('click', function () {
            const lessonId = this.getAttribute('data-lesson-id');
            const courseId = this.getAttribute('data-course-id');

            fetch('api/complete_lesson.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `lesson_id=${lessonId}&course_id=${courseId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-accent');
                    this.innerHTML = '<i class="fa-solid fa-circle-check"></i> Completed';
                    
                    const progressBar = document.getElementById('courseProgressBar');
                    if (progressBar) {
                        progressBar.style.width = data.progress + '%';
                    }
                    const progressText = document.getElementById('courseProgressText');
                    if (progressText) {
                        progressText.innerText = data.progress + '%';
                    }
                }
            })
            .catch(err => console.error('Error completing lesson:', err));
        });
    }

    // 4. Custom Visual Quiz Exit Modal & Timer
    const quizForm = document.getElementById('quizForm');
    const customExitModal = document.getElementById('customQuizExitModal');
    const exitQuizBtn = document.getElementById('exitQuizBtn');
    const confirmStayQuizBtn = document.getElementById('confirmStayQuizBtn');
    const confirmExitQuizBtn = document.getElementById('confirmExitQuizBtn');
    
    let isQuizSubmitted = false;
    let pendingNavigationUrl = 'practice.php';

    if (quizForm) {
        quizForm.addEventListener('submit', function () {
            isQuizSubmitted = true;
        });

        // Trigger custom modal on Exit Quiz button click
        if (exitQuizBtn && customExitModal) {
            exitQuizBtn.addEventListener('click', function (e) {
                e.preventDefault();
                pendingNavigationUrl = 'practice.php';
                customExitModal.style.display = 'flex';
            });
        }

        // Intercept header/footer navigation links during active quiz
        const navLinks = document.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                if (!isQuizSubmitted && this.getAttribute('href') && !this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    pendingNavigationUrl = this.getAttribute('href');
                    if (customExitModal) {
                        customExitModal.style.display = 'flex';
                    }
                }
            });
        });

        if (confirmStayQuizBtn && customExitModal) {
            confirmStayQuizBtn.addEventListener('click', function () {
                customExitModal.style.display = 'none';
            });
        }

        if (confirmExitQuizBtn) {
            confirmExitQuizBtn.addEventListener('click', function () {
                isQuizSubmitted = true;
                window.location.href = pendingNavigationUrl;
            });
        }
    }

    // Quiz Timer
    const timerElement = document.getElementById('quizTimer');
    if (timerElement) {
        let durationMinutes = parseInt(timerElement.getAttribute('data-duration') || '15', 10);
        let timeRemaining = durationMinutes * 60;

        const timerInterval = setInterval(function () {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;

            timerElement.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                alert('Time is up! Your quiz answers will be submitted automatically.');
                isQuizSubmitted = true;
                if (quizForm) quizForm.submit();
            }
            timeRemaining--;
        }, 1000);
    }
});
