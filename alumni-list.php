<?php
// =======================================================
// BCAC591: Alumni Directory (Search & Discovery)
// =======================================================

$page_title = "Alumni Directory";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$search = trim($_GET['search'] ?? '');
$batch  = trim($_GET['batch'] ?? 'all');

// Build query
$query = "
    SELECT u.id, u.name, u.email, u.phone, u.department, u.avatar, 
           a.batch_year, a.current_company, a.designation, a.city, a.linkedin_url 
    FROM users u 
    JOIN alumni_details a ON u.id = a.user_id 
    WHERE u.role = 'alumni' AND u.status = 'active'
";
$params = [];

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR a.current_company LIKE ? OR a.designation LIKE ? OR a.city LIKE ? OR u.department LIKE ?)";
    $term = "%$search%";
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}

if ($batch !== 'all' && !empty($batch)) {
    $query .= " AND a.batch_year = ?";
    $params[] = (int)$batch;
}

$query .= " ORDER BY a.batch_year DESC, u.name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alumni_list = $stmt->fetchAll();

// Fetch distinct batch years for filter dropdown
$batches = $pdo->query("SELECT DISTINCT batch_year FROM alumni_details ORDER BY batch_year DESC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🎓 Alumni Directory</h1>
        <p>Explore our global alumni community, discover where seniors work, and connect for career mentorship.</p>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card" style="padding: 18px 22px; margin-bottom: 28px;">
    <form method="GET" action="" class="filter-bar" style="margin-bottom: 0;">
        <input type="text" name="search" class="form-control" placeholder="Search by name, company (e.g. Google, Microsoft), designation, or city..." value="<?= e($search) ?>">

        <select name="batch" class="form-control" style="max-width: 190px;">
            <option value="all">All Passing Batches</option>
            <?php foreach ($batches as $b): ?>
                <option value="<?= e($b) ?>" <?= ($batch === (string)$b) ? 'selected' : '' ?>>Batch of <?= e($b) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Search Directory</button>
        <?php if (!empty($search) || $batch !== 'all'): ?>
            <a href="<?= base_url('alumni-list.php') ?>" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Alumni Cards Grid -->
<?php if (empty($alumni_list)): ?>
    <div class="card" style="text-align: center; padding: 48px 20px;">
        <span style="font-size: 3rem;">🔍</span>
        <h3 style="margin-top: 10px; color: var(--navy);">No Alumni Found</h3>
        <p style="color: var(--text-muted);">Try adjusting your search keywords or batch filter.</p>
    </div>
<?php else: ?>
    <div class="alumni-grid">
        <?php foreach ($alumni_list as $alumnus): ?>
            <div class="alumni-card">
                <div>
                    <div class="alumni-header">
                        <div class="alumni-avatar-bubble">
                            <?= strtoupper(substr($alumnus['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--navy); margin-bottom: 2px;">
                                <?= e($alumnus['name']) ?>
                            </h3>
                            <span class="badge" style="background: #e0e7ff; color: #4338ca;">Batch '<?= substr((string)$alumnus['batch_year'], -2) ?></span>
                            <span class="badge" style="background: #f1f5f9; color: #475569;"><?= e($alumnus['department']) ?></span>
                        </div>
                    </div>

                    <div class="alumni-company-box">
                        <div style="font-weight: 700; color: var(--primary); font-size: 0.95rem; margin-bottom: 2px;">
                            <?= e($alumnus['designation']) ?>
                        </div>
                        <div style="color: var(--navy); font-weight: 600; font-size: 0.9rem;">
                            🏢 <?= e($alumnus['current_company']) ?>
                        </div>
                        <?php if (!empty($alumnus['city'])): ?>
                            <div style="color: var(--text-muted); font-size: 0.82rem; margin-top: 4px;">
                                📍 <?= e($alumnus['city']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border); padding-top: 14px; margin-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <?php if (!empty($alumnus['linkedin_url'])): ?>
                        <a href="<?= e($alumnus['linkedin_url']) ?>" target="_blank" style="font-size: 0.85rem; font-weight: 600; color: #0077b5;">
                            🔗 LinkedIn
                        </a>
                    <?php else: ?>
                        <span style="font-size: 0.8rem; color: var(--text-light);">Verified Alumnus</span>
                    <?php endif; ?>

                    <a href="<?= base_url('alumni-view.php?id=' . $alumnus['id']) ?>" class="btn btn-sm btn-primary">
                        View Profile &rarr;
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
