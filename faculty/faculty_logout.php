<?php
session_start();
session_unset();
session_destroy();
header('Location: faculty_login.php?logout=1');
exit();
?>
