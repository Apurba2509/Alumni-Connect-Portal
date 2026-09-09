<?php
// =======================================================
// BCAC591: Promote Student to Alumni (Admin Privilege)
// =======================================================

$page_title = "Promote Student to Alumni";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Guard: Only Admin can promote
require_role('admin');

$error = '';
$selected_student_id = (int)($_GET['student_id'] ?? 0);

// Handle Promotion Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id      = (int)($_POST['student_id'] ?? 0);
    $batch_year      = (int)($_POST['batch_year'] ?? date('Y'));
    $current_company = trim($_POST['current_company'] ?? '');
    $designation     = trim($_POST['designation'] ?? '');
    $city            = trim($_POST['city'] ?? '');
    $linkedin_url    = trim($_POST['linkedin_url'] ?? '');

    // Server-Side Validation
    if ($student_id <= 0 || empty($current_company) || empty($designation) || $batch_year < 1990) {
        $error = 'Please select a valid student and provide their batch year, company, and designation.';
    } else {
        try {
            // Verify student exists and is currently 'student'
            $check_stmt = $pdo->prepare("SELECT name, role FROM users WHERE id = ?");
            $check_stmt->execute([$student_id]);
            $student = $check_stmt->fetch();

            if (!$student || $student['role'] !== 'student') {
                $error = 'The selected user is not a valid student account.';
            } else {
                $pdo->beginTransaction();

                // 1. Update user role from 'student' to 'alumni'
                $update_role = $pdo->prepare("UPDATE users SET role = 'alumni' WHERE id = ?");
                $update_role->execute([$student_id]);

                // 2. Insert into alumni_details table
                $insert_details = $pdo->prepare("
                    INSERT INTO alumni_details (user_id, batch_year, current_company, designation, city, linkedin_url) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        batch_year = VALUES(batch_year), 
                        current_company = VALUES(current_company), 
                        designation = VALUES(designation), 
                        city = VALUES(city), 
                        linkedin_url = VALUES(linkedin_url)
                ");
                $insert_details->execute([$student_id, $batch_year, $current_company, $designation, $city, $linkedin_url]);

                $pdo->commit();

                set_flash('success', "🎉 Student '" . $student['name'] . "' has been officially graduated and promoted to Alumni status!");
                header('Location: ' . base_url('admin/promote.php'));
                exit();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all current students for promotion table & dropdown
$students_stmt = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY name ASC");
$students = $students_stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>🎓 Promote Student to Alumni</h1>
        <p>When students pass out, upgrade their profile to an Alumnus account so they can post jobs and mentor juniors.</p>
    </div>
    <div>
        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error">
        <span><?= e($error) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; align-items: start;">
    
    <!-- Promotion Form Card -->
    <div class="card">
        <h2 class="card-title">Promote Graduating Student</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="student_id">Select Graduating Student *</label>
                <select name="student_id" id="student_id" class="form-control" required>
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($selected_student_id === $s['id']) ? 'selected' : '' ?>>
                            <?= e($s['name']) ?> (<?= e($s['email']) ?> - <?= e($s['department']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="batch_year">Passing Out Year (Batch) *</label>
                    <input type="number" id="batch_year" name="batch_year" class="form-control" value="<?= date('Y') ?>" min="1990" max="<?= date('Y') + 1 ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="current_company">Placed / Working Company *</label>
                    <input type="text" id="current_company" name="current_company" class="form-control" placeholder="e.g. Infosys, TCS, Wipro" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="designation">Job Title / Designation *</label>
                    <input type="text" id="designation" name="designation" class="form-control" placeholder="e.g. Junior Software Engineer" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">Current Work City</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="e.g. Bangalore, Kolkata">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="linkedin_url">LinkedIn Profile URL</label>
                <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/username">
            </div>

            <button type="submit" class="btn btn-success btn-block" style="margin-top: 10px;">
                ✓ Confirm Promotion to Alumni
            </button>
        </form>
    </div>

    <!-- Active Students Waiting for Graduation -->
    <div class="card">
        <h2 class="card-title">Enrolled Students (<?= count($students) ?>)</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 14px;">Click 'Promote' next to any student to populate the form.</p>

        <?php if (empty($students)): ?>
            <p style="color: var(--text-muted);">No students currently enrolled.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Dept</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td>
                                    <strong><?= e($s['name']) ?></strong><br>
                                    <small style="color: var(--text-muted);"><?= e($s['email']) ?></small>
                                </td>
                                <td><?= e($s['department']) ?></td>
                                <td>
                                    <a href="<?= base_url('admin/promote.php?student_id=' . $s['id']) ?>" class="btn btn-sm btn-primary">Promote &rarr;</a>
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
