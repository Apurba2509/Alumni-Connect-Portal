<?php
// =======================================================
// BCAC591: Reusable Header & Navigation Bar
// =======================================================

require_once __DIR__ . '/functions.php';
$user = current_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' | Alumni Connect Portal' : 'Alumni Connect Portal' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- Main Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="<?= base_url('index.php') ?>" class="nav-brand">
                <div class="nav-logo-icon">🎓</div>
                <div class="nav-brand-text">
                    <span class="nav-brand-title">Alumni Connect</span>
                    <span class="nav-brand-sub">University Portal</span>
                </div>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= base_url('index.php') ?>" class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">Home</a></li>
                    <li><a href="<?= base_url('alumni-list.php') ?>" class="<?= ($current_page === 'alumni-list.php' || $current_page === 'alumni-view.php') ? 'active' : '' ?>">Directory</a></li>
                    <li><a href="<?= base_url('jobs/job-list.php') ?>" class="<?= (strpos($current_page, 'job') !== false) ? 'active' : '' ?>">Jobs & Referrals</a></li>
                    <li><a href="<?= base_url('notices.php') ?>" class="<?= ($current_page === 'notices.php') ? 'active' : '' ?>">Notices</a></li>
                    <li><a href="<?= base_url('event-contribute.php') ?>" class="<?= ($current_page === 'event-contribute.php') ? 'active' : '' ?>">Event Funding</a></li>

                    <?php if (is_logged_in()): ?>
                        <?php if ($user['role'] === 'student'): ?>
                            <li><a href="<?= base_url('student-dashboard.php') ?>" class="<?= ($current_page === 'student-dashboard.php') ? 'active' : '' ?>">Dashboard</a></li>
                            <li><a href="<?= base_url('my-applications.php') ?>" class="<?= ($current_page === 'my-applications.php') ? 'active' : '' ?>">My Applications</a></li>
                        <?php elseif ($user['role'] === 'alumni'): ?>
                            <li><a href="<?= base_url('alumni-dashboard.php') ?>" class="<?= ($current_page === 'alumni-dashboard.php') ? 'active' : '' ?>">Dashboard</a></li>
                            <li><a href="<?= base_url('jobs/job-add.php') ?>" class="<?= ($current_page === 'job-add.php') ? 'active' : '' ?>">+ Post Job</a></li>
                        <?php elseif ($user['role'] === 'admin'): ?>
                            <li><a href="<?= base_url('admin/dashboard.php') ?>" class="<?= (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? 'active' : '' ?>">Admin Panel</a></li>
                            <li><a href="<?= base_url('admin/promote.php') ?>">Promote Student</a></li>
                        <?php endif; ?>

                        <li style="margin-left: 8px;">
                            <div class="nav-user">
                                <div class="nav-user-avatar">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <a href="<?= base_url('profile.php') ?>" class="nav-user-name" title="View Profile"><?= e($user['name']) ?></a>
                                <span class="nav-role-badge <?= e($user['role']) ?>"><?= e($user['role']) ?></span>
                                <a href="<?= base_url('logout.php') ?>" class="btn btn-sm btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; margin-left: 4px;">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li style="margin-left: 10px;"><a href="<?= base_url('login.php') ?>" class="btn btn-sm btn-secondary">Sign In</a></li>
                        <li><a href="<?= base_url('register.php') ?>" class="btn btn-sm btn-primary">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <main class="main-content">
        <!-- Render any pending flash messages -->
        <?php display_flash(); ?>
