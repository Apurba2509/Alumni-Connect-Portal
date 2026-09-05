# Complete Technical Implementation Plan: Alumni Connect Portal

A comprehensive, production-grade architectural and implementation blueprint for the **Alumni Connect Portal**, built on **PHP 8.x** and **MySQL (XAMPP)** with modern Vanilla CSS and JavaScript.

---

## 1. System Architecture & Tech Stack Specifications

### 1.1 Technology Stack
* **Runtime / Backend**: PHP 8.2+ (Procedural / Modular OOP, PDO database driver).
* **Database**: MySQL 8.0+ (Engine: `InnoDB`, Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`).
* **Frontend**: Semantic HTML5, Vanilla CSS3 (Custom Design Tokens, Flexbox/Grid, Glassmorphism, CSS Variables, no external CSS frameworks required), and Vanilla ES6 JavaScript (Fetch API, DOM manipulation, Modal controllers).
* **Environment**: Local Apache & MySQL via XAMPP (`http://localhost/Clg%20Project/Alumni-Connect-Portal/`).
* **Storage**: Local filesystem under `assets/uploads/` for profile pictures, event banners, and student resume PDFs (with mime-type verification and file renaming).

### 1.2 Security Architecture
1. **Database Access**: 100% Parameterized queries using PHP PDO prepared statements (`$stmt->execute([$param])`) to eliminate SQL injection.
2. **Password Security**: Strong one-way hashing with `password_hash($password, PASSWORD_BCRYPT)` and verification via `password_verify()`.
3. **Cross-Site Scripting (XSS)**: Strict output escaping via a global helper `e($data)` utilizing `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')`.
4. **Cross-Site Request Forgery (CSRF)**: Cryptographically secure random tokens (`bin2hex(random_bytes(32))`) stored in `$_SESSION` and validated on every `POST` request.
5. **Role-Based Access Control (RBAC)**: Centralized middleware (`includes/auth_check.php`) enforcing permissions:
   * **Guest**: Public landing page, Login, Registration, Public events.
   * **Student**: View directory, Apply for jobs/referrals, Request mentorship, RSVP to events, Forum.
   * **Alumni**: Post jobs/referrals, Manage applications, Set mentorship slots & accept sessions, Network.
   * **Admin**: Verify alumni accounts, Moderate jobs/events, Platform analytics.
6. **File Upload Hardening**:
   * Whitelist file extensions (`.jpg`, `.jpeg`, `.png`, `.webp`, `.pdf`).
   * Verify MIME types via `mime_content_type()`.
   * Limit file sizes (Max 2MB for avatars, Max 5MB for resumes).
   * Rename all files to unique UUIDs/hashes (`bin2hex(random_bytes(16)) . '.' . $ext`) to prevent path traversal and script execution exploits.

---

## 2. Exhaustive Database Schema Design

The database name is **`alumni-connect-portal`** (or `alumni_portal`). All tables will use `InnoDB` for foreign key integrity.

### Table 1: `users` (Core Identity & Auth)
```sql
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'alumni', 'admin') NOT NULL DEFAULT 'student',
    status ENUM('pending', 'active', 'rejected', 'suspended') NOT NULL DEFAULT 'active',
    avatar VARCHAR(255) DEFAULT 'default_avatar.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 2: `alumni_profiles` (Extended Alumni Details)
```sql
CREATE TABLE alumni_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    graduation_year INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    current_company VARCHAR(150) NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    industry VARCHAR(100) DEFAULT NULL,
    location VARCHAR(150) DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    skills TEXT DEFAULT NULL, -- Comma-separated or JSON list
    is_mentor_available TINYINT(1) DEFAULT 0,
    mentorship_topics VARCHAR(255) DEFAULT NULL,
    verification_document VARCHAR(255) DEFAULT NULL, -- Degree cert or ID card upload
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company (current_company),
    INDEX idx_grad_year (graduation_year),
    INDEX idx_dept (department),
    INDEX idx_mentor (is_mentor_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 3: `student_profiles` (Extended Student Details)
```sql
CREATE TABLE student_profiles (
    user_id INT UNSIGNED PRIMARY KEY,
    enrollment_number VARCHAR(50) NOT NULL UNIQUE,
    current_year INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    skills TEXT DEFAULT NULL,
    resume_path VARCHAR(255) DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    github_url VARCHAR(255) DEFAULT NULL,
    career_interests TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 4: `jobs` (Job & Referral Postings)
```sql
CREATE TABLE jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alumni_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(150) NOT NULL,
    location VARCHAR(100) NOT NULL,
    job_type ENUM('full_time', 'part_time', 'internship', 'referral') NOT NULL DEFAULT 'full_time',
    workplace_type ENUM('on_site', 'hybrid', 'remote') NOT NULL DEFAULT 'on_site',
    experience_level VARCHAR(50) DEFAULT 'Entry-level',
    salary_range VARCHAR(100) DEFAULT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    external_apply_url VARCHAR(255) DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumni_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_job_type (job_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 5: `job_applications` (Student Applications)
```sql
CREATE TABLE job_applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    cover_note TEXT DEFAULT NULL,
    status ENUM('submitted', 'under_review', 'referred', 'shortlisted', 'rejected') DEFAULT 'submitted',
    feedback TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_job_student (job_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 6: `mentorship_requests` (1-on-1 Mentorship Booking)
```sql
CREATE TABLE mentorship_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    alumni_id INT UNSIGNED NOT NULL,
    topic VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    preferred_date DATE NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    meeting_link VARCHAR(255) DEFAULT NULL,
    response_note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alumni_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 7: `events` (Webinars, Reunions & Workshops)
```sql
CREATE TABLE events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    event_type ENUM('webinar', 'reunion', 'workshop', 'conference') NOT NULL,
    banner_image VARCHAR(255) DEFAULT NULL,
    max_capacity INT UNSIGNED DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 8: `event_rsvps` (Event Registrations)
```sql
CREATE TABLE event_rsvps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rsvp_status ENUM('attending', 'cancelled') DEFAULT 'attending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_event_user (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Table 9: `forum_posts` & `forum_replies` (Ask-Alumni Q&A Community)
```sql
CREATE TABLE forum_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category ENUM('Career Advice', 'Higher Studies', 'Interview Prep', 'Campus Life', 'General') NOT NULL,
    views_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE forum_replies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    is_solution TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 3. Directory Layout & Modular Structure

```text
c:\xampp\htdocs\Clg Project\Alumni-Connect-Portal\
├── config/
│   ├── database.php            # PDO singleton database connection
│   └── config.php              # Global environment, base URL, session config
├── includes/
│   ├── header.php              # Global HTML head, topbar, navigation
│   ├── footer.php              # Global footer, closing tags, shared scripts
│   ├── auth_check.php          # Session validation & role restriction guards
│   ├── functions.php           # Sanitization, CSRF token helpers, alerts, dates
│   └── nav.php                 # Dynamic role-based navigation menu
├── assets/
│   ├── css/
│   │   ├── variables.css       # Color palette, spacing, typography, glassmorphism
│   │   ├── style.css           # Base styles, navbar, footer, cards, forms, buttons
│   │   ├── directory.css       # Alumni search grid, filter sidebar, profile cards
│   │   ├── jobs.css            # Job board layout, tags, application modal
│   │   └── dashboard.css       # User dashboard, statistics grid, status badges
│   ├── js/
│   │   ├── main.js             # Mobile nav toggle, auto-dismiss alerts, modals
│   │   └── filters.js          # Live AJAX search/filtering for alumni & jobs
│   └── uploads/
│       ├── avatars/            # User profile photos
│       ├── resumes/            # Student uploaded PDF resumes
│       └── banners/            # Event banner images
├── auth/
│   ├── login.php               # Unified login handler & UI
│   ├── register.php            # Role-selected registration (Student vs Alumni)
│   └── logout.php              # Safe session destruction
├── profile/
│   ├── view.php                # Public profile view (Alumni or Student)
│   ├── edit.php                # Profile update & avatar upload
│   └── resume.php              # Secure resume download/view handler
├── alumni/
│   ├── directory.php           # Alumni directory with multi-filter search
│   └── search-api.php          # JSON API for async live filtering
├── jobs/
│   ├── index.php               # Browse all active jobs & referral offers
│   ├── create.php              # Post a job/referral (Alumni only)
│   ├── view.php                # Job detail page
│   ├── apply.php               # Student application processing
│   └── manage.php              # Alumni dashboard: view applicants & change status
├── mentorship/
│   ├── index.php               # Browse mentors & book session
│   ├── request.php             # Session booking form modal/page
│   └── requests.php            # Mentorship dashboard for Alumni and Student
├── events/
│   ├── index.php               # Campus events, webinars & reunions
│   ├── create.php              # Create event (Admin & verified Alumni)
│   └── rsvp.php                # RSVP toggle action handler
├── forum/
│   ├── index.php               # Discussion board post list
│   ├── view.php                # View thread & submit reply
│   └── create.php              # Create new discussion question
├── admin/
│   ├── index.php               # Admin overview & analytics dashboard
│   ├── verify-alumni.php       # Pending alumni approvals (Accept/Reject)
│   ├── users.php               # User directory & account status control
│   └── jobs.php                # Job moderation
├── sql/
│   ├── schema.sql              # Master table creation script
│   └── seed.sql                # Rich demo data (15+ alumni, jobs, events)
└── index.php                   # Landing page (hero, highlights, stats, CTA)
```

---

## 4. Detailed Step-by-Step Implementation Sequence

### Phase 1: Core Configuration & Database Foundation
1. **`config/database.php`**:
   * Implement a robust PDO connection:
     ```php
     $host = '127.0.0.1';
     $db   = 'alumni-connect-portal';
     $user = 'root';
     $pass = '';
     $charset = 'utf8mb4';
     $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
     $options = [
         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES   => false,
     ];
     ```
2. **`config/config.php`**:
   * Define `BASE_URL`, `SITE_NAME`, path constants, and start session with secure cookie parameters (`session_start()`).
3. **`includes/functions.php`**:
   * Security functions: `e($string)` for output escaping, `generate_csrf_token()`, `verify_csrf_token()`.
   * Flash messaging system: `set_flash($type, $message)` and `display_flash()`.
   * Formatting helpers: time ago / human dates, company badges, file upload handler.
4. **Database Execution**:
   * Create all 9 tables in the `alumni-connect-portal` database.
   * Populate `sql/seed.sql` with sample verified alumni working at top companies (Google, Microsoft, Amazon, Infosys, TCS), sample jobs, and upcoming events to make demoing effortless.

---

### Phase 2: Authentication & Role Authorization Engine
1. **Registration Flow (`auth/register.php`)**:
   * Step 1: User selects role (`student` or `alumni`).
   * Step 2: Form displays role-tailored fields:
     * *Student*: Name, College Email, Password, Enrollment Number, Branch, Graduation Year. Status is automatically `active`.
     * *Alumni*: Name, Email, Password, Graduation Year, Degree, Department, Current Company, Job Title, LinkedIn URL. Status is set to `pending` (requiring Admin approval).
2. **Login Flow (`auth/login.php`)**:
   * Unified login form (Email & Password).
   * Verifies password hash.
   * Checks account status: If alumni account is `pending`, displays: *"Your account is pending verification by the college administration. You will receive access once approved."*
   * Saves user object into `$_SESSION['user'] = [...]`.
3. **Session & Role Guards (`includes/auth_check.php`)**:
   * Helper functions: `is_logged_in()`, `current_user()`, `require_login()`, `require_role('admin')`, `require_role('alumni')`.
   * Redirects unauthorized users with descriptive flash alerts.

---

### Phase 3: Modern Design System & Base Templates
1. **`assets/css/variables.css`**:
   * Primary Indigo (`#4f46e5`), Emerald Accent (`#10b981`), Amber Warning (`#f59e0b`), Slate Dark Palette (`#0f172a`, `#1e293b`).
   * Subtle glassmorphism gradients, card shadows, rounded border-radius tokens.
2. **`includes/header.php` & `includes/footer.php`**:
   * Responsive navigation bar: Logo, dynamic navigation links based on user role (Directory, Jobs, Mentorship, Events, Forum, Admin Panel).
   * User dropdown menu (My Profile, My Applications / My Postings, Logout).
   * Mobile hamburger menu.

---

### Phase 4: Modern Landing Page (`index.php`)
1. **Hero Section**:
   * Compelling headline: *"Bridging Campus & Careers: Connect with Our Global Alumni Network."*
   * Call to action: "Find a Mentor", "Explore Job Referrals", "Join as Alumni".
2. **Live Stats Banner**:
   * Dynamic counters pulled from the DB: Total Alumni, Companies Represented, Job Referrals Posted, Mentorship Sessions Completed.
3. **Spotlight Carousels / Grids**:
   * "Distinguished Alumni" preview cards with company badges.
   * "Latest Job & Referral Postings" preview.
   * "Upcoming Reunions & Webinars".
4. **Interactive FAQ & Testimonials**.

---

### Phase 5: Alumni Directory & Smart Discovery Engine
1. **Directory Page (`alumni/directory.php`)**:
   * Filter controls:
     * Search bar: Name or keywords.
     * Filter by Company: Dropdown populated dynamically (`SELECT DISTINCT current_company FROM alumni_profiles`).
     * Filter by Graduation Batch: (e.g., 2015 to 2024).
     * Filter by Department: (CSE, IT, ECE, Mechanical, etc.).
     * Toggle: *"Open for Mentorship Only"*.
2. **Alumni Card Design**:
   * Profile photo with fallback initials.
   * Name, Graduation Year, Degree.
   * Current Company & Designation badge.
   * Skills tags (e.g. `React`, `AWS`, `Product Management`).
   * "Open for Mentorship" glowing status indicator.
   * Buttons: "View Profile" & "Request Mentorship".
3. **Asynchronous Filtering (`assets/js/filters.js` + `alumni/search-api.php`)**:
   * Instant, smooth filtering without full-page reloads.

---

### Phase 6: Careers & Internal Referral Board
1. **Job Listing Hub (`jobs/index.php`)**:
   * Clean tabs: "All Openings", "Full-time", "Internships", "Internal Referrals".
   * Job Cards: Company logo/icon, Job title, Company name, Location (Remote/On-site), Job type pill badge, Salary, Posted date, and Days left.
2. **Alumni Job Creation (`jobs/create.php`)**:
   * Restricted to verified alumni.
   * Form capturing: Role Title, Company, Location, Type, Experience, Description, Requirements, Application Deadline, and Referral Notes.
3. **Student Application Workflow (`jobs/apply.php`)**:
   * Modal dialog: Student selects either their pre-uploaded resume or uploads a fresh PDF.
   * Custom message / elevator pitch: *"Why I'm a strong fit for this referral"*.
   * Stored in `job_applications` table.
4. **Alumni Application Management (`jobs/manage.php`)**:
   * Dashboard showing all jobs posted by the logged-in alumnus.
   * For each job: Table of applicants with resume download link, student details, and status dropdown (`Submitted` -> `Under Review` -> `Referred` -> `Rejected`).
   * Status change instantly updates student's "My Applications" view.

---

### Phase 7: 1-on-1 Mentorship Booking Engine
1. **Mentorship Hub (`mentorship/index.php`)**:
   * Explanatory banner on how mentorship works.
   * List of alumni who have `is_mentor_available = 1`.
   * Displays mentorship topics (e.g., "Mock Coding Interview", "FAANG Preparation", "Masters Abroad Advice").
2. **Booking Modal (`mentorship/request.php`)**:
   * Student selects a topic, writes their query/expectation, and suggests a preferred date.
3. **Mentorship Management Dashboard (`mentorship/requests.php`)**:
   * **For Alumni**: Tab of "Incoming Requests". Buttons to "Accept" (prompts for Google Meet / Zoom link) or "Decline" (with feedback).
   * **For Students**: "My Mentorship Sessions" tracking status: `Pending`, `Accepted` (with meeting link), `Completed`.

---

### Phase 8: Campus Events & Reunions Module
1. **Events Page (`events/index.php`)**:
   * Grid of upcoming events: Webinars, Alumni Meetups, Hackathons, Guest Lectures.
   * Event Card: Banner image, Date badge (Month/Day), Time, Location/Platform, RSVP counter (`X / Max spots filled`).
2. **RSVP Action (`events/rsvp.php`)**:
   * One-click RSVP toggle. Prevents overbooking beyond `max_capacity`.
   * Generates a confirmation card: "You are registered!".
3. **Event Creation (`events/create.php`)**:
   * Allows Admin and verified Alumni to publish new events.

---

### Phase 9: "Ask Alumni" Community Forum
1. **Forum Feed (`forum/index.php`)**:
   * Categories: Career Advice, Higher Studies, Interview Prep, Campus Life.
   * Post listing with author details, creation date, reply count, and category badge.
2. **Post Detail & Threaded Replies (`forum/view.php`)**:
   * Full discussion view.
   * Authenticated users can post replies.
   * Original author can mark an alumnus's reply as the "Accepted Solution".

---

### Phase 10: Admin Command Center
1. **Admin Dashboard Overview (`admin/index.php`)**:
   * KPI stat cards:
     * Total Registered Users (Broken down by Students vs Alumni).
     * Pending Alumni Approvals counter (with direct action button).
     * Total Job & Referral Listings active.
     * Mentorship Sessions completed.
   * Top Hiring Companies breakdown.
2. **Alumni Verification Queue (`admin/verify-alumni.php`)**:
   * Table displaying all alumni accounts with status `pending`.
   * Inspect graduation batch, degree, company, and uploaded verification document.
   * Actions: **Approve** (changes status to `active`) or **Reject** (with reason notification).
3. **User Management (`admin/users.php`)**:
   * Search users, toggle active/suspended status, reset passwords if needed.

---

## 5. Verification & Testing Plan

### Automated / Sanity Testing
* **Database Migration Test**: Run schema script via PHP/MySQL CLI to verify table creation and foreign key constraints without syntax errors.
* **Database Connection Test**: Execute a script testing PDO connectivity, charset configuration, and transaction handling.

### Manual End-to-End User Journeys
1. **Authentication & Authorization**:
   * Register a new student -> confirm instant login and restricted access to alumni/admin routes.
   * Register an alumni -> confirm `pending` status prevents login until Admin approval.
   * Login as Admin -> Approve the alumni -> confirm the alumni can now log in.
2. **Directory & Search**:
   * Search for alumni by company name and graduation year; verify filters accurately narrow down the results.
3. **Job & Referral Cycle**:
   * Log in as Alumni -> Post a new job referral.
   * Log in as Student -> View job listing -> Apply with resume PDF upload.
   * Log back in as Alumni -> View applicant list -> Download resume -> Update status to "Referred".
   * Log in as Student -> Verify status is updated to "Referred" under My Applications.
4. **Mentorship Lifecycle**:
   * Student requests mentorship session with an available alumnus.
   * Alumni accepts request, provides Google Meet URL.
   * Student views accepted session and Google Meet link.
5. **Events & RSVP**:
   * Create an event -> Student RSVPs -> Verify attendee count increments and duplicates are prevented.

---

## 6. Open Questions & Design Decisions

> [!NOTE]
> All core architectural patterns are finalized. The implementation will proceed cleanly with native PHP 8, PDO, and modern custom CSS without heavy third-party framework overhead.
