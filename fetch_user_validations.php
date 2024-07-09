<?php
session_start();
include("dbconn.php");

if (isset($_GET['userId'])) {
    $user_id = $_GET['userId'];

    // Fetch user data from the database
    $query = "SELECT fname, lname, gender, birthdate, address, cnumber, email, validId, selfiepic FROM user WHERE user_id = ?";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($fname, $lname, $gender, $birthdate, $address, $cnumber, $email, $validId, $selfiepic);

        // Check if data was fetched successfully
        if ($stmt->fetch()) {
            $stmt->close();

            // Return user data as JSON
            echo json_encode([
                'fname' => $fname,
                'lname' => $lname,
                'gender' => $gender,
                'birthdate' => $birthdate,
                'address' => $address,
                'cnumber' => $cnumber,
                'email' => $email,
                'validId' => base64_encode($validId), // Convert binary data to base64 for image display
                'selfiepic' => base64_encode($selfiepic) // Convert binary data to base64 for image display
            ]);
        } else {
            // Handle the case where user data retrieval failed
            echo json_encode(['error' => 'Error fetching user data']);
        }
    } else {
        // Handle the case where the prepare statement failed
        echo json_encode(['error' => 'Error preparing statement']);
    }
} else {
    echo json_encode(['error' => 'User ID not provided']);
}
?>
