<?php
// =======================================================
// BCAC591: Route Authentication & Role Guards
// =======================================================

require_once __DIR__ . '/functions.php';

/**
 * Require user to be logged in to view page
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access this page.');
        header('Location: ' . base_url('login.php'));
        exit();
    }
}

/**
 * Require user to possess a specific role (or one of multiple allowed roles)
 */
function require_role($allowed_roles) {
    require_login();
    
    if (!has_role($allowed_roles)) {
        set_flash('error', 'Access denied. You do not have permission to access that section.');
        
        // Redirect to their own dashboard
        $role = $_SESSION['user_role'] ?? 'student';
        if ($role === 'admin') {
            header('Location: ' . base_url('admin/dashboard.php'));
        } elseif ($role === 'alumni') {
            header('Location: ' . base_url('alumni-dashboard.php'));
        } else {
            header('Location: ' . base_url('student-dashboard.php'));
        }
        exit();
    }
}
