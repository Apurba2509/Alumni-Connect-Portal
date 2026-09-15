<?php
// =======================================================
// BCAC591: Admin Directory Entry Point
// Redirects directly to the Admin Dashboard
// =======================================================

require_once __DIR__ . '/../includes/functions.php';

header('Location: ' . base_url('admin/dashboard.php'));
exit();
