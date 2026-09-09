<?php
// =======================================================
// BCAC591: User Management (Admin Area)
// =======================================================

$page_title = "Manage Users";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Admin
require_role('admin');

$role_filter = $_GET['role'] ?? 'all';

// Handle Status Toggle Action
if (isset($_GET['toggle_status'])) {
    $target_id = (int)$_GET['toggle_status'];
    // Avoid deactivating self
    if ($target_id === $_SESSION['user_id']) {
        set_flash('error', 'You cannot deactivate your own administrative account.');
    } else {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$target_id]);
        set_flash('success', 'User account status updated.');
    }
    header('Location: ' . base_url('admin/users.php?role=' . urlencode($role_filter)));
    exit();
}

// Handle User Deletion
if (isset($_GET['delete'])) {
    $target_id = (int)$_GET['delete'];
    if ($target_id === $_SESSION['user_id']) {
        set_flash('error', 'You cannot delete your own administrative account.');
    } else {
        $del_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $del_stmt->execute([$target_id]);
        set_flash('success', 'User account deleted successfully.');
    }
    header('Location: ' . base_url('admin/users.php?role=' . urlencode($role_filter)));
    exit();
}

// Fetch users with optional role filter
$query = "SELECT * FROM users";
$params = [];

if ($role_filter !== 'all' && in_array($role_filter, ['student', 'alumni', 'admin'])) {
    $query .= " WHERE role = ?";
    $params[] = $role_filter;
}
$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>👥 User Account Management</h1>
        <p>Monitor, activate, deactivate, or delete registered user accounts.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <h2 class="card-title" style="margin-bottom: 0;">Registered Accounts (<?= count($users) ?>)</h2>

        <!-- Role Filter Tabs -->
        <div style="display: flex; gap: 8px;">
            <a href="<?= base_url('admin/users.php?role=all') ?>" class="btn btn-sm <?= ($role_filter === 'all') ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="<?= base_url('admin/users.php?role=student') ?>" class="btn btn-sm <?= ($role_filter === 'student') ? 'btn-primary' : 'btn-secondary' ?>">Students</a>
            <a href="<?= base_url('admin/users.php?role=alumni') ?>" class="btn btn-sm <?= ($role_filter === 'alumni') ? 'btn-primary' : 'btn-secondary' ?>">Alumni</a>
            <a href="<?= base_url('admin/users.php?role=admin') ?>" class="btn btn-sm <?= ($role_filter === 'admin') ? 'btn-primary' : 'btn-secondary' ?>">Admins</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>User Details</th>
                    <th>Role</th>
                    <th>Dept & Phone</th>
                    <th>Registered</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= e($u['name']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= e($u['email']) ?></small>
                        </td>
                        <td>
                            <span class="badge" style="background: #e2e8f0; color: #334155;"><?= e($u['role']) ?></span>
                        </td>
                        <td>
                            <?= e($u['department']) ?><br>
                            <small style="color: var(--text-muted);"><?= e($u['phone'] ?: 'N/A') ?></small>
                        </td>
                        <td><?= format_date($u['created_at']) ?></td>
                        <td>
                            <span class="badge <?= ($u['status'] === 'active') ? 'badge-referred' : 'badge-rejected' ?>">
                                <?= e($u['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <a href="<?= base_url('admin/users.php?toggle_status=' . $u['id'] . '&role=' . $role_filter) ?>" class="btn btn-sm btn-secondary" title="Toggle Status">
                                    <?= ($u['status'] === 'active') ? 'Deactivate' : 'Activate' ?>
                                </a>
                                <a href="<?= base_url('admin/users.php?delete=' . $u['id'] . '&role=' . $role_filter) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user? All their posted jobs/applications will also be removed.');">
                                    Delete
                                </a>
                            <?php else: ?>
                                <small style="color: var(--text-muted);">Current User</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
