<?php
session_start();

// Destroy ALL session data on logout
// This clears the login badge completely
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
