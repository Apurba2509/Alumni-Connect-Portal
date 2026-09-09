<?php
// =======================================================
// BCAC591: Applicant Review & 3-State Status Workflow
// =======================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$job_id = (int)($_GET['job_id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = has_role('admin');

// Fetch Job
$job_stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$job_stmt->execute([$job_id]);
$job = $job_stmt->fetch();

if (!$job) {
    set_flash('error', 'Job posting not found.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

// Check authorization (Job Owner or Admin)
if ($job['posted_by'] !== $user_id && !$is_admin) {
    set_flash('error', 'You are not authorized to view applicants for this job.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

$page_title = "Applicants for " . $job['title'];

// Handle Status Workflow Update (Status: Pending -> Shortlisted -> Referred / Rejected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $app_id     = (int)($_POST['app_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    $allowed_statuses = ['Pending', 'Shortlisted', 'Referred', 'Rejected'];
    if (in_array($new_status, $allowed_statuses, true) && $app_id > 0) {
        $update_stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ? AND job_id = ?");
        $update_stmt->execute([$new_status, $app_id, $job_id]);

        set_flash('success', "Application status successfully changed to '$new_status'.");
        header('Location: ' . base_url('jobs/job-applicants.php?job_id=' . $job_id));
        exit();
    }
}

// Fetch all applicants for this job
$apps_stmt = $pdo->prepare("
    SELECT a.*, u.name as student_name, u.email as student_email, u.phone as student_phone, u.department as student_dept 
    FROM applications a 
    JOIN users u ON a.student_id = u.id 
    WHERE a.job_id = ? 
    ORDER BY a.applied_at DESC
");
$apps_stmt->execute([$job_id]);
$applicants = $apps_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to Job Overview</a>
        <h1 style="margin-top: 6px;">👥 Student Applicants (<?= count($applicants) ?>)</h1>
        <p>Review candidate resumes and update referral status for <strong><?= e($job['title']) ?></strong> (<?= e($job['company']) ?>)</p>
    </div>
    <div>
        <?php if (has_role('alumni')): ?>
            <a href="<?= base_url('alumni-dashboard.php') ?>" class="btn btn-secondary">Alumni Dashboard</a>
        <?php else: ?>
            <a href="<?= base_url('admin/jobs.php') ?>" class="btn btn-secondary">Admin Jobs</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <?php if (empty($applicants)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 30px 0;">No students have applied for this opportunity yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name & Contact</th>
                        <th>Dept</th>
                        <th>Applied On</th>
                        <th>Resume & Pitch</th>
                        <th>Current Status</th>
                        <th>Update Status (Workflow)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr>
                            <td>
                                <strong><?= e($app['student_name']) ?></strong><br>
                                <small style="color: var(--text-muted);">
                                    ✉️ <?= e($app['student_email']) ?><br>
                                    📞 <?= e($app['student_phone'] ?: 'No phone provided') ?>
                                </small>
                            </td>
                            <td><?= e($app['student_dept']) ?></td>
                            <td><?= format_date($app['applied_at']) ?></td>
                            <td>
                                <!-- Resume Download / View -->
                                <a href="<?= base_url('uploads/resumes/' . $app['resume_file']) ?>" class="btn btn-sm btn-secondary" target="_blank" style="margin-bottom: 6px;">
                                    📄 View Resume (PDF)
                                </a>
                                <?php if (!empty($app['cover_note'])): ?>
                                    <div style="font-size: 0.8rem; color: #475569; max-width: 280px; background: #f8fafc; padding: 6px 8px; border-radius: 4px; border: 1px solid var(--border);">
                                        "<?= e($app['cover_note']) ?>"
                                    </div>
                                <?php endif; ?>
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
                                <!-- 3-State Status Workflow Form -->
                                <form method="POST" action="" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="app_id" value="<?= $app['id'] ?>">

                                    <select name="new_status" class="form-control" style="padding: 6px 10px; font-size: 0.85rem; width: auto;">
                                        <option value="Pending" <?= ($app['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="Shortlisted" <?= ($app['status'] === 'Shortlisted') ? 'selected' : '' ?>>Shortlisted</option>
                                        <option value="Referred" <?= ($app['status'] === 'Referred') ? 'selected' : '' ?>>Referred ✓</option>
                                        <option value="Rejected" <?= ($app['status'] === 'Rejected') ? 'selected' : '' ?>>Rejected ✗</option>
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
