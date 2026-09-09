<?php
// =======================================================
// BCAC591: Edit User Profile
// =======================================================

$page_title = "Edit Profile";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$user_id = $_SESSION['user_id'];

// Fetch current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch alumni details if applicable
$alumni_details = null;
if ($user['role'] === 'alumni') {
    $alumni_stmt = $pdo->prepare("SELECT * FROM alumni_details WHERE user_id = ?");
    $alumni_stmt->execute([$user_id]);
    $alumni_details = $alumni_stmt->fetch();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');

    // Alumni specific
    $batch_year      = (int)($_POST['batch_year'] ?? 0);
    $current_company = trim($_POST['current_company'] ?? '');
    $designation     = trim($_POST['designation'] ?? '');
    $city            = trim($_POST['city'] ?? '');
    $linkedin_url    = trim($_POST['linkedin_url'] ?? '');

    if (empty($name) || empty($department)) {
        $error = 'Name and department cannot be empty.';
    } else {
        try {
            $pdo->beginTransaction();

            // Update user table
            $update_user = $pdo->prepare("UPDATE users SET name = ?, phone = ?, department = ? WHERE id = ?");
            $update_user->execute([$name, $phone, $department, $user_id]);

            // Update session name and department
            $_SESSION['user_name']       = $name;
            $_SESSION['user_department'] = $department;

            // Update alumni details if role is alumni
            if ($user['role'] === 'alumni') {
                $update_alumni = $pdo->prepare("
                    INSERT INTO alumni_details (user_id, batch_year, current_company, designation, city, linkedin_url) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        batch_year = VALUES(batch_year), 
                        current_company = VALUES(current_company), 
                        designation = VALUES(designation), 
                        city = VALUES(city), 
                        linkedin_url = VALUES(linkedin_url)
                ");
                $update_alumni->execute([$user_id, $batch_year, $current_company, $designation, $city, $linkedin_url]);
            }

            $pdo->commit();

            set_flash('success', 'Profile information updated successfully!');
            header('Location: ' . base_url('profile.php'));
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Edit Profile</h1>
        <p>Keep your contact and professional details up to date.</p>
    </div>
    <div>
        <a href="<?= base_url('profile.php') ?>" class="btn btn-secondary">&larr; Back to Profile</a>
    </div>
</div>

<div style="max-width: 650px; margin: 0 auto;">
    <div class="card">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                <span class="form-help">Email cannot be changed as it is your unique login credential.</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="department">Department *</label>
                    <input type="text" id="department" name="department" class="form-control" value="<?= e($user['department']) ?>" required>
                </div>
            </div>

            <?php if ($user['role'] === 'alumni'): ?>
                <div style="background: #f8fafc; border: 1px dashed var(--border); border-radius: 8px; padding: 16px; margin: 20px 0;">
                    <h4 style="color: var(--primary); margin-bottom: 12px;">🎓 Professional & Alumni Information</h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="batch_year">Passing Batch Year</label>
                            <input type="number" id="batch_year" name="batch_year" class="form-control" value="<?= e($alumni_details['batch_year'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="current_company">Current Company</label>
                            <input type="text" id="current_company" name="current_company" class="form-control" value="<?= e($alumni_details['current_company'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label" for="designation">Designation</label>
                            <input type="text" id="designation" name="designation" class="form-control" value="<?= e($alumni_details['designation'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?= e($alumni_details['city'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                        <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" value="<?= e($alumni_details['linkedin_url'] ?? '') ?>">
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-block">Save Profile Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
