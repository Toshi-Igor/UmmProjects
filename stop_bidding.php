<?php
include("dbconn.php");

// Retrieve item ID from the request
$itemId = $_GET['itemId'];

// Query to fetch the maximum bid for the item
$queryMaxBid = "SELECT MAX(bid_amount) AS max_bid FROM bids WHERE item_id = ?";
$stmtMaxBid = mysqli_prepare($con, $queryMaxBid);
mysqli_stmt_bind_param($stmtMaxBid, "i", $itemId);
mysqli_stmt_execute($stmtMaxBid);
$resultMaxBid = mysqli_stmt_get_result($stmtMaxBid);

if ($rowMaxBid = mysqli_fetch_assoc($resultMaxBid)) {
    $maxBidAmount = $rowMaxBid['max_bid'];

    // Check if there are bids for the item
    if ($maxBidAmount !== null) {
        // Query to fetch the details of the highest bid
        $queryWinnerBid = "SELECT bid_id FROM bids WHERE item_id = ? AND bid_amount = ?";
        $stmtWinnerBid = mysqli_prepare($con, $queryWinnerBid);
        mysqli_stmt_bind_param($stmtWinnerBid, "id", $itemId, $maxBidAmount);
        mysqli_stmt_execute($stmtWinnerBid);
        $resultWinnerBid = mysqli_stmt_get_result($stmtWinnerBid);

        if ($rowWinnerBid = mysqli_fetch_assoc($resultWinnerBid)) {
            $winningBidId = $rowWinnerBid['bid_id'];

            // Save the winner's bid information in the bid_winners table
            $queryInsertWinner = "INSERT INTO bid_winners (bid_id, win_date, win_time) VALUES (?, NOW(), NOW())";
            $stmtInsertWinner = mysqli_prepare($con, $queryInsertWinner);
            mysqli_stmt_bind_param($stmtInsertWinner, "i", $winningBidId);
            mysqli_stmt_execute($stmtInsertWinner);

            if (mysqli_stmt_affected_rows($stmtInsertWinner) > 0) {
                // Bidding stopped and winner identified successfully
                $message = "The bidding winner for item with ID $itemId is identified. Bid amount: $maxBidAmount";
            } else {
                // Error occurred while saving winner's information
                $message = "Error: Unable to save winner's information.";
            }
        } else {
            // No bidder found for the item
            $message = "Error: No bidder found for the item.";
        }
    } else {
        // No bids found for the item
        $message = "Error: No bids found for the item.";
    }
} else {
    // Error occurred while fetching maximum bid
    $message = "Error: Unable to fetch maximum bid for the item.";
}

// Close prepared statements and database connection
mysqli_stmt_close($stmtMaxBid);
mysqli_stmt_close($stmtWinnerBid);
mysqli_stmt_close($stmtInsertWinner);
mysqli_close($con);

// Return the message to the frontend
echo $message;
?>
