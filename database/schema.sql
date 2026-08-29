-- Study Point Academy Enterprise LMS Database Schema

CREATE DATABASE IF NOT EXISTS `studylms_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studylms_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'instructor', 'admin') DEFAULT 'student',
  `avatar` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
  `bio` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `icon` VARCHAR(50) DEFAULT 'fa-code',
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Courses Table
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `instructor_name` VARCHAR(100) DEFAULT 'Sarah Connor',
  `rating` DECIMAL(3, 1) DEFAULT 4.9,
  `short_description` TEXT NOT NULL,
  `description` LONGTEXT NOT NULL,
  `level` ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
  `thumbnail` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80',
  `duration_minutes` INT DEFAULT 0,
  `price` DECIMAL(10, 2) DEFAULT 0.00,
  `is_featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('draft', 'published') DEFAULT 'published',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Course Modules Table
CREATE TABLE IF NOT EXISTS `course_modules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 1,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Lessons Table
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `video_url` VARCHAR(255) DEFAULT NULL,
  `duration_minutes` INT DEFAULT 15,
  `sort_order` INT DEFAULT 1,
  FOREIGN KEY (`module_id`) REFERENCES `course_modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Enrollments Table (With payment_status approval column)
CREATE TABLE IF NOT EXISTS `enrollments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `payment_status` ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
  `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `user_course_unique` (`user_id`, `course_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Lesson Progress Table
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `lesson_id` INT NOT NULL,
  `is_completed` TINYINT(1) DEFAULT 1,
  `completed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_lesson_unique` (`user_id`, `lesson_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Quizzes Table
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `duration_minutes` INT DEFAULT 15,
  `passing_score` INT DEFAULT 70,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Questions Table
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `quiz_id` INT NOT NULL,
  `question_text` TEXT NOT NULL,
  `option_a` VARCHAR(255) NOT NULL,
  `option_b` VARCHAR(255) NOT NULL,
  `option_c` VARCHAR(255) NOT NULL,
  `option_d` VARCHAR(255) NOT NULL,
  `correct_option` CHAR(1) NOT NULL,
  `explanation` TEXT DEFAULT NULL,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Quiz Attempts Table
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `quiz_id` INT NOT NULL,
  `score` INT NOT NULL,
  `total_questions` INT NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL,
  `passed` TINYINT(1) NOT NULL,
  `attempted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Certificates Table
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `course_id` INT NOT NULL,
  `certificate_code` VARCHAR(50) NOT NULL UNIQUE,
  `issued_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Standalone Tutorials Table
CREATE TABLE IF NOT EXISTS `standalone_tutorials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `category` VARCHAR(100) NOT NULL,
  `short_description` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `demo_code` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Resources Table (Downloadable Notes, PDFs, Cheatsheets)
CREATE TABLE IF NOT EXISTS `resources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `file_type` VARCHAR(50) DEFAULT 'PDF',
  `file_path` VARCHAR(255) DEFAULT '#',
  `description` TEXT DEFAULT NULL,
  `download_count` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. Blog Posts Table
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT NOT NULL,
  `content` LONGTEXT NOT NULL,
  `category` VARCHAR(100) DEFAULT 'General',
  `author_id` INT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED DATA

-- Users (Default Password: password123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `bio`) VALUES
(1, 'System Administrator', 'admin@studypoint.com', '$2y$10$3YPPrMmM2OQ5JC9/iSq87u12jNXrCfjJlatQSXbMClD0vLrhZw9qO', 'admin', 'Academy Enterprise Manager & Content Director.'),
(2, 'Alex Johnson', 'alex@studypoint.com', '$2y$10$3YPPrMmM2OQ5JC9/iSq87u12jNXrCfjJlatQSXbMClD0vLrhZw9qO', 'student', 'Passionate Web Development student learning HTML, CSS & JavaScript.'),
(3, 'Sarah Connor', 'sarah@studypoint.com', '$2y$10$3YPPrMmM2OQ5JC9/iSq87u12jNXrCfjJlatQSXbMClD0vLrhZw9qO', 'instructor', 'Senior Frontend Engineer with 8+ years experience teaching web development.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'HTML5 & Semantics', 'html5-semantics', 'fa-brands fa-html5', 'Master structural elements, semantic tags, forms, and modern web markup.'),
(2, 'CSS3 & Modern Styling', 'css3-modern-styling', 'fa-brands fa-css3-alt', 'Learn Flexbox, Grid, CSS variables, animations, and responsive UI layouts.'),
(3, 'JavaScript & ES6+', 'javascript-es6', 'fa-brands fa-js', 'Core JavaScript, DOM manipulation, Async/Await, and modern ES6+ features.'),
(4, 'Programming Fundamentals', 'programming-fundamentals', 'fa-solid fa-code', 'Logic building, algorithms, data structures, and problem solving basics.'),
(5, 'Exam & Practice Tests', 'exam-practice-tests', 'fa-solid fa-file-pen', 'Comprehensive mock exams, quizzes, and self-assessment question banks.')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Courses (Paid & Free with Ratings & Instructors)
INSERT INTO `courses` (`id`, `category_id`, `title`, `slug`, `instructor_name`, `rating`, `short_description`, `description`, `level`, `thumbnail`, `duration_minutes`, `price`, `is_featured`, `status`) VALUES
(1, 1, 'HTML5 Foundations for Beginners', 'html5-foundations-for-beginners', 'Alex Johnson', 4.8, 'Learn to structure web pages from scratch using semantic HTML5 elements, forms, and accessible markup.', 'This comprehensive course guides you step-by-step through modern HTML5 web development. You will build real-world web pages, master semantic structure, embed media, and write accessible code suited for enterprise standards.', 'Beginner', 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?auto=format&fit=crop&w=600&q=80', 120, 0.00, 1, 'published'),
(2, 2, 'CSS3 Flexbox & Grid Masterclass', 'css3-flexbox-grid-masterclass', 'Sarah Connor', 4.9, 'Design highly responsive, beautiful website layouts using modern CSS Flexbox, Grid, and CSS Variables.', 'Master the art of CSS layout design! Learn how to create mobile-first responsive web applications, sleek cards, hero sections, and fluid grids with zero framework bloat.', 'Intermediate', 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=600&q=80', 180, 29.99, 1, 'published'),
(3, 3, 'Complete Modern JavaScript ES6+', 'complete-modern-javascript-es6', 'Sarah Connor', 5.0, 'Master core JS, DOM manipulation, events, Promises, Async/Await, and API integration.', 'JavaScript is the engine of modern web development. Learn variables, data types, functions, arrow functions, DOM events, fetch API, local storage, and ES6+ modules.', 'Intermediate', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80', 300, 49.99, 1, 'published'),
(4, 4, 'Full-Stack PHP & MySQL Essentials', 'full-stack-php-mysql-essentials', 'System Administrator', 4.7, 'Build dynamic web applications, user authentication systems, and database-driven portals with PHP and MySQL.', 'Unlock backend web development with PHP and MySQL! Learn PDO security, session control, user login systems, CRUD operations, and architecture for enterprise applications.', 'Intermediate', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=600&q=80', 240, 39.99, 1, 'published')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Modules for Course 1 (HTML5)
INSERT INTO `course_modules` (`id`, `course_id`, `title`, `sort_order`) VALUES
(1, 1, 'Module 1: Introduction to Web Structure', 1),
(2, 1, 'Module 2: HTML5 Semantic Elements & Forms', 2);

-- Lessons for Course 1
INSERT INTO `lessons` (`id`, `module_id`, `title`, `slug`, `content`, `video_url`, `duration_minutes`, `sort_order`) VALUES
(1, 1, 'Welcome & Basic HTML Skeleton', 'welcome-basic-html-skeleton', '<h3>Introduction to HTML5</h3><p>HTML (HyperText Markup Language) is the standard markup language for documents designed to be displayed in a web browser.</p><pre><code>&lt;!DOCTYPE html&gt;\n&lt;html lang="en"&gt;\n  &lt;head&gt;\n    &lt;title&gt;My First Page&lt;/title&gt;\n  &lt;/head&gt;\n  &lt;body&gt;\n    &lt;h1&gt;Hello World!&lt;/h1&gt;\n  &lt;/body&gt;\n&lt;/html&gt;</code></pre><p>Every webpage begins with this standard declaration and structural element layout.</p>', 'https://www.youtube.com/embed/gT0LhB-Zp50', 15, 1),
(2, 1, 'Working with Text, Headings & Lists', 'working-with-text-headings-lists', '<h3>Structuring Content with Headings</h3><p>Use &lt;h1&gt; through &lt;h6&gt; to create clear document hierarchies. For lists, use &lt;ul&gt; for unordered lists and &lt;ol&gt; for ordered lists.</p>', 'https://www.youtube.com/embed/gT0LhB-Zp50', 20, 2),
(3, 2, 'Semantic Layout: Header, Nav, Main & Footer', 'semantic-layout-header-nav-main-footer', '<h3>Why Semantic HTML Matters</h3><p>Semantic elements such as &lt;header&gt;, &lt;nav&gt;, &lt;main&gt;, &lt;article&gt;, &lt;aside&gt;, and &lt;footer&gt; clearly describe their meaning to both the browser and the developer, boosting SEO and accessibility.</p>', 'https://www.youtube.com/embed/gT0LhB-Zp50', 25, 3),
(4, 2, 'Building Interactive Web Forms', 'building-interactive-web-forms', '<h3>HTML5 Forms</h3><p>Learn how to capture user input using input types like email, text, password, number, submit, and select dropdowns.</p>', 'https://www.youtube.com/embed/gT0LhB-Zp50', 30, 4);

-- Standalone Preview Tutorials
INSERT INTO `standalone_tutorials` (`id`, `title`, `slug`, `category`, `short_description`, `content`, `demo_code`, `image`) VALUES
(1, 'HTML5 Interactive Form Controls Demo', 'html5-interactive-form-controls-demo', 'HTML5', 'Experience interactive form controls including color pickers, date selectors, and range sliders before enrolling in HTML5 Foundations.', '<p>HTML5 introduced native input types that eliminate the need for heavy external JavaScript plugins. In this quick interactive preview, test out modern input elements and see how modern browsers render them natively.</p>', '<form style="padding:15px; background:#f8fafc; border-radius:8px; border:1px solid #cbd5e1;">\n  <label style="display:block; margin-bottom:8px;">Choose Color: <input type="color" value="#2563eb"></label>\n  <label style="display:block; margin-bottom:8px;">Select Date: <input type="date"></label>\n  <label style="display:block; margin-bottom:8px;">Volume Level: <input type="range" min="0" max="100"></label>\n</form>', 'https://images.unsplash.com/photo-1542831371-29b0f74f9713?auto=format&fit=crop&w=600&q=80'),
(2, 'CSS Flexbox Alignment Live Playground Demo', 'css-flexbox-alignment-playground-demo', 'CSS3', 'Interactive preview of CSS Flexbox justify-content and align-items properties.', '<p>CSS Flexbox provides a clean way to distribute space among items in a container. Try out flexbox properties in real-time below.</p>', '<div style="display:flex; justify-content:space-between; align-items:center; background:#1e293b; color:#fff; padding:20px; border-radius:8px;">\n  <div style="background:#2563eb; padding:10px 15px; border-radius:4px;">Item 1</div>\n  <div style="background:#10b981; padding:10px 15px; border-radius:4px;">Item 2</div>\n  <div style="background:#06b6d4; padding:10px 15px; border-radius:4px;">Item 3</div>\n</div>', 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=600&q=80'),
(3, 'JavaScript ES6 Arrow Functions & Fetch Demo', 'javascript-es6-arrow-functions-demo', 'JavaScript', 'Quick interactive demonstration of ES6 arrow syntax and asynchronous fetch calls.', '<p>Arrow functions simplify function expressions in JavaScript. Here is a comparison between ES5 and ES6 syntax:</p><pre><code>// ES5\nfunction add(a, b) { return a + b; }\n\n// ES6 Arrow Function\nconst add = (a, b) => a + b;</code></pre>', NULL, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80');

-- Quizzes
INSERT INTO `quizzes` (`id`, `course_id`, `title`, `description`, `duration_minutes`, `passing_score`) VALUES
(1, 1, 'HTML5 Fundamentals Knowledge Assessment', 'Test your understanding of semantic tags, document structure, and HTML forms.', 15, 70),
(2, 2, 'CSS3 Flexbox & Layout Quiz', 'Evaluate your skills in CSS styling, box model rules, and flexbox layout properties.', 15, 70);

-- Questions for Quiz 1
INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`) VALUES
(1, 1, 'Which tag is used to specify the main content area of a document in HTML5?', '<content>', '<main>', '<body-content>', '<section-main>', 'B', 'The <main> tag represents the dominant content of the <body> of a document.'),
(2, 1, 'Which HTML5 element is best suited for wrapping independent, self-contained articles or blog posts?', '<section>', '<div>', '<article>', '<aside>', 'C', 'The <article> tag is intended for reusable, independent content snippets such as blog posts or news items.'),
(3, 1, 'What is the correct HTML element for inserting a line break?', '<break>', '<br>', '<lb>', '<newline>', 'B', 'The <br> tag inserts a single line break in HTML content.');

-- Downloadable Resources
INSERT INTO `resources` (`id`, `title`, `category`, `file_type`, `description`, `download_count`) VALUES
(1, 'HTML5 Cheatsheet & Semantic Guide', 'HTML', 'PDF', 'A concise 2-page quick reference sheet for all modern HTML5 semantic elements and form attributes.', 142),
(2, 'CSS3 Flexbox & Grid Quick Reference', 'CSS', 'PDF', 'Visual guide and code snippets for CSS Flexbox properties, alignment rules, and Grid template areas.', 98),
(3, 'JavaScript ES6+ Syntax & Methods Guide', 'JavaScript', 'PDF', 'Summary of arrow functions, array helper methods (.map, .filter, .reduce), destructuring, and async/await.', 215);

-- Blog Posts
INSERT INTO `blog_posts` (`id`, `title`, `slug`, `excerpt`, `content`, `category`, `author_id`, `image`) VALUES
(1, 'Top 10 Modern HTML5 & CSS3 Best Practices for 2026', 'top-10-modern-html5-css3-best-practices', 'Discover essential coding guidelines for clean semantic markup, mobile-first CSS design, and web accessibility.', '<p>Building modern web applications requires adherence to clean architecture standards. In this article, we cover 10 actionable recommendations including CSS variables, semantic tags, fluid typography, and performance optimizations.</p><p>First, always write semantic HTML elements like &lt;header&gt;, &lt;nav&gt;, &lt;main&gt;, and &lt;footer&gt;. Second, leverage CSS variables for dark mode and design system consistency. Third, adopt mobile-first CSS Grid and Flexbox layouts.</p>', 'Web Development', 3, 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=600&q=80'),
(2, 'How to Learn JavaScript Fast: A Structured Roadmap', 'how-to-learn-javascript-fast-roadmap', 'A step-by-step roadmap for mastering vanilla JS fundamentals before diving into heavy frameworks.', '<p>Many students jump into complex frontend frameworks too quickly. Mastering core DOM manipulation, events, array methods, and async operations in JavaScript will save you hundreds of hours of debugging later.</p><p>Start with basic data types and functions. Move on to DOM selection, event listeners, array methods (.map, .filter, .reduce), and promises with async/await.</p>', 'Career Advice', 3, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80');
