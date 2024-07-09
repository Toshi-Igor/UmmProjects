<?php
// Include your database connection code (dbconn.php or similar)
include("dbconn.php");

// Function to fetch user details
function fetchUserDetails($con) {
    $query = "SELECT `fname`, `lname`, `gender`, `birthdate`, `address`, `cnumber`, `email`, `dateCreated`, `timeCreated` FROM `user`";
    $result = mysqli_query($con, $query);

    if (!$result) {
        // Return an error response if the query fails
        return array('error' => 'Failed to fetch user details: ' . mysqli_error($con));
    }

    $users = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    if (empty($users)) {
        // Return a message if no user details are found
        return array('message' => 'No user details found.');
    }

    // Return user details
    return $users;
}

// Fetch user details
$userDetails = fetchUserDetails($con);

// Add error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($userDetails);
?>