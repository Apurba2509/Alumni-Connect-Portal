<?php
// =======================================================
// BCAC591: Admin Dashboard
// =======================================================

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Admin
require_role('admin');

// 1. Fetch Key Portal Metrics
$student_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$alumni_count  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni'")->fetchColumn();
$jobs_count    = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$notices_count = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();

// 2. Fetch Recent Registrations
$recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// 3. Fetch Recent Notices
$recent_notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Admin Control Panel 🛡️</h1>
        <p>College Administration & Portal Management Hub</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= base_url('admin/promote.php') ?>" class="btn btn-success">🎓 Promote Student to Alumni</a>
        <a href="<?= base_url('admin/notices.php') ?>" class="btn btn-primary">📢 Post College Notice</a>
    </div>
</div>

<!-- System Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Students</div>
        <div class="stat-number"><?= e($student_count) ?></div>
        <div class="stat-desc">Enrolled in portal</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Alumni</div>
        <div class="stat-number" style="color: var(--success);"><?= e($alumni_count) ?></div>
        <div class="stat-desc">Graduated & verified</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Jobs & Drives</div>
        <div class="stat-number"><?= e($jobs_count) ?></div>
        <div class="stat-desc">Posted by alumni & admin</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Published Notices</div>
        <div class="stat-number"><?= e($notices_count) ?></div>
        <div class="stat-desc">Official announcements</div>
    </div>
</div>

<!-- Quick Admin Navigation Blocks -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 30px;">
    <a href="<?= base_url('admin/promote.php') ?>" class="card" style="text-decoration: none; color: inherit; padding: 20px; transition: transform 0.2s;">
        <div style="font-size: 1.8rem; margin-bottom: 8px;">🎓</div>
        <h3 style="font-size: 1.1rem; margin-bottom: 4px; color: var(--text);">Student Promotion</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Promote graduating students to Alumni accounts.</p>
    </a>
    <a href="<?= base_url('admin/notices.php') ?>" class="card" style="text-decoration: none; color: inherit; padding: 20px; transition: transform 0.2s;">
        <div style="font-size: 1.8rem; margin-bottom: 8px;">📢</div>
        <h3 style="font-size: 1.1rem; margin-bottom: 4px; color: var(--text);">Manage Notices</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Publish official announcements for campus.</p>
    </a>
    <a href="<?= base_url('admin/users.php') ?>" class="card" style="text-decoration: none; color: inherit; padding: 20px; transition: transform 0.2s;">
        <div style="font-size: 1.8rem; margin-bottom: 8px;">👥</div>
        <h3 style="font-size: 1.1rem; margin-bottom: 4px; color: var(--text);">User Management</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">View all accounts, toggle status, or remove users.</p>
    </a>
    <a href="<?= base_url('admin/jobs.php') ?>" class="card" style="text-decoration: none; color: inherit; padding: 20px; transition: transform 0.2s;">
        <div style="font-size: 1.8rem; margin-bottom: 8px;">💼</div>
        <h3 style="font-size: 1.1rem; margin-bottom: 4px; color: var(--text);">Job Moderation</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Review all posted jobs or post campus drives.</p>
    </a>
</div>

<!-- Recent Users Table -->
<div class="card">
    <div class="page-header" style="margin-bottom: 16px;">
        <h2 class="card-title" style="margin-bottom: 0;">Recent Registered Accounts</h2>
        <a href="<?= base_url('admin/users.php') ?>" class="btn btn-sm btn-secondary">View All Users</a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name & Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Registered On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= e($u['name']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= e($u['email']) ?></small>
                        </td>
                        <td>
                            <span class="badge" style="background: #e2e8f0; color: #334155;"><?= e($u['role']) ?></span>
                        </td>
                        <td><?= e($u['department']) ?></td>
                        <td><?= format_date($u['created_at']) ?></td>
                        <td>
                            <span class="badge <?= ($u['status'] === 'active') ? 'badge-referred' : 'badge-rejected' ?>">
                                <?= e($u['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
