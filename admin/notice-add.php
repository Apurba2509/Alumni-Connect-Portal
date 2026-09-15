<?php
// =======================================================
// BCAC591: Admin Notice Add Shortcut
// Redirects to the Notice Publishing panel
// =======================================================

require_once __DIR__ . '/../includes/functions.php';

header('Location: ' . base_url('admin/notices.php'));
exit();
