<?php
// =======================================================
// BCAC591: Student Dashboard
// =======================================================

$page_title = "Student Dashboard";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

// Guard: Only students can access
require_role('student');

$student_id = $_SESSION['user_id'];

// 1. Fetch Student Metrics
$app_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE student_id = ?");
$app_count_stmt->execute([$student_id]);
$my_apps_count = $app_count_stmt->fetchColumn();

$active_jobs_count = $pdo->query("SELECT COUNT(*) FROM jobs WHERE deadline >= CURDATE()")->fetchColumn();
$alumni_network_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND status = 'active'")->fetchColumn();

// 2. Fetch My Recent 5 Applications with Job Details
$apps_stmt = $pdo->prepare("
    SELECT a.*, j.title as job_title, j.company, j.job_type, j.location 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE a.student_id = ? 
    ORDER BY a.applied_at DESC 
    LIMIT 5
");
$apps_stmt->execute([$student_id]);
$my_recent_apps = $apps_stmt->fetchAll();

// 3. Fetch Recent 3 Notices
$notices = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Welcome, <?= e($_SESSION['user_name']) ?> 👋</h1>
        <p>Department of <?= e($_SESSION['user_department']) ?> &bull; Student Career Dashboard</p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-primary">Browse All Opportunities</a>
    </div>
</div>

<!-- Key Student Metrics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">My Applications</div>
        <div class="stat-number"><?= e($my_apps_count) ?></div>
        <div class="stat-desc">Referrals & jobs applied to</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Opportunities</div>
        <div class="stat-number"><?= e($active_jobs_count) ?></div>
        <div class="stat-desc">Open for referral submissions</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Alumni Network</div>
        <div class="stat-number"><?= e($alumni_network_count) ?></div>
        <div class="stat-desc">Verified seniors available</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    
    <!-- My Applications Table (Status Workflow) -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 16px;">
            <h2 class="card-title" style="margin-bottom: 0;">📋 My Recent Applications</h2>
            <a href="<?= base_url('my-applications.php') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <?php if (empty($my_recent_apps)): ?>
            <p style="color: var(--text-muted); padding: 10px 0;">You haven't applied for any jobs or referrals yet. <a href="<?= base_url('jobs/job-list.php') ?>">Browse active listings here</a>.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Job Title & Company</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_recent_apps as $app): ?>
                            <tr>
                                <td>
                                    <strong><?= e($app['job_title']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= e($app['company']) ?></small>
                                </td>
                                <td><?= format_date($app['applied_at']) ?></td>
                                <td>
                                    <?php
                                        $badge_class = 'badge-pending';
                                        if ($app['status'] === 'Shortlisted') $badge_class = 'badge-shortlisted';
                                        elseif ($app['status'] === 'Referred') $badge_class = 'badge-referred';
                                        elseif ($app['status'] === 'Rejected') $badge_class = 'badge-rejected';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= e($app['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('jobs/job-view.php?id=' . $app['job_id']) ?>" class="btn btn-sm btn-secondary">View Job</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notices Feed -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 16px;">
            <h2 class="card-title" style="margin-bottom: 0;">📢 Notices</h2>
            <a href="<?= base_url('notices.php') ?>" class="btn btn-sm btn-secondary">All</a>
        </div>

        <?php if (empty($notices)): ?>
            <p style="color: var(--text-muted);">No notices posted.</p>
        <?php else: ?>
            <?php foreach ($notices as $notice): ?>
                <div class="notice-card" style="margin-bottom: 12px; padding: 12px 14px;">
                    <div class="notice-meta">
                        <span class="badge badge-pending"><?= e($notice['category']) ?></span>
                        <span><?= format_date($notice['created_at']) ?></span>
                    </div>
                    <div style="font-weight: 700; font-size: 0.95rem; margin-bottom: 4px;"><?= e($notice['title']) ?></div>
                    <div style="font-size: 0.85rem; color: var(--secondary);"><?= e(substr($notice['content'], 0, 80)) ?>...</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
