<?php
session_start();
include("dbconn.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'C:/xampp/htdocs/bidmo/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/bidmo/PHPMailer/src/SMTP.php';
require 'C:/xampp/htdocs/bidmo/PHPMailer/src/Exception.php';

// PHP code to handle the bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Get user ID from the session
    $user_id = $_SESSION['user_id'];

    // Get bid amount and item ID from the POST data
    $bid_amount = filter_var($_POST['bidAmount'], FILTER_VALIDATE_FLOAT);
    $item_id = filter_var($_POST['itemId'], FILTER_VALIDATE_INT);

    // Check if the bid amount is greater than the latest bid amount for the item
    $latest_bid_query = "SELECT MAX(bid_amount) FROM bids WHERE item_id = ?";
    $stmt_latest_bid = $con->prepare($latest_bid_query);

    if ($stmt_latest_bid) {
        $stmt_latest_bid->bind_param("i", $item_id);
        $stmt_latest_bid->execute();
        $stmt_latest_bid->bind_result($latest_bid);
        $stmt_latest_bid->fetch();
        $stmt_latest_bid->close();

        if ($latest_bid !== null && $bid_amount <= $latest_bid) {
            // The bid amount is less than or equal to the latest bid amount
            echo json_encode(['success' => false, 'message' => 'The amount placed is less than or equal to the latest bid amount']);
            exit;
        }
    } else {
        // Handle the case where the prepare statement for the latest bid failed
        echo json_encode(['success' => false, 'message' => 'Error preparing statement for latest bid: ' . $con->error]);
        exit;
    }

    // Insert bid into the database
    $insert_bid_query = "INSERT INTO bids (user_id, item_id, bid_amount, bid_date, bid_time) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt_insert_bid = $con->prepare($insert_bid_query);

    if ($stmt_insert_bid) {
        $stmt_insert_bid->bind_param("iid", $user_id, $item_id, $bid_amount);

        if ($stmt_insert_bid->execute()) {
            // Bid successfully inserted

            sendEmailNotificationsAuctioneers($con, $user_id, $item_id, $bid_amount);
            // Send email notifications
            sendEmailNotifications($con, $user_id, $item_id, $bid_amount);

            echo json_encode(['success' => true, 'message' => 'Bid submitted successfully']);
        } else {
            // Handle execution error for bid insertion
            echo json_encode(['success' => false, 'message' => 'Error executing statement for bid insertion: ' . $stmt_insert_bid->error]);
        }

        // Close the statement for bid insertion
        $stmt_insert_bid->close();
    } else {
        // Handle the case where the prepare statement for bid insertion failed
        echo json_encode(['success' => false, 'message' => 'Error preparing statement for bid insertion: ' . $con->error]);
    }
}

function sendEmailNotifications($con, $user_id, $item_id, $bid_amount) {
    // Fetch the item name
    $item_name = getItemName($con, $item_id);

    // Fetch the users whose bids have been surpassed
    $query_fetch_surpassed_users = "SELECT DISTINCT u.email 
                                    FROM bids b
                                    JOIN user u ON b.user_id = u.user_id
                                    WHERE b.item_id = ? AND b.bid_amount < ?";
    $stmt_fetch_surpassed_users = $con->prepare($query_fetch_surpassed_users);
    $stmt_fetch_surpassed_users->bind_param("id", $item_id, $bid_amount);
    $stmt_fetch_surpassed_users->execute();
    $result_surpassed_users = $stmt_fetch_surpassed_users->get_result();

    // Send email notifications
    while ($row = $result_surpassed_users->fetch_assoc()) {
        $email = $row['email'];

        // Create a new PHPMailer instance
        $mail = new PHPMailer();

        // Configure PHPMailer
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kazeynaval0329@gmail.com'; // Your Gmail address
        $mail->Password = 'htszjykecyxlclhg'; // Your Gmail password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('kazeynaval0329@gmail.com', 'Bidmo'); // Sender's email and name
        $mail->addAddress($email); // Recipient's email

        $mail->isHTML(true);
        $mail->Subject = 'Your bid has been surpassed';
        $mail->Body = 'Your bid has been surpassed by a new bid of ' . $bid_amount . ' for item: ' . $item_name;

        // Send the email
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    $stmt_fetch_surpassed_users->close();
}

function sendEmailNotificationsAuctioneers($con, $user_id, $item_id, $bid_amount) {
    // Fetch the item name
    $item_name = getItemName($con, $item_id);

    // Fetch user's name
    $user_name = getUserName($con, $user_id);

    // Get auctioneer details
    $query_auctioneer = "SELECT email FROM auctioneers WHERE is_approver = 1";
    $stmt_auctioneer = $con->prepare($query_auctioneer);
    $stmt_auctioneer->execute();
    $result_auctioneer = $stmt_auctioneer->get_result();

    // Send email notifications to auctioneers
    while ($row_auctioneer = $result_auctioneer->fetch_assoc()) {
        $auctioneer_email = $row_auctioneer['email'];

        // Create a new PHPMailer instance
        $mail = new PHPMailer();

        // Configure PHPMailer
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kazeynaval0329@gmail.com'; // Your Gmail address
        $mail->Password = 'htszjykecyxlclhg'; // Your Gmail password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('kazeynaval0329@gmail.com', 'Bidmo'); // Sender's email and name
        $mail->addAddress('shilohjae19@gmail.com'); // Recipient's email

        $mail->isHTML(true);
        $mail->Subject = 'New bid on your item';
        $mail->Body = 'A new bid of ' . $bid_amount . ' has been placed on your item: ' . $item_name . ' by ' . $user_name;

        // Send the email
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }

    $stmt_auctioneer->close();
}

function getItemName($con, $item_id) {
    $item_name = "";

    // Fetch the item name
    $query_fetch_item = "SELECT item_name FROM items WHERE item_id = ?";
    $stmt_fetch_item = $con->prepare($query_fetch_item);
    $stmt_fetch_item->bind_param("i", $item_id);
    $stmt_fetch_item->execute();
    $stmt_fetch_item->bind_result($item_name);
    $stmt_fetch_item->fetch();
    $stmt_fetch_item->close();

    return $item_name;
}

function getUserName($con, $user_id) {
    $user_fname = "";

    // Fetch the user's first name
    $query_fetch_fname = "SELECT fname FROM user WHERE user_id = ?";
    $stmt_fetch_fname = $con->prepare($query_fetch_fname);
    
    if ($stmt_fetch_fname) {
        $stmt_fetch_fname->bind_param("i", $user_id);
        $stmt_fetch_fname->execute();
        $stmt_fetch_fname->bind_result($user_fname);
        $stmt_fetch_fname->fetch();
        $stmt_fetch_fname->close();
    } else {
        // Handle the case where the prepare statement fails
        echo "Error preparing statement: " . $con->error;
    }

    return $user_fname;
}
?>