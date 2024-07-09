<?php
// Check if the user is not logged in
if (!isset($_SESSION['auctioneer_id']) && !isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header("location: index.php");
    exit();
}
?>
