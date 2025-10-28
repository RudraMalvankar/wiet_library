<?php
/**
 * Admin Directory Index
 * Redirects to login page to prevent directory listing
 */

// Redirect to login page
header('Location: login.php');
exit();
