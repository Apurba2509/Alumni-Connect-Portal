<?php
// =======================================================
// BCAC591: Delete Job Opportunity (CRUD: Delete)
// =======================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$job_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$is_admin = has_role('admin');

// Fetch job
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch();

if ($job) {
    // Check authorization (Owner or Admin)
    if ($job['posted_by'] === $user_id || $is_admin) {
        $del = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $del->execute([$job_id]);
        set_flash('success', 'Job posting deleted successfully.');
    } else {
        set_flash('error', 'You are not authorized to delete this job.');
    }
} else {
    set_flash('error', 'Job posting not found.');
}

// Redirect back to dashboard or listings
if (has_role('alumni')) {
    header('Location: ' . base_url('alumni-dashboard.php'));
} elseif (has_role('admin')) {
    header('Location: ' . base_url('admin/jobs.php'));
} else {
    header('Location: ' . base_url('jobs/job-list.php'));
}
exit();
