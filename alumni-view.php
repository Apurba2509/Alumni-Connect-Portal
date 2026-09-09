<?php
// =======================================================
// BCAC591: Alumni Profile Detail View
// =======================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$alumni_id = (int)($_GET['id'] ?? 0);
if ($alumni_id <= 0) {
    set_flash('error', 'Invalid alumni profile requested.');
    header('Location: ' . base_url('alumni-list.php'));
    exit();
}

// Fetch Alumni Profile
$stmt = $pdo->prepare("
    SELECT u.*, a.batch_year, a.current_company, a.designation, a.city, a.linkedin_url 
    FROM users u 
    JOIN alumni_details a ON u.id = a.user_id 
    WHERE u.id = ? AND u.role = 'alumni'
");
$stmt->execute([$alumni_id]);
$alumnus = $stmt->fetch();

if (!$alumnus) {
    set_flash('error', 'Alumni profile not found.');
    header('Location: ' . base_url('alumni-list.php'));
    exit();
}

$page_title = $alumnus['name'] . " - Alumni Profile";

// Fetch jobs/referrals posted by this alumnus
$jobs_stmt = $pdo->prepare("SELECT * FROM jobs WHERE posted_by = ? ORDER BY created_at DESC");
$jobs_stmt->execute([$alumni_id]);
$posted_jobs = $jobs_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= base_url('alumni-list.php') ?>" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to Alumni Directory</a>
        <h1 style="margin-top: 6px;"><?= e($alumnus['name']) ?></h1>
        <p>
            <?= e($alumnus['designation']) ?> at <strong><?= e($alumnus['current_company']) ?></strong> &bull; 
            Batch of <?= e($alumnus['batch_year']) ?> (<?= e($alumnus['department']) ?>)
        </p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">
    
    <!-- Profile Card -->
    <div class="card">
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 3.5rem; background: #f1f5f9; border-radius: 50%; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; border: 2px solid var(--border);">
                👨‍💼
            </div>
            <h2 style="font-size: 1.3rem; margin-bottom: 4px;"><?= e($alumnus['name']) ?></h2>
            <span class="badge" style="background: #e0e7ff; color: #4338ca;">Batch <?= e($alumnus['batch_year']) ?></span>
            <span class="badge" style="background: #f1f5f9; color: #475569;"><?= e($alumnus['department']) ?></span>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 16px; font-size: 0.95rem;">
            <p style="margin-bottom: 8px;"><strong>🏢 Current Company:</strong> <?= e($alumnus['current_company']) ?></p>
            <p style="margin-bottom: 8px;"><strong>💼 Designation:</strong> <?= e($alumnus['designation']) ?></p>
            <p style="margin-bottom: 8px;"><strong>📍 Location:</strong> <?= e($alumnus['city'] ?: 'Not specified') ?></p>
            <p style="margin-bottom: 8px;"><strong>✉️ Email:</strong> <?= e($alumnus['email']) ?></p>

            <?php if (!empty($alumnus['linkedin_url'])): ?>
                <div style="margin-top: 16px;">
                    <a href="<?= e($alumnus['linkedin_url']) ?>" target="_blank" class="btn btn-primary btn-block">
                        🔗 View LinkedIn Profile
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Opportunities Posted by this Alumnus -->
    <div class="card">
        <h2 class="card-title">Opportunities & Referrals Offered by <?= e($alumnus['name']) ?></h2>

        <?php if (empty($posted_jobs)): ?>
            <p style="color: var(--text-muted); padding: 20px 0;">This alumnus has not posted any job or referral openings currently.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Role & Company</th>
                            <th>Type</th>
                            <th>Deadline</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posted_jobs as $job): ?>
                            <tr>
                                <td>
                                    <strong><?= e($job['title']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= e($job['location']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-referral"><?= e($job['job_type']) ?></span>
                                </td>
                                <td><?= format_date($job['deadline']) ?></td>
                                <td>
                                    <a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" class="btn btn-sm btn-primary">Apply &rarr;</a>
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
