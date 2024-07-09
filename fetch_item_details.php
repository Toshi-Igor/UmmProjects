<?php
include("dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['itemId'])) {
    $item_id = $_GET['itemId'];


    // Fetch item details
    $query_fetch_item_details = "SELECT item_id, item_name, description, starting_price, start_date, start_time, end_date, end_time, item_picture FROM items WHERE item_id = ?";
    $stmt_fetch_item_details = $con->prepare($query_fetch_item_details);

    if ($stmt_fetch_item_details) {
        $stmt_fetch_item_details->bind_param("i", $item_id);

        if ($stmt_fetch_item_details->execute()) {
            $stmt_fetch_item_details->bind_result($item_id, $item_name, $description, $starting_price, $start_date, $start_time, $end_date, $end_time, $item_picture);
            $stmt_fetch_item_details->fetch();
            $stmt_fetch_item_details->close();

            // Fetch latest bid information
            $query_fetch_latest_bid = "SELECT bid_amount, user.fname, user.lname, user.cnumber
                           FROM bids 
                           JOIN user ON bids.user_id = user.user_id 
                           WHERE item_id = ? 
                           ORDER BY bid_date DESC, bid_time DESC 
                           LIMIT 1";

            $stmt_fetch_latest_bid = $con->prepare($query_fetch_latest_bid);

            if ($stmt_fetch_latest_bid) {
                $stmt_fetch_latest_bid->bind_param("i", $item_id);

                if ($stmt_fetch_latest_bid->execute()) {
                    $stmt_fetch_latest_bid->bind_result($latest_bid_amount, 
                                                        $latest_bidder_fname, 
                                                        $latest_bidder_lname, 
                                                        $latest_bidder_cnumber);
                    $stmt_fetch_latest_bid->fetch();
                    $stmt_fetch_latest_bid->close();

                    // Prepare the response as an associative array
                    $response = [
                        'item_id' => $item_id,
                        'item_name' => $item_name,
                        'description' => $description,
                        'starting_price' => $starting_price,
                        'start_date' => $start_date,
                        'start_time' => $start_time,
                        'end_date' => $end_date,
                        'end_time' => $end_time,
                        'item_picture' => base64_encode($item_picture), 
                        'latest_bid_amount' => $latest_bid_amount,
                        'latest_bidder_fname' => $latest_bidder_fname,
                        'latest_bidder_lname' => $latest_bidder_lname,
                        'latest_bidder_cnumber' => $latest_bidder_cnumber,
                    ];

                    // Send JSON response
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
        }
    }
}

// If execution reaches here, there was an error
http_response_code(500); // Internal Server Error
echo json_encode(['error' => 'Error fetching item details']);
?>
