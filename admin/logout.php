<?php
/**
 * Admin Logout Page
 * Destroys session and redirects to login
 */

require_once 'auth_system.php';

// Destroy the session
destroyAdminSession();

// Redirect to login page
header('Location: login.php');
exit();
