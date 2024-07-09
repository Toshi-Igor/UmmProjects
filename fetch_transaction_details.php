<?php
// Establish database connection
include("dbconn.php");

// Get the transaction ID from the request
$transactionId = $_GET['transactionId'];

// Query to fetch transaction details
$query = "SELECT * FROM bid_transactions WHERE transaction_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $transactionId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch the row as an associative array
$transactionDetails = mysqli_fetch_assoc($result);

// Send the transaction details as JSON response
echo json_encode($transactionDetails);

// Close the statement and database connection
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
