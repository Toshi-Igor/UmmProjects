<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log('Received AJAX request in update_item.php');

session_start();
include("dbconn.php");

// Get the values from the FormData
$itemId = $_POST['itemId'];
$itemName = $_POST['itemName'];
$description = $_POST['description'];
$startingPrice = $_POST['startingPrice'];

// Check if the keys are set before accessing them
$startDateTime = isset($_POST['startDateTime']) ? $_POST['startDateTime'] : null;
$endDateTime = isset($_POST['endDateTime']) ? $_POST['endDateTime'] : null;

// Convert combined date and time strings to DateTime objects
$startDateTime = ($startDateTime) ? new DateTime($startDateTime) : null;
$endDateTime = ($endDateTime) ? new DateTime($endDateTime) : null;

// Extract date and time components
$start_date = ($startDateTime) ? $startDateTime->format('Y-m-d') : null;
$start_time = ($startDateTime) ? $startDateTime->format('H:i:s') : null;
$end_date = ($endDateTime) ? $endDateTime->format('Y-m-d') : null;
$end_time = ($endDateTime) ? $endDateTime->format('H:i:s') : null;

// Add more detailed logging
error_log('Received data from AJAX request:');
error_log('Item ID: ' . $itemId);
error_log('Item Name: ' . $itemName);
error_log('Description: ' . $description);
error_log('Starting Price: ' . $startingPrice);
error_log('Start Date: ' . $start_date);
error_log('Start Time: ' . $start_time);
error_log('End Date: ' . $end_date);
error_log('End Time: ' . $end_time);

$sql = "UPDATE items SET
        item_name = ?,
        description = ?,
        starting_price = ?,
        start_date = ?,
        start_time = ?,
        end_date = ?,
        end_time = ?
        WHERE item_id = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("ssdssssi", $itemName, $description, $startingPrice, $start_date, $start_time, $end_date, $end_time, $itemId);

$con->begin_transaction();

if ($stmt->execute()) {
    $con->commit();
    echo "Item updated successfully";
    error_log("Item updated successfully for Item ID: " . $itemId);
} else {
    $con->rollback();
    echo "Error updating item: " . $stmt->error;
    error_log("Error updating item: " . $stmt->error);
}

$stmt->close();
?>
