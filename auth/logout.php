<?php
// Logout functionality
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Destroy the session
destroySession();

// Redirect to main page with success message
setSuccess('You have been successfully logged out.');
redirect(SITE_URL . '/index.php');
?>