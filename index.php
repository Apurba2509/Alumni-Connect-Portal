<?php
// =======================================================
// BCAC591: Alumni Connect Portal - Landing / Home Page
// =======================================================

$page_title = "Home";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch Live Statistics
try {
    $alumni_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND status = 'active'")->fetchColumn();
    $jobs_count = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $notices_count = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
    $apps_count = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    // Fetch Latest 3 Notices
    $notices_stmt = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3");
    $recent_notices = $notices_stmt->fetchAll();

    // Fetch Latest 3 Jobs
    $jobs_stmt = $pdo->query("SELECT j.*, u.name as posted_by_name FROM jobs j JOIN users u ON j.posted_by = u.id ORDER BY j.created_at DESC LIMIT 3");
    $recent_jobs = $jobs_stmt->fetchAll();
} catch (PDOException $e) {
    $alumni_count = $jobs_count = $notices_count = $apps_count = 0;
    $recent_notices = [];
    $recent_jobs = [];
}
?>

<!-- Hero Banner -->
<section class="hero">
    <h1>Connect. Guide. Elevate.</h1>
    <p>The official portal connecting college students with passed-out alumni for career guidance, verified job referrals, and official campus notices.</p>
    
    <div class="hero-actions">
        <?php if (!is_logged_in()): ?>
            <a href="<?= base_url('register.php') ?>" class="btn btn-primary">Join the Network</a>
            <a href="<?= base_url('login.php') ?>" class="btn btn-secondary">Sign In</a>
        <?php else: ?>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-primary">Explore Jobs</a>
            <a href="<?= base_url('alumni-list.php') ?>" class="btn btn-secondary">Browse Alumni</a>
        <?php endif; ?>
    </div>
</section>

<!-- Live Statistics Counter -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Registered Alumni</div>
        <div class="stat-number"><?= e($alumni_count) ?></div>
        <div class="stat-desc">Working across top tech companies</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Job Openings</div>
        <div class="stat-number"><?= e($jobs_count) ?></div>
        <div class="stat-desc">Referrals, internships & full-time</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Official Notices</div>
        <div class="stat-number"><?= e($notices_count) ?></div>
        <div class="stat-desc">Published by College Admin</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Referral Applications</div>
        <div class="stat-number"><?= e($apps_count) ?></div>
        <div class="stat-desc">Students connected so far</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-top: 20px;">
    
    <!-- Latest Notices Column -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 16px;">
            <h2 class="card-title" style="margin-bottom: 0;">📢 Latest Notices</h2>
            <a href="<?= base_url('notices.php') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>
        
        <?php if (empty($recent_notices)): ?>
            <p style="color: var(--text-muted);">No announcements published yet.</p>
        <?php else: ?>
            <?php foreach ($recent_notices as $notice): ?>
                <div class="notice-card">
                    <div class="notice-meta">
                        <span class="badge badge-pending"><?= e($notice['category']) ?></span>
                        <span><?= format_date($notice['created_at']) ?></span>
                    </div>
                    <div class="notice-title"><?= e($notice['title']) ?></div>
                    <div class="notice-content"><?= e(substr($notice['content'], 0, 120)) ?>...</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Opportunities Column -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 16px;">
            <h2 class="card-title" style="margin-bottom: 0;">💼 Recent Opportunities</h2>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <?php if (empty($recent_jobs)): ?>
            <p style="color: var(--text-muted);">No job listings posted yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Position & Company</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_jobs as $job): ?>
                            <tr>
                                <td>
                                    <strong><?= e($job['title']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= e($job['company']) ?> &bull; <?= e($job['location']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $type_class = ($job['job_type'] === 'Referral') ? 'badge-referral' : (($job['job_type'] === 'Internship') ? 'badge-internship' : 'badge-fulltime');
                                    ?>
                                    <span class="badge <?= $type_class ?>"><?= e($job['job_type']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" class="btn btn-sm btn-primary">Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
