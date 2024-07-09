<?php
session_start();
include("dbconn.php"); 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database
    $query = "SELECT fname, lname, gender, birthdate, address, cnumber, email FROM user WHERE user_id = ?";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($fname, $lname, $gender, $birthdate, $address, $cnumber, $email);

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
    echo json_encode(['error' => 'User not logged in']);
}
?>
