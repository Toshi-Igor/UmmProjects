<?php
// logout.php

// Start the session
session_start();

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to index.php
header("location: index.php");
exit();
?>
