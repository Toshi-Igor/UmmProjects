<?php
// Establish database connection
include("dbconn.php");

// Retrieve item ID and transaction type from the request
$data = json_decode(file_get_contents("php://input"), true);
$item_id = $data['item_id'];
$winner_id = $data['winner_id'];
$transaction_type = $data['transaction_type'];

// Get the latest winning bid for the specific item ID
$queryLatestWinningBid = "SELECT winner_id 
FROM bid_winners 
WHERE bid_id = (
    SELECT bid_id 
    FROM bids 
    WHERE item_id = ? 
    ORDER BY bid_amount DESC 
    LIMIT 1
) 
LIMIT 1";
$stmtLatestWinningBid = mysqli_prepare($con, $queryLatestWinningBid);
mysqli_stmt_bind_param($stmtLatestWinningBid, "i", $item_id);
mysqli_stmt_execute($stmtLatestWinningBid);
$resultLatestWinningBid = mysqli_stmt_get_result($stmtLatestWinningBid);

if ($rowLatestWinningBid = mysqli_fetch_assoc($resultLatestWinningBid)) {
    $winner_id = $rowLatestWinningBid['winner_id'];

    // Store the transaction with the latest winner ID
    $queryStoreTransaction = "INSERT INTO bid_transactions (winner_id, transaction_type, status, transaction_date, transaction_time)
                              VALUES (?, ?, 'Ongoing', CURDATE(), CURTIME())";
    $stmtStoreTransaction = mysqli_prepare($con, $queryStoreTransaction);
    mysqli_stmt_bind_param($stmtStoreTransaction, "is", $winner_id, $transaction_type);
    mysqli_stmt_execute($stmtStoreTransaction);

    // Get the transaction ID of the newly stored transaction
    $transaction_id = mysqli_insert_id($con);

    // Prepare JSON response with the transaction ID
    $response = array('transaction_id' => $transaction_id);

    // Send the response
    echo json_encode($response);
} else {
    // No winner found for the item, return error message
    echo json_encode(array('error' => 'No winner found for the item.'));
}

// Close prepared statement and database connection
mysqli_stmt_close($stmtLatestWinningBid);
mysqli_stmt_close($stmtStoreTransaction);
mysqli_close($con);
?>
