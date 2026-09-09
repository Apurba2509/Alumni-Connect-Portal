<?php
// =======================================================
// BCAC591: User Login
// =======================================================

$page_title = "Login";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect to respective dashboard
if (is_logged_in()) {
    $role = $_SESSION['user_role'] ?? 'student';
    if ($role === 'admin') {
        header('Location: ' . base_url('admin/dashboard.php'));
    } elseif ($role === 'alumni') {
        header('Location: ' . base_url('alumni-dashboard.php'));
    } else {
        header('Location: ' . base_url('student-dashboard.php'));
    }
    exit();
}

$error = '';
$email = '';

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-Side Form Validation
    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Find user by email using PDO Prepared Statement
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] !== 'active') {
                    $error = 'Your account has been deactivated. Please contact the administrator.';
                } else {
                    // Password correct & account active -> Set Session
                    $_SESSION['user_id']         = $user['id'];
                    $_SESSION['user_name']       = $user['name'];
                    $_SESSION['user_email']      = $user['email'];
                    $_SESSION['user_role']       = $user['role'];
                    $_SESSION['user_department'] = $user['department'];

                    set_flash('success', 'Welcome back, ' . $user['name'] . '!');

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: ' . base_url('admin/dashboard.php'));
                    } elseif ($user['role'] === 'alumni') {
                        header('Location: ' . base_url('alumni-dashboard.php'));
                    } else {
                        header('Location: ' . base_url('student-dashboard.php'));
                    }
                    exit();
                }
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 480px; margin: 30px auto;">
    <div class="card">
        <h1 class="card-title" style="text-align: center; font-size: 1.6rem; margin-bottom: 8px;">Portal Sign In</h1>
        <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">Sign in with your Student, Alumni, or Admin account</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. rahul@gmail.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Sign In</button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account yet? <a href="<?= base_url('register.php') ?>" style="font-weight: 600;">Register here</a>
        </div>
        
        <!-- Lab Demo Credentials Hint Box -->
        <div style="background: #f1f5f9; border-radius: 6px; padding: 12px; margin-top: 20px; font-size: 0.85rem; color: #475569;">
            <strong>🔑 Demo Test Credentials:</strong><br>
            • Password for ALL accounts is: <code>changeme</code><br>
            • Admin: <code>admin@college.edu</code><br>
            • Alumni: <code>rahul@gmail.com</code><br>
            • Student: <code>amit@college.edu</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
