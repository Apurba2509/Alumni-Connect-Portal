<?php
// =======================================================
// BCAC591: Reusable Helper Functions
// =======================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape string for safe HTML output (XSS Prevention)
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get the application's base URL path dynamically
 */
function base_url($path = '') {
    // Standard path within XAMPP htdocs
    $base = '/Clg%20Project/Alumni-Connect-Portal';
    return $base . '/' . ltrim($path, '/');
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user information
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'         => $_SESSION['user_id'] ?? 0,
        'name'       => $_SESSION['user_name'] ?? '',
        'email'      => $_SESSION['user_email'] ?? '',
        'role'       => $_SESSION['user_role'] ?? 'student',
        'department' => $_SESSION['user_department'] ?? ''
    ];
}

/**
 * Check if the logged-in user has a specific role
 */
function has_role($role) {
    if (!is_logged_in()) return false;
    $current_role = $_SESSION['user_role'] ?? '';
    if (is_array($role)) {
        return in_array($current_role, $role, true);
    }
    return $current_role === $role;
}

/**
 * Set a flash message for the next request
 * @param string $type 'success' or 'error' or 'info'
 * @param string $message The message text
 */
function set_flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Render flash messages (Success / Error alerts)
 */
function display_flash() {
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $alert_class = ($type === 'success') ? 'alert-success' : (($type === 'error') ? 'alert-error' : 'alert-info');
            echo '<div class="alert ' . $alert_class . '">';
            echo '<span>' . e($message) . '</span>';
            echo '<button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>';
            echo '</div>';
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Format date nicely (e.g. 15 Oct 2026)
 */
function format_date($date_str) {
    if (empty($date_str)) return '-';
    $timestamp = strtotime($date_str);
    return date('d M Y', $timestamp);
}
