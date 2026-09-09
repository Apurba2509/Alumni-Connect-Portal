<?php
// =======================================================
// BCAC591: User Registration (Student & Alumni)
// =======================================================

$page_title = "Register";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect
if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit();
}

$error = '';
$role = $_POST['role'] ?? 'student';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$department = trim($_POST['department'] ?? 'BCA');

// Alumni specific fields
$batch_year = trim($_POST['batch_year'] ?? '');
$current_company = trim($_POST['current_company'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$city = trim($_POST['city'] ?? '');
$linkedin_url = trim($_POST['linkedin_url'] ?? '');

// Process Registration Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Server-Side Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($department)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'alumni' && (empty($batch_year) || empty($current_company) || empty($designation))) {
        $error = 'Please provide your graduation batch year, company, and designation.';
    } else {
        try {
            // Check if email already registered
            $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $check_stmt->execute([$email]);
            if ($check_stmt->fetch()) {
                $error = 'An account with this email address already exists. Please login.';
            } else {
                // Begin database transaction for atomicity
                $pdo->beginTransaction();

                // Hash password securely with BCRYPT
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert into `users` table
                $insert_user = $pdo->prepare("INSERT INTO users (name, email, password, role, phone, department, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $insert_user->execute([$name, $email, $hashed_password, $role, $phone, $department]);
                $new_user_id = $pdo->lastInsertId();

                // If registering as Alumni, insert into `alumni_details`
                if ($role === 'alumni') {
                    $insert_alumni = $pdo->prepare("INSERT INTO alumni_details (user_id, batch_year, current_company, designation, city, linkedin_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_alumni->execute([$new_user_id, (int)$batch_year, $current_company, $designation, $city, $linkedin_url]);
                }

                $pdo->commit();

                set_flash('success', 'Registration successful! You can now log in with your credentials.');
                header('Location: ' . base_url('login.php'));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 680px; margin: 30px auto;">
    <div class="card" style="padding: 36px 32px; box-shadow: var(--shadow-md);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 50px; height: 50px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 12px;">
                📝
            </div>
            <h1 class="card-title" style="font-size: 1.65rem; margin-bottom: 6px;">Create an Account</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">Join the official Alumni Connect Portal community</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Role Selection Segmented Cards -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="margin-bottom: 8px;">Select Account Type *</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1.5px solid <?= ($role === 'student') ? 'var(--primary)' : 'var(--border)' ?>; border-radius: 8px; cursor: pointer; background: <?= ($role === 'student') ? 'var(--primary-light)' : '#ffffff' ?>; transition: all 0.15s ease;">
                        <input type="radio" name="role" value="student" <?= ($role === 'student') ? 'checked' : '' ?> onchange="toggleAlumniFields(false)">
                        <div>
                            <strong style="color: var(--navy); display: block;">🎓 Student</strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Current enrolled student</span>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 14px 16px; border: 1.5px solid <?= ($role === 'alumni') ? 'var(--primary)' : 'var(--border)' ?>; border-radius: 8px; cursor: pointer; background: <?= ($role === 'alumni') ? 'var(--primary-light)' : '#ffffff' ?>; transition: all 0.15s ease;">
                        <input type="radio" name="role" value="alumni" <?= ($role === 'alumni') ? 'checked' : '' ?> onchange="toggleAlumniFields(true)">
                        <div>
                            <strong style="color: var(--navy); display: block;">👨‍💼 Alumnus</strong>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Passed out graduate</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Common Basic Details -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Rahul Sharma" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. rahul@example.com" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($phone) ?>" placeholder="e.g. 9812345678">
                </div>

                <div class="form-group">
                    <label class="form-label" for="department">Department / Course *</label>
                    <select id="department" name="department" class="form-control" required>
                        <option value="BCA" <?= ($department === 'BCA') ? 'selected' : '' ?>>BCA (Computer Applications)</option>
                        <option value="CSE" <?= ($department === 'CSE') ? 'selected' : '' ?>>B.Tech Computer Science</option>
                        <option value="IT" <?= ($department === 'IT') ? 'selected' : '' ?>>Information Technology</option>
                        <option value="MCA" <?= ($department === 'MCA') ? 'selected' : '' ?>>MCA</option>
                        <option value="ECE" <?= ($department === 'ECE') ? 'selected' : '' ?>>Electronics & Communication</option>
                    </select>
                </div>
            </div>

            <!-- Alumni Specific Fields (Hidden by default unless Alumni selected) -->
            <div id="alumni-fields" style="display: <?= ($role === 'alumni') ? 'block' : 'none' ?>; background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                <h4 style="margin-bottom: 14px; color: var(--navy); display: flex; align-items: center; gap: 6px;">
                    <span>🎓</span> Alumni Professional Details
                </h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="batch_year">Graduation Year (Batch) *</label>
                        <input type="number" id="batch_year" name="batch_year" class="form-control" value="<?= e($batch_year) ?>" placeholder="e.g. 2021" min="1990" max="<?= date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="current_company">Current Company *</label>
                        <input type="text" id="current_company" name="current_company" class="form-control" value="<?= e($current_company) ?>" placeholder="e.g. Google, TCS, Infosys">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="designation">Job Title / Designation *</label>
                        <input type="text" id="designation" name="designation" class="form-control" value="<?= e($designation) ?>" placeholder="e.g. Software Engineer">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City / Location</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?= e($city) ?>" placeholder="e.g. Bangalore, Pune">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="linkedin_url">LinkedIn Profile URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" value="<?= e($linkedin_url) ?>" placeholder="https://linkedin.com/in/username">
                </div>
            </div>

            <!-- Passwords -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 0.98rem; margin-top: 8px;">
                Create Account &rarr;
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="<?= base_url('login.php') ?>" style="font-weight: 700;">Sign in here</a>
        </div>
    </div>
</div>

<script>
function toggleAlumniFields(show) {
    const fields = document.getElementById('alumni-fields');
    fields.style.display = show ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
