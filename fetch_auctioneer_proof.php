<?php
include("dbconn.php");

// Fetch auctioneer proof pictures and descriptions from the database
$query = "SELECT proofpic, trans_description FROM transaction_pictures WHERE auctioneer_id = ?";
$auctioneer_id = 1; // Replace '1' with the actual auctioneer ID for whom you want to fetch proof pictures
$stmt = $con->prepare($query);

if ($stmt) {
    $stmt->bind_param("i", $auctioneer_id);
    $stmt->execute();
    $stmt->bind_result($proofpic, $description);

    $proof_pictures = array();
    while ($stmt->fetch()) {
        // Assuming you want to return the proof pictures as base64 encoded strings
        $proof_pictures[] = array(
            'proofpic' => base64_encode($proofpic),
            'description' => $description
        );
    }

    $stmt->close();

    // Return the proof pictures with descriptions as JSON response
    echo json_encode($proof_pictures);
} else {
    echo "Failed to prepare the statement";
}

// Close database connection
mysqli_close($con);
?>