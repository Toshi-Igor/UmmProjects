<?php
session_start();
include("dbconn.php");

// Check if the request method is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Get user ID from the session
    $user_id = $_SESSION['user_id'];

    // Get updated profile details from the POST data
    $fname = filter_var($_POST['fname'], FILTER_SANITIZE_STRING);
    $lname = filter_var($_POST['lname'], FILTER_SANITIZE_STRING);
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $birthdate = filter_var($_POST['birthdate'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
    $cnumber = filter_var($_POST['cnumber'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Update user profile in the database
    $query = "UPDATE user SET fname=?, lname=?, gender=?, birthdate=?, address=?, cnumber=?, email=? WHERE user_id=?";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sssssssi", $fname, $lname, $gender, $birthdate, $address, $cnumber, $email, $user_id);

        if ($stmt->execute()) {
            // Profile successfully updated
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'userData' => compact('fname', 'lname', 'gender', 'birthdate', 'address', 'cnumber', 'email')]);
        } else {
            // Handle execution error
            echo json_encode(['success' => false, 'message' => 'Error executing statement: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle the case where the prepare statement failed
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $con->error]);
    }
} else {
    // Invalid request or user not logged in
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
