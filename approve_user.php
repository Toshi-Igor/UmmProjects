<?php
session_start();
include("dbconn.php");

// Check if the auctioneer ID is set in the session
if (!isset($_SESSION['auctioneer_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized access: Auctioneer ID not set in session'));
    exit();
}

// Function to approve a user
function approveUser($con, $userId) {
    $query = "UPDATE user_validations SET approval_status = 1, approval_timestamp = NOW() WHERE user_id = ?";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $success = $stmt->execute();
        $stmt->close();

        return array('success' => $success, 'error' => (!$success) ? $con->error : '');
    } else {
        return array('success' => false, 'error' => $con->error);
    }
}

// Check if the user is an auctioneer
if ($_SESSION['auctioneer_id'] == 1) {
    // Auctioneer is an approver, handle user approval actions
    if (isset($_GET['user_id'])) {
        $userIdToApprove = $_GET['user_id'];
        $result = approveUser($con, $userIdToApprove);
        echo json_encode($result);
        exit();
    } else {
        echo json_encode(array('success' => false, 'error' => 'User ID not provided for approval'));
        exit();
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized access: Not an auctioneer'));
    exit();
}
?>
