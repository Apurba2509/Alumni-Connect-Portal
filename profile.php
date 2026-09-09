<?php
// =======================================================
// BCAC591: User Profile View
// =======================================================

$page_title = "My Profile";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

require_login();

$user_id = $_SESSION['user_id'];

// Fetch User
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'User record not found.');
    header('Location: ' . base_url('index.php'));
    exit();
}

// Fetch alumni details if role is alumni
$alumni_details = null;
if ($user['role'] === 'alumni') {
    $alumni_stmt = $pdo->prepare("SELECT * FROM alumni_details WHERE user_id = ?");
    $alumni_stmt->execute([$user_id]);
    $alumni_details = $alumni_stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>👤 My Account Profile</h1>
        <p>Manage your account settings and personal information.</p>
    </div>
    <div>
        <a href="<?= base_url('profile-edit.php') ?>" class="btn btn-primary">✏️ Edit Profile</a>
    </div>
</div>

<div style="max-width: 650px; margin: 0 auto;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 3.5rem; background: #f1f5f9; border-radius: 50%; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; border: 2px solid var(--border);">
                <?= ($user['role'] === 'admin') ? '🛡️' : (($user['role'] === 'alumni') ? '👨‍💼' : '🎓') ?>
            </div>
            <h2 style="font-size: 1.4rem; color: var(--text);"><?= e($user['name']) ?></h2>
            <span class="badge" style="background: #e2e8f0; color: #334155;"><?= strtoupper(e($user['role'])) ?></span>
            <span class="badge badge-referred"><?= e($user['status']) ?></span>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Email Address</span>
                    <div style="font-weight: 600; margin-top: 4px;"><?= e($user['email']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Phone Number</span>
                    <div style="font-weight: 600; margin-top: 4px;"><?= e($user['phone'] ?: 'Not specified') ?></div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Department / Course</span>
                    <div style="font-weight: 600; margin-top: 4px;"><?= e($user['department']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Joined Portal</span>
                    <div style="font-weight: 600; margin-top: 4px;"><?= format_date($user['created_at']) ?></div>
                </div>
            </div>

            <?php if ($user['role'] === 'alumni' && $alumni_details): ?>
                <div style="background: #f8fafc; border: 1px dashed var(--border); border-radius: 8px; padding: 16px; margin-top: 20px;">
                    <h4 style="color: var(--primary); margin-bottom: 12px;">🎓 Alumni Career Details</h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.95rem;">
                        <div><strong>Batch Year:</strong> <?= e($alumni_details['batch_year']) ?></div>
                        <div><strong>Company:</strong> <?= e($alumni_details['current_company']) ?></div>
                        <div><strong>Designation:</strong> <?= e($alumni_details['designation']) ?></div>
                        <div><strong>City:</strong> <?= e($alumni_details['city'] ?: 'N/A') ?></div>
                    </div>

                    <?php if (!empty($alumni_details['linkedin_url'])): ?>
                        <div style="margin-top: 10px;">
                            <a href="<?= e($alumni_details['linkedin_url']) ?>" target="_blank" style="font-weight: 600;">🔗 <?= e($alumni_details['linkedin_url']) ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top: 24px;">
                <a href="<?= base_url('profile-edit.php') ?>" class="btn btn-primary btn-block">Edit Profile Information</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
