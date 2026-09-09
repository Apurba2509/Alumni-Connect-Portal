<?php
// =======================================================
// BCAC591: Official College Notice Board
// =======================================================

$page_title = "College Notices & Announcements";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$category_filter = $_GET['category'] ?? 'all';

$query = "SELECT n.*, u.name as admin_name FROM notices n JOIN users u ON n.admin_id = u.id";
$params = [];

if ($category_filter !== 'all' && in_array($category_filter, ['General', 'Placement', 'Alumni Meet', 'Urgent'])) {
    $query .= " WHERE n.category = ?";
    $params[] = $category_filter;
}

$query .= " ORDER BY n.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notices = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>📢 Official College Notice Board</h1>
        <p>Stay updated with the latest campus placement drives, alumni reunions, and important academic notifications.</p>
    </div>
    <div>
        <?php if (has_role('admin')): ?>
            <a href="<?= base_url('admin/notices.php') ?>" class="btn btn-primary">+ Post New Notice</a>
        <?php endif; ?>
    </div>
</div>

<!-- Category Filters -->
<div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
    <a href="<?= base_url('notices.php?category=all') ?>" class="btn btn-sm <?= ($category_filter === 'all') ? 'btn-primary' : 'btn-secondary' ?>">All Announcements</a>
    <a href="<?= base_url('notices.php?category=Placement') ?>" class="btn btn-sm <?= ($category_filter === 'Placement') ? 'btn-primary' : 'btn-secondary' ?>">Placement & Drives</a>
    <a href="<?= base_url('notices.php?category=Alumni Meet') ?>" class="btn btn-sm <?= ($category_filter === 'Alumni Meet') ? 'btn-primary' : 'btn-secondary' ?>">Alumni Meets</a>
    <a href="<?= base_url('notices.php?category=Urgent') ?>" class="btn btn-sm <?= ($category_filter === 'Urgent') ? 'btn-primary' : 'btn-secondary' ?>">Urgent Alerts</a>
</div>

<!-- Notices List -->
<?php if (empty($notices)): ?>
    <div class="card" style="text-align: center; padding: 40px;">
        <p style="color: var(--text-muted);">No announcements published under this category.</p>
    </div>
<?php else: ?>
    <?php foreach ($notices as $n): ?>
        <div class="card notice-card" style="padding: 24px; margin-bottom: 20px;">
            <div class="notice-meta" style="margin-bottom: 10px;">
                <?php 
                    $cat_badge = 'badge-pending';
                    if ($n['category'] === 'Urgent') $cat_badge = 'badge-rejected';
                    elseif ($n['category'] === 'Placement') $cat_badge = 'badge-shortlisted';
                    elseif ($n['category'] === 'Alumni Meet') $cat_badge = 'badge-referred';
                ?>
                <span class="badge <?= $cat_badge ?>"><?= e($n['category']) ?></span>
                <span>📅 Published on <?= format_date($n['created_at']) ?></span>
                <span>&bull; Author: <strong><?= e($n['admin_name']) ?></strong></span>
            </div>

            <h2 class="notice-title" style="font-size: 1.35rem; margin-bottom: 12px; color: var(--text);">
                <?= e($n['title']) ?>
            </h2>

            <div style="color: var(--secondary); font-size: 1rem; line-height: 1.7; white-space: pre-line;">
                <?= e($n['content']) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
