<?php
// =======================================================
// BCAC591: Student Application Tracker (Status Workflow)
// =======================================================

$page_title = "My Applications";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

require_role('student');

$student_id = $_SESSION['user_id'];

// Fetch all applications submitted by this student
$apps_stmt = $pdo->prepare("
    SELECT a.*, j.title as job_title, j.company, j.location, j.job_type, j.salary, j.deadline, u.name as poster_name 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN users u ON j.posted_by = u.id 
    WHERE a.student_id = ? 
    ORDER BY a.applied_at DESC
");
$apps_stmt->execute([$student_id]);
$applications = $apps_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>📋 My Job & Referral Applications</h1>
        <p>Track the real-time review and referral status of your submitted applications.</p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-primary">Browse More Opportunities</a>
    </div>
</div>

<div class="card">
    <?php if (empty($applications)): ?>
        <div style="text-align: center; padding: 40px 20px;">
            <span style="font-size: 3rem;">📭</span>
            <h3 style="margin-top: 10px; color: var(--text);">No Applications Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">You haven't submitted any job or referral applications yet.</p>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-primary">Explore Available Openings</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Job Title & Company</th>
                        <th>Type</th>
                        <th>Applied On</th>
                        <th>Uploaded Resume</th>
                        <th>Referral Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <strong><?= e($app['job_title']) ?></strong><br>
                                <small style="color: var(--text-muted);"><?= e($app['company']) ?> &bull; <?= e($app['location']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-referral"><?= e($app['job_type']) ?></span>
                            </td>
                            <td><?= format_date($app['applied_at']) ?></td>
                            <td>
                                <a href="<?= base_url('uploads/resumes/' . $app['resume_file']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                                    📄 PDF Resume
                                </a>
                            </td>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
