<?php
session_start();
include("dbconn.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in or is not a regular user
    http_response_code(401); // Unauthorized
    exit(); // Stop further execution
}

// Get the user ID
$userID = $_SESSION['user_id'];

// Get the old password, new password, and confirm new password from the POST data
$oldPassword = $_POST['oldPassword'];
$newPassword = $_POST['newPassword'];

// Validate old password
$query = "SELECT password FROM user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hashedPassword);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Verify the old password
    if (password_verify($oldPassword, $hashedPassword)) {
        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $updateQuery = "UPDATE user SET password = ? WHERE user_id = ?";
        $updateStmt = mysqli_prepare($con, $updateQuery);

        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, "si", $hashedNewPassword, $userID);
            if (mysqli_stmt_execute($updateStmt)) {
                // Password successfully updated
                http_response_code(200); // OK
                echo "success";
            } else {
                // Error updating password
                http_response_code(500); // Internal Server Error
                echo "error";
            }
            mysqli_stmt_close($updateStmt);
        } else {
            // Error preparing update statement
            http_response_code(500); // Internal Server Error
            echo "error";
        }
    } else {
        // Old password does not match
        http_response_code(400); // Bad Request
        echo "invalid_old_password";
    }
} else {
    // Error preparing statement
    http_response_code(500); // Internal Server Error
    echo "error";
}

// Close database connection
mysqli_close($con);
?>
