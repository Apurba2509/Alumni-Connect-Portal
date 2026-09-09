<?php
// =======================================================
// BCAC591: Job Detail View & Student Application
// =======================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$job_id = (int)($_GET['id'] ?? 0);
if ($job_id <= 0) {
    set_flash('error', 'Invalid job opening requested.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

// Fetch Job Details
$stmt = $pdo->prepare("
    SELECT j.*, u.name as poster_name, u.email as poster_email, u.role as poster_role, u.department as poster_dept 
    FROM jobs j 
    JOIN users u ON j.posted_by = u.id 
    WHERE j.id = ?
");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if (!$job) {
    set_flash('error', 'Job posting not found or has been removed.');
    header('Location: ' . base_url('jobs/job-list.php'));
    exit();
}

$page_title = $job['title'] . " at " . $job['company'];

// Check if current user is logged in
$student_id = $_SESSION['user_id'] ?? 0;
$user_role  = $_SESSION['user_role'] ?? '';
$is_owner   = ($student_id === (int)$job['posted_by']);
$is_admin   = ($user_role === 'admin');

// Check if student has already applied
$existing_app = null;
if ($user_role === 'student') {
    $check_app = $pdo->prepare("SELECT * FROM applications WHERE job_id = ? AND student_id = ?");
    $check_app->execute([$job_id, $student_id]);
    $existing_app = $check_app->fetch();
}

// Total applicants count for this job
$app_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE job_id = ?");
$app_count_stmt->execute([$job_id]);
$total_applicants = $app_count_stmt->fetchColumn();

// Handle Student Application Submission (With File Upload)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply') {
    if (!is_logged_in() || $user_role !== 'student') {
        $error = 'Only students can submit job applications.';
    } elseif ($existing_app) {
        $error = 'You have already submitted an application for this opportunity.';
    } else {
        $cover_note = trim($_POST['cover_note'] ?? '');

        // File Upload Validation (Resume PDF)
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload your resume in PDF format.';
        } else {
            $file_tmp  = $_FILES['resume']['tmp_name'];
            $file_name = $_FILES['resume']['name'];
            $file_size = $_FILES['resume']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate File Type (Only PDF)
            if ($file_ext !== 'pdf') {
                $error = 'Invalid file type. Only PDF documents are accepted.';
            } 
            // Validate File Size (Max 2MB = 2097152 bytes)
            elseif ($file_size > 2097152) {
                $error = 'Resume file size is too large. Maximum allowed size is 2MB.';
            } else {
                // Generate secure unique filename
                $new_filename = 'resume_' . $student_id . '_' . time() . '.pdf';
                $upload_path  = __DIR__ . '/../uploads/resumes/' . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Save to database
                    $insert_app = $pdo->prepare("
                        INSERT INTO applications (job_id, student_id, resume_file, cover_note, status) 
                        VALUES (?, ?, ?, ?, 'Pending')
                    ");
                    $insert_app->execute([$job_id, $student_id, $new_filename, $cover_note]);

                    set_flash('success', 'Your application and resume have been submitted successfully!');
                    header('Location: ' . base_url('jobs/job-view.php?id=' . $job_id));
                    exit();
                } else {
                    $error = 'Failed to upload resume file. Please try again.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= base_url('jobs/job-list.php') ?>" style="color: var(--text-muted); font-size: 0.9rem;">&larr; Back to all opportunities</a>
        <h1 style="margin-top: 6px;"><?= e($job['title']) ?></h1>
        <p>
            <strong>🏢 <?= e($job['company']) ?></strong> &bull; 
            📍 <?= e($job['location']) ?> &bull; 
            Posted on <?= format_date($job['created_at']) ?>
        </p>
    </div>
    <div>
        <?php if ($is_owner || $is_admin): ?>
            <a href="<?= base_url('jobs/job-applicants.php?job_id=' . $job['id']) ?>" class="btn btn-primary">
                👥 View Applicants (<?= e($total_applicants) ?>)
            </a>
            <a href="<?= base_url('jobs/job-edit.php?id=' . $job['id']) ?>" class="btn btn-secondary">Edit</a>
            <a href="<?= base_url('jobs/job-delete.php?id=' . $job['id']) ?>" class="btn btn-danger" onclick="return confirm('Delete this job?');">Delete</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <span><?= e($error) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    
    <!-- Left Column: Detailed Job Description -->
    <div>
        <div class="card">
            <h2 class="card-title">Job & Referral Overview</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 8px;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Opportunity Type</span>
                    <div style="font-weight: 700; margin-top: 4px;"><?= e($job['job_type']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Compensation</span>
                    <div style="font-weight: 700; margin-top: 4px;"><?= e($job['salary'] ?: 'As per industry norms') ?></div>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Application Deadline</span>
                    <div style="font-weight: 700; color: var(--danger); margin-top: 4px;"><?= format_date($job['deadline']) ?></div>
                </div>
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Total Applicants</span>
                    <div style="font-weight: 700; margin-top: 4px;"><?= e($total_applicants) ?> students</div>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Role Description & Requirements:</h3>
            <div style="white-space: pre-line; line-height: 1.7; color: var(--secondary); margin-bottom: 24px;">
                <?= e($job['description']) ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Poster Info & Application Box -->
    <div>
        <!-- Opportunity Poster Card -->
        <div class="card">
            <h3 class="card-title" style="font-size: 1.1rem;">Posted By</h3>
            <p><strong><?= e($job['poster_name']) ?></strong></p>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                <?= ucfirst(e($job['poster_role'])) ?> &bull; Department of <?= e($job['poster_dept']) ?>
            </p>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">
                ✉️ <?= e($job['poster_email']) ?>
            </p>
        </div>

        <!-- Student Application Box -->
        <div class="card">
            <h3 class="card-title" style="font-size: 1.1rem;">Application Status</h3>

            <?php if (!is_logged_in()): ?>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">Please log in as a student to apply for this opening or request a referral.</p>
                <a href="<?= base_url('login.php') ?>" class="btn btn-primary btn-block">Log In to Apply</a>

            <?php elseif ($user_role === 'student'): ?>
                <?php if ($existing_app): ?>
                    <!-- Already Applied Message -->
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px; text-align: center;">
                        <span style="font-size: 2rem;">✅</span>
                        <h4 style="margin: 8px 0 4px; color: #166534;">Application Submitted!</h4>
                        <p style="font-size: 0.85rem; color: #15803d; margin-bottom: 12px;">
                            You applied on <?= format_date($existing_app['applied_at']) ?>
                        </p>
                        <div>
                            Current Status: 
                            <?php
                                $badge_class = 'badge-pending';
                                if ($existing_app['status'] === 'Shortlisted') $badge_class = 'badge-shortlisted';
                                elseif ($existing_app['status'] === 'Referred') $badge_class = 'badge-referred';
                                elseif ($existing_app['status'] === 'Rejected') $badge_class = 'badge-rejected';
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= e($existing_app['status']) ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Application Form (With File Upload) -->
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="apply">

                        <div class="form-group">
                            <label class="form-label" for="resume">Upload Resume (PDF only, Max 2MB) *</label>
                            <input type="file" id="resume" name="resume" class="form-control" accept=".pdf" required>
                            <span class="form-help">Accepted format: .pdf (Max size: 2MB)</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="cover_note">Cover Note / Why hire you?</label>
                            <textarea id="cover_note" name="cover_note" class="form-control" rows="4" placeholder="Briefly mention your projects, LeetCode, or why you are a great fit..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">Submit Application & Resume</button>
                    </form>
                <?php endif; ?>

            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 0.9rem;">You are signed in as an <strong><?= e($user_role) ?></strong>. Applications are reserved for students.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
