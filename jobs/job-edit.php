<?php
// =======================================================
// BCAC591: Edit Job Opportunity (CRUD: Update)
// =======================================================

$page_title = "Edit Opportunity";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$job_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = has_role('admin');

// Fetch existing job
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    set_flash('error', 'Job not found.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

// Check authorization (Owner or Admin)
if ($job['posted_by'] !== $user_id && !$is_admin) {
    set_flash('error', 'You are not authorized to edit this opportunity.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $company     = trim($_POST['company'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $job_type    = trim($_POST['job_type'] ?? 'Referral');
    $salary      = trim($_POST['salary'] ?? '');
    $deadline    = trim($_POST['deadline'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($company) || empty($location) || empty($deadline) || empty($description)) {
        $error = 'Please fill in all mandatory fields.';
    } else {
        try {
            $update = $pdo->prepare("
                UPDATE jobs 
                SET title = ?, company = ?, location = ?, job_type = ?, salary = ?, deadline = ?, description = ? 
                WHERE id = ?
            ");
            $update->execute([$title, $company, $location, $job_type, $salary, $deadline, $description, $job_id]);

            set_flash('success', 'Job opportunity details updated successfully.');
            header('Location: ' . base_url('jobs/job-view.php?id=' . $job_id));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>✏️ Edit Opportunity Details</h1>
        <p>Update information for <strong><?= e($job['title']) ?></strong></p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" class="btn btn-secondary">&larr; Back to Job</a>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="title">Job Title *</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?= e($job['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="company">Company *</label>
                    <input type="text" id="company" name="company" class="form-control" value="<?= e($job['company']) ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="location">Location *</label>
                    <input type="text" id="location" name="location" class="form-control" value="<?= e($job['location']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="job_type">Type *</label>
                    <select id="job_type" name="job_type" class="form-control" required>
                        <option value="Referral" <?= ($job['job_type'] === 'Referral') ? 'selected' : '' ?>>Alumni Referral</option>
                        <option value="Internship" <?= ($job['job_type'] === 'Internship') ? 'selected' : '' ?>>Internship</option>
                        <option value="Full-Time" <?= ($job['job_type'] === 'Full-Time') ? 'selected' : '' ?>>Full-Time Job</option>
                        <option value="Part-Time" <?= ($job['job_type'] === 'Part-Time') ? 'selected' : '' ?>>Part-Time</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="salary">Compensation</label>
                    <input type="text" id="salary" name="salary" class="form-control" value="<?= e($job['salary']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="deadline">Deadline *</label>
                <input type="date" id="deadline" name="deadline" class="form-control" value="<?= e($job['deadline']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description & Requirements *</label>
                <textarea id="description" name="description" class="form-control" rows="6" required><?= e($job['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
