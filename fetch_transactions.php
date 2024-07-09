<?php
// Establish database connection
include("dbconn.php");

// Get the user ID from the session or wherever it's stored
$userID = $_SESSION['user_id']; // Assuming user ID is stored in session

// Query to fetch user's transactions
$query = "SELECT * FROM bid_transactions WHERE winner_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch all rows as an array
$transactions = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Send the transactions as JSON response
echo json_encode($transactions);

// Close the statement and database connection
mysqli_stmt_close($stmt);
mysqli_close($con);
?>
