<?php
include("dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['itemId'])) {
    $item_id = $_GET['itemId'];

    // Fetch all bids for the item
    $query_fetch_bids = "SELECT bid_amount, user.fname, user.lname, user.cnumber, 
                                user.email, user.address, bid_date, bid_time
                         FROM bids 
                         JOIN user ON bids.user_id = user.user_id 
                         WHERE item_id = ?
                         ORDER BY bid_date DESC, bid_time DESC";

    $stmt_fetch_bids = $con->prepare($query_fetch_bids);

    if ($stmt_fetch_bids) {
        $stmt_fetch_bids->bind_param("i", $item_id);

        if ($stmt_fetch_bids->execute()) {
            $result_bids = $stmt_fetch_bids->get_result();
            $bids = $result_bids->fetch_all(MYSQLI_ASSOC);
            $stmt_fetch_bids->close();

            // Prepare the response as an associative array
            $response = [
                'bids' => $bids,
            ];

            // Send JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
}

// If execution reaches here, there was an error
http_response_code(500); // Internal Server Error
echo json_encode(['error' => 'Error fetching bids']);
?>
