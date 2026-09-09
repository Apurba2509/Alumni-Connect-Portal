<?php
// =======================================================
// BCAC591: Job Listings (Search + Sort Page)
// =======================================================

$page_title = "Jobs & Referral Openings";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Search and Sort Parameters
$search = trim($_GET['search'] ?? '');
$sort   = $_GET['sort'] ?? 'newest';
$type   = $_GET['type'] ?? 'all';

// Build Dynamic PDO Query
$query = "SELECT j.*, u.name as poster_name, u.role as poster_role FROM jobs j JOIN users u ON j.posted_by = u.id WHERE 1=1";
$params = [];

// 1. Keyword Search
if (!empty($search)) {
    $query .= " AND (j.title LIKE ? OR j.company LIKE ? OR j.location LIKE ? OR j.description LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term]);
}

// 2. Job Type Filter
if ($type !== 'all' && in_array($type, ['Full-Time', 'Part-Time', 'Internship', 'Referral'])) {
    $query .= " AND j.job_type = ?";
    $params[] = $type;
}

// 3. Sorting
if ($sort === 'deadline') {
    $query .= " ORDER BY j.deadline ASC";
} elseif ($sort === 'company') {
    $query .= " ORDER BY j.company ASC";
} else {
    $query .= " ORDER BY j.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>💼 Jobs & Referral Opportunities</h1>
        <p>Explore opportunities and direct referral openings posted by college alumni and the placement cell.</p>
    </div>
    <div>
        <?php if (has_role(['alumni', 'admin'])): ?>
            <a href="<?= base_url('jobs/job-add.php') ?>" class="btn btn-primary">+ Post Opportunity</a>
        <?php endif; ?>
    </div>
</div>

<!-- Search & Sort Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="" class="filter-bar" style="margin-bottom: 0;">
        <input type="text" name="search" class="form-control" placeholder="Search by job title, company, or skills..." value="<?= e($search) ?>">

        <select name="type" class="form-control" style="max-width: 180px;">
            <option value="all" <?= ($type === 'all') ? 'selected' : '' ?>>All Opportunity Types</option>
            <option value="Referral" <?= ($type === 'Referral') ? 'selected' : '' ?>>Alumni Referrals</option>
            <option value="Internship" <?= ($type === 'Internship') ? 'selected' : '' ?>>Internships</option>
            <option value="Full-Time" <?= ($type === 'Full-Time') ? 'selected' : '' ?>>Full-Time Jobs</option>
        </select>

        <select name="sort" class="form-control" style="max-width: 180px;">
            <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Sort: Newest First</option>
            <option value="deadline" <?= ($sort === 'deadline') ? 'selected' : '' ?>>Sort: Deadline Soonest</option>
            <option value="company" <?= ($sort === 'company') ? 'selected' : '' ?>>Sort: Company (A-Z)</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if (!empty($search) || $type !== 'all' || $sort !== 'newest'): ?>
            <a href="<?= base_url('jobs/job-list.php') ?>" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Jobs Table Listing -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Role & Company</th>
                    <th>Type</th>
                    <th>Salary / Package</th>
                    <th>Posted By</th>
                    <th>Deadline</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No job listings found matching your search criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <strong><a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" style="color: var(--text);"><?= e($job['title']) ?></a></strong><br>
                                <small style="color: var(--text-muted);">🏢 <?= e($job['company']) ?> &bull; 📍 <?= e($job['location']) ?></small>
                            </td>
                            <td>
                                <?php 
                                    $type_class = ($job['job_type'] === 'Referral') ? 'badge-referral' : (($job['job_type'] === 'Internship') ? 'badge-internship' : 'badge-fulltime');
                                ?>
                                <span class="badge <?= $type_class ?>"><?= e($job['job_type']) ?></span>
                            </td>
                            <td><?= e($job['salary'] ?: 'As per industry norms') ?></td>
                            <td>
                                <?= e($job['poster_name']) ?><br>
                                <small style="color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem;"><?= e($job['poster_role']) ?></small>
                            </td>
                            <td>
                                <?= format_date($job['deadline']) ?>
                            </td>
                            <td>
                                <a href="<?= base_url('jobs/job-view.php?id=' . $job['id']) ?>" class="btn btn-sm btn-primary">View Details &rarr;</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
