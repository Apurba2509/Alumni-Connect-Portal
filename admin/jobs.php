<?php
// =======================================================
// BCAC591: Job Moderation (Admin Area)
// =======================================================

$page_title = "Moderate Jobs";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Admin
require_role('admin');

// Handle Job Deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->execute([$delete_id]);

    set_flash('success', 'Job posting removed successfully.');
    header('Location: ' . base_url('admin/jobs.php'));
    exit();
}

// Fetch all jobs with poster details and application count
$jobs_stmt = $pdo->query("
    SELECT j.*, u.name as poster_name, u.role as poster_role, COUNT(a.id) as app_count 
    FROM jobs j 
    JOIN users u ON j.posted_by = u.id 
    LEFT JOIN applications a ON j.id = a.job_id 
    GROUP BY j.id 
    ORDER BY j.created_at DESC
");
$jobs = $jobs_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>💼 Job & Placement Moderation</h1>
        <p>Monitor all jobs and referral opportunities posted across the platform.</p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-add.php') ?>" class="btn btn-primary">+ Post College Drive / Job</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Job Title & Company</th>
                    <th>Posted By</th>
                    <th>Type</th>
                    <th>Deadline</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No job listings found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $j): ?>
                        <tr>
                            <td>
                                <strong><?= e($j['title']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= e($j['company']) ?> &bull; <?= e($j['location']) ?></small>
                            </td>
                            <td>
                                <?= e($j['poster_name']) ?><br>
                                <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.7rem;"><?= e($j['poster_role']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-referral"><?= e($j['job_type']) ?></span>
                            </td>
                            <td><?= format_date($j['deadline']) ?></td>
                            <td><strong><?= e($j['app_count']) ?></strong> applicants</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?= base_url('jobs/job-view.php?id=' . $j['id']) ?>" class="btn btn-sm btn-secondary" target="_blank">View</a>
                                    <a href="<?= base_url('admin/jobs.php?delete=' . $j['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this job posting permanently?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
