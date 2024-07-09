<?php
// Establish database connection
include("dbconn.php");

// Get the JSON data sent from the JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Prepare and execute the SQL query to update the transaction status
$query = "UPDATE bid_transactions SET status = ? WHERE transaction_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "si", $data['status'], $data['transactionId']);
mysqli_stmt_execute($stmt);

// Check if the update was successful
if (mysqli_stmt_affected_rows($stmt) > 0) {
    // Send a success message as JSON response
    echo json_encode(array('message' => 'Status updated successfully'));
} else {
    // Send an error message as JSON response
    echo json_encode(array('message' => 'Error updating status'));
}

// Close the statement and database connection
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
