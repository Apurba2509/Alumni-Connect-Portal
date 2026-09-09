<?php
// =======================================================
// BCAC591: Manage Notices (Admin Area)
// =======================================================

$page_title = "Manage Notices";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Admin
require_role('admin');

$error = '';
$admin_id = $_SESSION['user_id'];

// Handle New Notice Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $content  = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        $error = 'Please provide both notice title and description content.';
    } else {
        try {
            $insert_stmt = $pdo->prepare("INSERT INTO notices (admin_id, title, category, content) VALUES (?, ?, ?, ?)");
            $insert_stmt->execute([$admin_id, $title, $category, $content]);

            set_flash('success', 'Official notice published successfully!');
            header('Location: ' . base_url('admin/notices.php'));
            exit();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Notice Deletion
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    try {
        $del_stmt = $pdo->prepare("DELETE FROM notices WHERE id = ?");
        $del_stmt->execute([$delete_id]);

        set_flash('success', 'Notice deleted successfully.');
        header('Location: ' . base_url('admin/notices.php'));
        exit();
    } catch (PDOException $e) {
        $error = 'Error deleting notice: ' . $e->getMessage();
    }
}

// Fetch all notices
$notices = $pdo->query("SELECT n.*, u.name as admin_name FROM notices n JOIN users u ON n.admin_id = u.id ORDER BY n.created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>📢 Manage College Notices</h1>
        <p>Publish and manage announcements displayed to all students and alumni on the portal.</p>
    </div>
    <div>
        <a href="<?= base_url('notices.php') ?>" class="btn btn-secondary" target="_blank">View Live Public Board &rarr;</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <span><?= e($error) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 24px; align-items: start;">
    
    <!-- Publish New Notice Form -->
    <div class="card">
        <h2 class="card-title">Publish New Announcement</h2>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label class="form-label" for="title">Notice Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Campus Placement Drive by Infosys" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="category">Notice Category *</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="General">General Announcement</option>
                    <option value="Placement">Placement / Drive Alert</option>
                    <option value="Alumni Meet">Alumni Homecoming / Meet</option>
                    <option value="Urgent">Urgent / Important</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="content">Announcement Details / Content *</label>
                <textarea id="content" name="content" class="form-control" rows="5" placeholder="Write full details, dates, venue, or eligibility criteria..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Publish Notice</button>
        </form>
    </div>

    <!-- Existing Notices List -->
    <div class="card">
        <h2 class="card-title">All Published Notices (<?= count($notices) ?>)</h2>

        <?php if (empty($notices)): ?>
            <p style="color: var(--text-muted);">No notices published yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Notice</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notices as $n): ?>
                            <tr>
                                <td>
                                    <strong><?= e($n['title']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= e(substr($n['content'], 0, 75)) ?>...</small>
                                </td>
                                <td>
                                    <span class="badge badge-pending"><?= e($n['category']) ?></span>
                                </td>
                                <td><?= format_date($n['created_at']) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/notices.php?delete=' . $n['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notice?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
