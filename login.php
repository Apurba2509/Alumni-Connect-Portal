<?php
// =======================================================
// BCAC591: User Login
// =======================================================

$page_title = "Sign In";
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
    $email    = trim($_POST['email'] ?? '');
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

<div style="max-width: 440px; margin: 40px auto;">
    <div class="card" style="padding: 36px 32px; box-shadow: var(--shadow-md);">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="width: 50px; height: 50px; background: var(--primary-light); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 14px;">
                🎓
            </div>
            <h1 class="card-title" style="font-size: 1.6rem; margin-bottom: 6px;">Portal Sign In</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">Welcome back! Please enter your details.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="email">College / Personal Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>" placeholder="name@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 0.96rem; margin-top: 6px;">
                Sign In to Portal &rarr;
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account yet? <a href="<?= base_url('register.php') ?>" style="font-weight: 700;">Create Account</a>
        </div>
        
        <!-- Lab Evaluation Test Credentials Hint Box -->
        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 14px; margin-top: 24px; font-size: 0.84rem; color: var(--secondary);">
            <div style="font-weight: 700; color: var(--navy); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                <span>🔑</span>
                <span>Demo Evaluation Credentials:</span>
            </div>
            <div style="line-height: 1.6;">
                Password for all accounts: <code>changeme</code><br>
                • Admin: <code>admin@college.edu</code><br>
                • Alumni: <code>rahul@gmail.com</code><br>
                • Student: <code>amit@college.edu</code>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
