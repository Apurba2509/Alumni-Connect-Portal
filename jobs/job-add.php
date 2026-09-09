<?php
// =======================================================
// BCAC591: Post Opportunity (Alumni & Admin Access)
// =======================================================

$page_title = "Post Job / Referral Opportunity";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Alumni and Admin can post jobs
require_role(['alumni', 'admin']);

$error = '';
$user_id = $_SESSION['user_id'];

// Default company for alumni if available
$default_company = '';
if ($_SESSION['user_role'] === 'alumni') {
    $alumni_company_stmt = $pdo->prepare("SELECT current_company FROM alumni_details WHERE user_id = ?");
    $alumni_company_stmt->execute([$user_id]);
    $default_company = $alumni_company_stmt->fetchColumn() ?: '';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $company     = trim($_POST['company'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $job_type    = trim($_POST['job_type'] ?? 'Referral');
    $salary      = trim($_POST['salary'] ?? '');
    $deadline    = trim($_POST['deadline'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Server-Side Form Validation
    if (empty($title) || empty($company) || empty($location) || empty($deadline) || empty($description)) {
        $error = 'Please fill in all required fields marked with an asterisk (*).';
    } elseif (strtotime($deadline) < strtotime(date('Y-m-d'))) {
        $error = 'The application deadline cannot be a past date.';
    } else {
        try {
            $insert_job = $pdo->prepare("
                INSERT INTO jobs (posted_by, title, company, location, job_type, salary, description, deadline) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insert_job->execute([$user_id, $title, $company, $location, $job_type, $salary, $description, $deadline]);
            $new_job_id = $pdo->lastInsertId();

            set_flash('success', 'Opportunity posted successfully! Students can now view and apply.');
            header('Location: ' . base_url('jobs/job-view.php?id=' . $new_job_id));
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
        <h1>💼 Post New Opportunity or Referral</h1>
        <p>Help current college students by listing job vacancies, paid internships, or direct referral opportunities.</p>
    </div>
    <div>
        <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-secondary">&larr; Back to Listings</a>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <h2 class="card-title">Opportunity Details</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <span><?= e($error) ?></span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="title">Job Title / Role *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Associate Software Engineer" value="<?= e($_POST['title'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="company">Company / Organization *</label>
                    <input type="text" id="company" name="company" class="form-control" placeholder="e.g. Google, Microsoft, TCS" value="<?= e($_POST['company'] ?? $default_company) ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="location">Location / Work Mode *</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Bangalore / Hybrid / Remote" value="<?= e($_POST['location'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="job_type">Opportunity Type *</label>
                    <select id="job_type" name="job_type" class="form-control" required>
                        <option value="Referral" <?= (($_POST['job_type'] ?? '') === 'Referral') ? 'selected' : '' ?>>Alumni Referral</option>
                        <option value="Internship" <?= (($_POST['job_type'] ?? '') === 'Internship') ? 'selected' : '' ?>>Internship</option>
                        <option value="Full-Time" <?= (($_POST['job_type'] ?? '') === 'Full-Time') ? 'selected' : '' ?>>Full-Time Job</option>
                        <option value="Part-Time" <?= (($_POST['job_type'] ?? '') === 'Part-Time') ? 'selected' : '' ?>>Part-Time</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="salary">Compensation / CTC</label>
                    <input type="text" id="salary" name="salary" class="form-control" placeholder="e.g. 6 - 8 LPA or 25k/month" value="<?= e($_POST['salary'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="deadline">Application Deadline *</label>
                <input type="date" id="deadline" name="deadline" class="form-control" value="<?= e($_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" min="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Job Description, Requirements & Eligibility *</label>
                <textarea id="description" name="description" class="form-control" rows="6" placeholder="Describe the role, tech stack (e.g. React, Java, Python), qualifications, and referral criteria..." required><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Publish Opportunity</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
