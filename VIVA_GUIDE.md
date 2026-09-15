# BCAC591 Lab Viva Guide: Alumni Connect Portal

**Subject:** BCAC591 – Web Technology Lab (PHP with MySQL)  
**Project Title:** Alumni Connect Portal  
**Team Size:** 4 Students  

---

## Part 1: The 30-Second Opening Pitch
*(If Sir asks: "Explain your project in brief" or "What does this portal do?")*

> **Your Answer:**  
> *"Sir, the **Alumni Connect Portal** is a role-based web application connecting college students, graduating alumni, and faculty administration. It has 3 distinct user roles:  
> 1. **Students** can browse verified alumni, view campus notices, and apply for job/internship referrals with their PDF resumes.  
> 2. **Alumni** can post career opportunities, review student applicants, and update their status through a 3-stage referral workflow (`Pending` → `Shortlisted` → `Referred`).  
> 3. **Admins** have a central control panel to moderate jobs, publish college notices, and officially **promote passed-out students to Alumni** upon graduation."*

---

## Part 2: Top 10 Most Frequently Asked Viva Questions & Answers

### Q1: "How did you prevent SQL Injection in your project?" *(#1 Favorite Question)*
* **Sir's intent:** Checking if you used vulnerable string concatenation or modern standards.
* **Your Answer:**  
  *"Sir, we used **PHP Data Objects (PDO)** with **Prepared Statements** everywhere. In `config/db.php`, we disabled emulation using `PDO::ATTR_EMULATE_PREPARES => false`. With prepared statements, the SQL command structure and user input parameters are sent to MySQL separately. The database compiler treats user input strictly as literal data, never as executable SQL code, completely preventing SQL injection."*

---

### Q2: "How are passwords stored? Why didn't you use MD5 or SHA1?"
* **Sir's intent:** Testing modern cybersecurity knowledge.
* **Your Answer:**  
  *"Sir, MD5 and SHA1 are obsolete and vulnerable to precomputed Rainbow Table attacks and fast brute-force collision attacks. Instead, we use PHP's native **`password_hash($pass, PASSWORD_BCRYPT)`** upon registration and **`password_verify($pass, $hash)`** upon login. BCRYPT automatically generates a cryptographically secure random salt and uses an adaptive cost factor, making brute-force cracking practically impossible."*

---

### Q3: "How does Session Management & Role-Based Access Control work?"
* **Sir's intent:** Checking if users can bypass authentication by typing URLs directly.
* **Your Answer:**  
  *"Sir, we use PHP sessions (`session_start()`). On successful login, the user's ID and role (`student`, `alumni`, or `admin`) are stored on the server in `$_SESSION`.  
  To prevent unauthorized access, we built central guard functions in `includes/auth_check.php`:  
  - `require_login()` redirects guests to `login.php`.  
  - `require_role('admin')` checks `$_SESSION['user_role']`. If a student manually types `/admin/dashboard.php` into the address bar, the server immediately denies access and redirects them back to their own student dashboard."*

---

### Q4: "Where are uploaded files (PDF resumes & avatars) saved? Why not in the database?"
* **Sir's intent:** Testing file storage vs database BLOB architecture.
* **Your Answer:**  
  *"Sir, we do **not** store binary files in MySQL as `BLOB`s because doing so bloats the database buffer pool and drastically slows down queries and backups.  
  Instead:  
  - The actual `.pdf` and image files are saved directly on the web server filesystem inside `uploads/resumes/` and `uploads/avatars/`.  
  - The MySQL database only stores a lightweight, unique string filename (e.g. `resume_4_1726388492.pdf`)."*

---

### Q5: "How did you make file uploads secure against malicious scripts?"
* **Sir's intent:** Checking if someone can upload a malicious `shell.php` file disguised as a PDF or image.
* **Your Answer:**  
  *"Sir, we implemented a 4-layer server-side validation pipeline:  
  1. **Extension Whitelist:** Only `pdf` is allowed for resumes; only `jpg, png, webp` for avatars.  
  2. **File Size Capping:** Strict check that `$_FILES['size'] <= 2097152` (2 MB max).  
  3. **MIME / Binary Header Verification:** For images, we run `getimagesize($_FILES['tmp_name'])` to verify authentic image headers so a renamed `.php` script is rejected.  
  4. **Filename Sanitization:** We rename files using `time() . '_' . $id . '.pdf'` using `move_uploaded_file()`, preventing filename collisions and directory traversal attacks."*

---

### Q6: "Explain your Database Schema. How many tables do you have?"
* **Sir's intent:** Verifying relational database design and normalization.
* **Your Answer:**  
  *"Sir, we designed a normalized **5-table relational schema** in MySQL (InnoDB engine):  
  1. **`users`**: Stores base identity, credentials, department, and role (`student`, `alumni`, `admin`).  
  2. **`alumni_details`**: 1-to-1 extension table storing batch year, current company, designation, and LinkedIn.  
  3. **`jobs`**: Core CRUD entity posted by alumni or admin (company, role, deadline, salary).  
  4. **`applications`**: Linking table for student applications, storing resume filename and 3-state referral status.  
  5. **`notices`**: Official announcements published by admins with categories (`Placement`, `Alumni Meet`, `Urgent`).  
  All foreign keys include **`ON DELETE CASCADE`** so if a user or job is deleted, orphaned applications and profile details are automatically cleaned up."*

---

### Q7: "What is a Database Transaction and where did you use it?"
* **Sir's intent:** Testing ACID properties.
* **Your Answer:**  
  *"Sir, we used database transactions (`$pdo->beginTransaction()` and `$pdo->commit()`) in two critical places:  
  1. **Alumni Registration & Student Promotion:** Upgrading a student to alumni requires two coordinated queries: updating `users.role = 'alumni'` AND inserting career info into `alumni_details`. If the second query fails, `$pdo->rollBack()` runs automatically, ensuring the database never enters an inconsistent state.  
  2. **Profile Photo Updates:** Ensuring user info and new avatar path are committed atomically."*

---

### Q8: "How do Flash Messages work in your project?"
* **Sir's intent:** Checking state preservation across HTTP redirects.
* **Your Answer:**  
  *"Sir, HTTP is stateless. When a form is submitted (POST), the server updates the database and issues a `header('Location: ...')` redirect (Post/Redirect/Get pattern to prevent duplicate form resubmissions). To display feedback like *'Job posted successfully'* on the next page, we store the message temporarily in `$_SESSION['flash']['success']`. When `includes/header.php` renders, `display_flash()` prints the HTML alert and immediately executes `unset($_SESSION['flash'])` so the alert vanishes on page refresh."*

---

### Q9: "What is XSS (Cross-Site Scripting) and how is it prevented?"
* **Sir's intent:** Web security fundamentals.
* **Your Answer:**  
  *"Sir, XSS happens when user-submitted JavaScript code is reflected back and executed in another user's browser. To prevent this, all dynamic variables output into HTML are wrapped in our helper function:  
  `function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }`  
  This converts dangerous characters like `<` and `>` into safe HTML entities (`&lt;` and `&gt;`), rendering them harmlessly as plain text."*

---

### Q10: "What is the 3-state Status Workflow in your project?"
* **Sir's intent:** Checking adherence to the syllabus workflow requirement.
* **Your Answer:**  
  *"Sir, in `jobs/job-applicants.php`, when a student applies for a referral, the application lifecycle goes through 3 defined stages:  
  `Pending` ➔ `Shortlisted` ➔ `Referred` (or `Rejected`).  
  The alumnus who posted the job can download the applicant's resume PDF, review their credentials, and update the status dropdown, which updates the student's tracker in real time on `my-applications.php`."*

---

## Part 3: Quick Code Reference Map
*(If Sir says: "Show me where in the code you did this...")*

| Feature to Show | Open This File |
| :--- | :--- |
| **Database Connection & PDO Flags** | `config/db.php` |
| **Session Guards & Role Check** | `includes/auth_check.php` |
| **Password Hashing (`password_hash`)** | `register.php` (around line 58) |
| **Password Verification (`password_verify`)** | `login.php` (around line 43) |
| **Resume PDF Upload & Validation** | `jobs/job-view.php` (around line 63) |
| **Profile Photo Upload (`getimagesize`)** | `profile-edit.php` (around line 45) |
| **Student Promotion Transaction** | `admin/promote.php` (around line 38) |
| **Status Workflow Dropdown** | `jobs/job-applicants.php` (around line 36) |
| **XSS Helper & Flash Messages** | `includes/functions.php` |

---

## Part 4: Demo Credentials to Have Ready
*(Keep these handy on a notepad when presenting)*

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@college.edu` | `changeme` |
| **Alumni** | `rahul@gmail.com` | `changeme` |
| **Student** | `amit@college.edu` | `changeme` |

---

## Part 5: Three Golden Presentation Tips
1. **Always use technical keywords:** Say *"PDO prepared statements"* instead of *"queries"*, *"BCRYPT hashing"* instead of *"encryption"*, and *"server-side validation"* instead of *"checks"*.
2. **Demonstrate role transitions:** Log in as **Admin**, navigate to **Promote Student**, select a graduating student, and promote them. Then log in as that newly promoted user to show they now have the **Alumni Dashboard** and can post jobs. Professors love seeing live database transitions!
3. **Be confident about the stack:** When asked why no framework (like Laravel or React) was used, state proudly: *"Sir, this project is built with clean vanilla PHP 8.2 and MySQL to demonstrate mastery over core server-side fundamentals, HTTP sessions, and relational database constraints as outlined in our BCAC591 syllabus."*
