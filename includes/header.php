<?php
// =======================================================
// BCAC591: Reusable Header & Navigation Bar
// =======================================================

require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' | Alumni Connect' : 'Alumni Connect Portal' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <!-- Main Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="<?= base_url('index.php') ?>" class="nav-brand">
                <span class="logo-badge">🎓</span>
                <span>Alumni Connect</span>
            </a>

            <nav>
                <ul class="nav-menu">
                    <li><a href="<?= base_url('index.php') ?>">Home</a></li>
                    <li><a href="<?= base_url('notices.php') ?>">Notices</a></li>
                    <li><a href="<?= base_url('jobs/job-list.php') ?>">Jobs & Drives</a></li>
                    <li><a href="<?= base_url('alumni-list.php') ?>">Alumni Directory</a></li>
                    <li><a href="<?= base_url('event-contribute.php') ?>">Event Funding</a></li>

                    <?php if (is_logged_in()): ?>
                        <?php if ($user['role'] === 'student'): ?>
                            <li><a href="<?= base_url('student-dashboard.php') ?>">My Dashboard</a></li>
                            <li><a href="<?= base_url('my-applications.php') ?>">Applications</a></li>
                        <?php elseif ($user['role'] === 'alumni'): ?>
                            <li><a href="<?= base_url('alumni-dashboard.php') ?>">Alumni Dashboard</a></li>
                            <li><a href="<?= base_url('jobs/job-add.php') ?>">+ Post Job</a></li>
                        <?php elseif ($user['role'] === 'admin'): ?>
                            <li><a href="<?= base_url('admin/dashboard.php') ?>">Admin Panel</a></li>
                            <li><a href="<?= base_url('admin/promote.php') ?>">Promote Student</a></li>
                        <?php endif; ?>

                        <li class="nav-user">
                            <span class="nav-role-badge"><?= e($user['role']) ?></span>
                            <a href="<?= base_url('profile.php') ?>" title="View Profile"><?= e($user['name']) ?></a>
                            <a href="<?= base_url('logout.php') ?>" class="btn btn-sm btn-secondary">Logout</a>
                        </li>
                    <?php else: ?>
                        <li><a href="<?= base_url('login.php') ?>" class="btn btn-sm btn-secondary">Login</a></li>
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
