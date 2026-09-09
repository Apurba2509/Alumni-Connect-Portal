<?php
// =======================================================
// BCAC591: Alumni Connect Portal - Landing / Home Page
// =======================================================

$page_title = "Home";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch Live Statistics
try {
    $alumni_count  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'alumni' AND status = 'active'")->fetchColumn();
    $jobs_count    = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $notices_count = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();
    $apps_count    = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    // Fetch Latest 3 Notices
    $notices_stmt = $pdo->query("SELECT * FROM notices ORDER BY created_at DESC LIMIT 3");
    $recent_notices = $notices_stmt->fetchAll();

    // Fetch Latest 4 Jobs
    $jobs_stmt = $pdo->query("SELECT j.*, u.name as posted_by_name FROM jobs j JOIN users u ON j.posted_by = u.id ORDER BY j.created_at DESC LIMIT 4");
    $recent_jobs = $jobs_stmt->fetchAll();
} catch (PDOException $e) {
    $alumni_count = $jobs_count = $notices_count = $apps_count = 0;
    $recent_notices = [];
    $recent_jobs = [];
}
?>

<!-- High-Impact University Portal Hero Banner -->
<section class="hero">
    <div class="hero-pill">
        <span>🏛️</span>
        <span>Official Alumni & Student Network</span>
    </div>
    <h1>Connect. Mentor. Elevate.</h1>
    <p>Bridging the gap between ambitious students and established alumni across leading global organizations for referrals, guidance, and college engagement.</p>
    
    <div class="hero-actions">
        <?php if (!is_logged_in()): ?>
            <a href="<?= base_url('register.php') ?>" class="btn btn-primary" style="padding: 12px 24px; font-size: 1rem;">Join Network &rarr;</a>
            <a href="<?= base_url('login.php') ?>" class="btn btn-secondary" style="padding: 12px 24px; font-size: 1rem;">Sign In</a>
        <?php else: ?>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-primary" style="padding: 12px 24px; font-size: 1rem;">Explore Opportunities &rarr;</a>
            <a href="<?= base_url('alumni-list.php') ?>" class="btn btn-secondary" style="padding: 12px 24px; font-size: 1rem;">Browse Alumni Directory</a>
        <?php endif; ?>
    </div>
</section>

<!-- Live Network Statistics Counter -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Verified Alumni</div>
        <div class="stat-number"><?= e($alumni_count) ?></div>
        <div class="stat-desc">Working across top companies worldwide</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Active Opportunities</div>
        <div class="stat-number" style="color: var(--primary);"><?= e($jobs_count) ?></div>
        <div class="stat-desc">Internal referrals & campus drives</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Official Notices</div>
        <div class="stat-number"><?= e($notices_count) ?></div>
        <div class="stat-desc">Published by College Administration</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Student Referrals</div>
        <div class="stat-number" style="color: var(--success);"><?= e($apps_count) ?></div>
        <div class="stat-desc">Applications submitted & reviewed</div>
    </div>
</div>

<!-- Main Split: Latest Notices & Recent Opportunities -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 28px; margin-top: 20px;">
    
    <!-- Latest Campus Notices Column -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 20px;">
            <h2 class="card-title" style="margin-bottom: 0;">📢 Latest Announcements</h2>
            <a href="<?= base_url('notices.php') ?>" class="btn btn-sm btn-secondary">View Board &rarr;</a>
        </div>
        
        <?php if (empty($recent_notices)): ?>
            <p style="color: var(--text-muted); padding: 15px 0;">No announcements published yet.</p>
        <?php else: ?>
            <?php foreach ($recent_notices as $notice): ?>
                <div class="notice-card">
                    <div class="notice-meta">
                        <?php
                            $cat_class = 'badge-pending';
                            if ($notice['category'] === 'Urgent') $cat_class = 'badge-rejected';
                            elseif ($notice['category'] === 'Placement') $cat_class = 'badge-shortlisted';
                            elseif ($notice['category'] === 'Alumni Meet') $cat_class = 'badge-referred';
                        ?>
                        <span class="badge <?= $cat_class ?>"><?= e($notice['category']) ?></span>
                        <span><?= format_date($notice['created_at']) ?></span>
                    </div>
                    <div class="notice-title"><?= e($notice['title']) ?></div>
                    <div class="notice-content"><?= e(substr($notice['content'], 0, 110)) ?>...</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Recent Opportunities Column -->
    <div class="card">
        <div class="page-header" style="margin-bottom: 20px;">
            <h2 class="card-title" style="margin-bottom: 0;">💼 Recent Opportunities</h2>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-sm btn-secondary">Explore All &rarr;</a>
        </div>

        <?php if (empty($recent_jobs)): ?>
            <p style="color: var(--text-muted); padding: 15px 0;">No job opportunities posted yet.</p>
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
                                    <strong><a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>"><?= e($job['title']) ?></a></strong><br>
                                    <small style="color: var(--text-muted); font-weight: 500;">🏢 <?= e($job['company']) ?> &bull; 📍 <?= e($job['location']) ?></small>
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
