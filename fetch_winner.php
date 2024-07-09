<?php
// Establish database connection
include("dbconn.php");

// Retrieve item ID from the request
$itemId = $_GET['itemId'];

// Query to fetch the winner's information
$queryWinnerInfo = "SELECT bw.*, b.bid_amount, u.user_id, u.fname, u.lname, u.cnumber, u.address, u.email, i.item_name, 
i.description, i.item_picture, a.fname as a_fname, a.lname as a_lname, a.email as a_email, a.address as a_address, a.cnumber as a_cnumber, bt.transaction_type, bt.status, bt.transaction_id
                    FROM bid_winners bw 
                    JOIN bids b ON bw.bid_id = b.bid_id 
                    JOIN user u ON b.user_id = u.user_id 
                    JOIN items i ON b.item_id = i.item_id 
                    JOIN auctioneers a ON i.auctioneer_id = a.auctioneer_id
                    LEFT JOIN bid_transactions bt ON bw.winner_id = bt.winner_id
                    WHERE i.item_id = ?
                    ORDER BY b.bid_amount DESC
                    LIMIT 1";

$stmtWinnerInfo = mysqli_prepare($con, $queryWinnerInfo);
mysqli_stmt_bind_param($stmtWinnerInfo, "i", $itemId);
mysqli_stmt_execute($stmtWinnerInfo);
$resultWinnerInfo = mysqli_stmt_get_result($stmtWinnerInfo);

if ($rowWinnerInfo = mysqli_fetch_assoc($resultWinnerInfo)) {
    // Winner found, prepare JSON response
    $winnerInfo = array(
        'user_id' => $rowWinnerInfo['user_id'],
        'fname' => $rowWinnerInfo['fname'],
        'lname' => $rowWinnerInfo['lname'],
        'cnumber' => $rowWinnerInfo['cnumber'],
        'address' => $rowWinnerInfo['address'],
        'email' => $rowWinnerInfo['email'],
        'item_name' => $rowWinnerInfo['item_name'],
        'description' => $rowWinnerInfo['description'],
        'item_picture' => base64_encode($rowWinnerInfo['item_picture']),
        'bid_amount' => $rowWinnerInfo['bid_amount'],
        'win_date' => $rowWinnerInfo['win_date'],
        'win_time' => $rowWinnerInfo['win_time'],
        'a_fname' => $rowWinnerInfo['a_fname'],
        'a_lname' => $rowWinnerInfo['a_lname'],
        'a_email' => $rowWinnerInfo['a_email'],
        'a_address' => $rowWinnerInfo['a_address'],
        'a_cnumber' => $rowWinnerInfo['a_cnumber'],
        'transaction_type' => $rowWinnerInfo['transaction_type'],
        'status' => $rowWinnerInfo['status'],
        'transaction_id' => $rowWinnerInfo['transaction_id']
    );

    // Send the winner's information as JSON response
    echo json_encode($winnerInfo);
} else {
    // No winner found, return error message
    echo json_encode(array('error' => 'No winner found for the item.'));
}

// Close prepared statement and database connection
mysqli_stmt_close($stmtWinnerInfo);
mysqli_close($con);
?>
