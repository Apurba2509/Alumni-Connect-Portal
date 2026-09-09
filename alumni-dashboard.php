<?php
// =======================================================
// BCAC591: Alumni Dashboard
// =======================================================

$page_title = "Alumni Dashboard";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

// Guard: Only alumni can access
require_role('alumni');

$alumni_id = $_SESSION['user_id'];

// 1. Fetch Alumni details for welcome banner
$alumni_stmt = $pdo->prepare("SELECT a.*, u.department FROM alumni_details a JOIN users u ON a.user_id = u.id WHERE a.user_id = ?");
$alumni_stmt->execute([$alumni_id]);
$alumni_info = $alumni_stmt->fetch();

// 2. Fetch Metrics
// Total jobs posted by this alumnus
$my_jobs_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE posted_by = ?");
$my_jobs_count_stmt->execute([$alumni_id]);
$my_jobs_count = $my_jobs_count_stmt->fetchColumn();

// Total applicants for jobs posted by this alumnus
$applicants_count_stmt = $pdo->prepare("
    SELECT COUNT(a.id) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.posted_by = ?
");
$applicants_count_stmt->execute([$alumni_id]);
$total_applicants = $applicants_count_stmt->fetchColumn();

// Pending reviews
$pending_count_stmt = $pdo->prepare("
    SELECT COUNT(a.id) 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE j.posted_by = ? AND a.status = 'Pending'
");
$pending_count_stmt->execute([$alumni_id]);
$pending_reviews = $pending_count_stmt->fetchColumn();

// 3. Fetch all jobs posted by this alumnus with applicants count
$jobs_stmt = $pdo->prepare("
    SELECT j.*, COUNT(a.id) as applicant_count 
    FROM jobs j 
    LEFT JOIN applications a ON j.id = a.job_id 
    WHERE j.posted_by = ? 
    GROUP BY j.id 
    ORDER BY j.created_at DESC
");
$jobs_stmt->execute([$alumni_id]);
$my_jobs = $jobs_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Welcome Back, <?= e($_SESSION['user_name']) ?> 👋</h1>
        <p>
            <?= !empty($alumni_info['designation']) ? e($alumni_info['designation']) . ' at ' . e($alumni_info['current_company']) : 'Alumnus Portal' ?> 
            &bull; Batch of <?= e($alumni_info['batch_year'] ?? '') ?> (<?= e($alumni_info['department'] ?? '') ?>)
        </p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-add.php') ?>" class="btn btn-primary">+ Post New Opportunity</a>
    </div>
</div>

<!-- Metrics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Jobs & Referrals Posted</div>
        <div class="stat-number"><?= e($my_jobs_count) ?></div>
        <div class="stat-desc">Openings created by you</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Student Applicants</div>
        <div class="stat-number"><?= e($total_applicants) ?></div>
        <div class="stat-desc">Resumes submitted for your roles</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pending Reviews</div>
        <div class="stat-number" style="color: var(--warning);"><?= e($pending_reviews) ?></div>
        <div class="stat-desc">Awaiting your evaluation</div>
    </div>
</div>

<!-- My Postings Management (Core Entity CRUD) -->
<div class="card">
    <div class="page-header" style="margin-bottom: 16px;">
        <h2 class="card-title" style="margin-bottom: 0;">💼 My Posted Opportunities & Referrals</h2>
        <a href="<?= base_url('jobs/job-add.php') ?>" class="btn btn-sm btn-primary">+ Add New Job</a>
    </div>

    <?php if (empty($my_jobs)): ?>
        <p style="color: var(--text-muted); padding: 15px 0;">You haven't posted any jobs or referral openings yet. <a href="<?= base_url('jobs/job-add.php') ?>">Post your first opening now</a> to help your juniors!</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Type</th>
                        <th>Deadline</th>
                        <th>Applicants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_jobs as $job): ?>
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
                            <td><?= format_date($job['deadline']) ?></td>
                            <td>
                                <strong><?= e($job['applicant_count']) ?></strong> applicants
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="<?= base_url('jobs/job-applicants.php?job_id=' . $job['id']) ?>" class="btn btn-sm btn-primary">Review Applicants</a>
                                    <a href="<?= base_url('jobs/job-edit.php?id=' . $job['id']) ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="<?= base_url('jobs/job-delete.php?id=' . $job['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this job posting?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
