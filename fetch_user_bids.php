<?php
session_start();
include("dbconn.php");

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user's bid history from the database
    $query = "SELECT items.item_name, items.description, bids.bid_amount, bids.bid_date, bids.bid_time
              FROM bids
              JOIN items ON bids.item_id = items.item_id
              WHERE bids.user_id = ?
              ORDER BY bid_date DESC, bid_time DESC";

    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $bidHistory = array();

        while ($row = $result->fetch_assoc()) {
            $bidHistory[] = $row;
        }

        // Close the statement
        $stmt->close();

        // Return bid history as JSON
        echo json_encode($bidHistory);
    } else {
        // Handle the case where the prepare statement failed
        echo json_encode(['error' => 'Error preparing statement: ' . $con->error]);
    }
} else {
    echo json_encode(['error' => 'User not logged in']);
}
?>
