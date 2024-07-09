<?php
session_start();
include("dbconn.php");

// Check if the auctioneer ID is set in the session
if (!isset($_SESSION['auctioneer_id'])) {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized access: Auctioneer ID not set in session'));
    exit();
}

// Function to decline a user
function declineUser($con, $userId) {
    // Delete user validation record
    $queryDeleteValidation = "DELETE FROM user_validations WHERE user_id = ?";
    $stmtDeleteValidation = $con->prepare($queryDeleteValidation);

    if (!$stmtDeleteValidation) {
        return array('success' => false, 'error' => $con->error);
    }

    $stmtDeleteValidation->bind_param("i", $userId);
    $successValidation = $stmtDeleteValidation->execute();
    $stmtDeleteValidation->close();

    // Delete user record
    $queryDeleteUser = "DELETE FROM user WHERE user_id = ?";
    $stmtDeleteUser = $con->prepare($queryDeleteUser);

    if (!$stmtDeleteUser) {
        return array('success' => false, 'error' => $con->error);
    }

    $stmtDeleteUser->bind_param("i", $userId);
    $successUser = $stmtDeleteUser->execute();
    $stmtDeleteUser->close();

    // Check if both deletion operations were successful
    if ($successValidation && $successUser) {
        return array('success' => true, 'error' => '');
    } else {
        return array('success' => false, 'error' => 'Error deleting user records');
    }
}

// Check if the user is an auctioneer
if ($_SESSION['auctioneer_id'] == 1) {
    // Auctioneer is an approver, handle user decline actions
    if (isset($_GET['userId'])) {
        $userIdToDecline = $_GET['userId'];
        $result = declineUser($con, $userIdToDecline);
        echo json_encode($result);
        exit();
    } else {
        echo json_encode(array('success' => false, 'error' => 'User ID not provided for decline'));
        exit();
    }
} else {
    echo json_encode(array('success' => false, 'error' => 'Unauthorized access: Not an auctioneer'));
    exit();
}
?>
