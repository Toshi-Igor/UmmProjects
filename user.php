<?php
session_start();
include("dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $contactnumber = $_POST['cnumber'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirm_password'];

    // File upload handling
    $validId = file_get_contents($_FILES['valid_id']['tmp_name']);
    $selfiepic = file_get_contents($_FILES['selfie']['tmp_name']);

    // Check if email already exists
    $emailCheckQuery = "SELECT COUNT(*) FROM user WHERE email = ?";
    $stmt = $con->prepare($emailCheckQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($emailCount);
    $stmt->fetch();
    $stmt->close();

    if (!empty($email) && !empty($password) && !is_numeric($email) && $password == $confirmpassword && $emailCount == 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user data including uploaded images
        $insertQuery = "INSERT INTO user (fname, lname, gender, validId, selfiepic, birthdate, address, cnumber, email, password, dateCreated, timeCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtInsert = $con->prepare($insertQuery);
        $stmtInsert->bind_param("ssssssssss", $firstname, $lastname, $gender, $validId, $selfiepic, $birthdate, $address, $contactnumber, $email, $hashed_password);

        if ($stmtInsert->execute()) {
            $userId = $stmtInsert->insert_id;
        
            // Insert validation record with default approval status as 0
            $validationQuery = "INSERT INTO user_validations (auctioneer_id, user_id, approval_status, approval_timestamp) VALUES (1, ?, 0, NOW())";
            $stmtValidation = $con->prepare($validationQuery);
        
            if ($stmtValidation) {
                $stmtValidation->bind_param("i", $userId);
                $validationSuccess = $stmtValidation->execute();
                $stmtValidation->close();
        
                if ($validationSuccess) {
                    echo "<script type='text/javascript'> alert('Successfully Registered. Awaiting approval from auctioneer.')</script>";
                } else {
                    echo "<script type='text/javascript'> alert('Error inserting validation record: " . $con->error . "')</script>";
                }
            } else {
                echo "<script type='text/javascript'> alert('Error preparing validation statement: " . $con->error . "')</script>";
            }
        } else {
            echo "<script type='text/javascript'> alert('Error: " . $stmtInsert->error . "')</script>";
        }
    }
}
        
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BidMo</title>
        <link rel="stylesheet" href="loginreg.css">
        <link rel="icon" type="image/png" href="Image/icontab.png">
    </head>
    <body>
        <!-- Manual Carousel -->
        <div class="carousel" id="imageCarousel">
            <div class="carousel-inner">
                <div class="carousel-item">
                    <img src="Image/bidpic.jpg" alt="Slide 1">
                </div>
                <div class="carousel-item">
                    <img src="Image/bidding.jpg" alt="Slide 2">
                </div>
                <div class="carousel-item">
                    <img src="Image/bid.png" alt="Slide 3">
                </div>
                <div class="carousel-item">
                    <img src="Image/bidding2.png" alt="Slide 4">
                </div>
                <div class="carousel-item">
                    <img src="Image/bidding3.jpg" alt="Slide 5">
                </div>
            </div>

            <button id="prevBtn">&lt;</button>
            <button id="nextBtn">&gt;</button>
        </div>
        <div class="container">
            <h1>User Registration</h1>
            <h4>Welcome to BidMo</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="identity">
                    <input type="text" id="fname" name="fname" placeholder="First name" required>
                    <input type="text" id="lname" name="lname" placeholder="Last name" required>
                    <input type="text" id="gender" name="gender" placeholder="Gender" required>
                </div>

                <div class="form-group">
                    <input type="date" id="birthdate" name="birthdate" required>
                    <span class="placeholder-text">Select your birthdate</span>
                </div>

                <div class="form-group">
                    <input type="text" id="address" name="address" placeholder="Address" required>
                </div>

                <div class="form-group">
                    <input type="tel" id="cnumber" name="cnumber" placeholder="Cellphone number" required>
                </div>
                
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Email address" required>
                </div>
                
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                </div>
                
                <div class="form-group">
                    <label for="valid_id">Upload Valid ID:</label>
                    <input type="file" id="valid_id" name="valid_id" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="selfie">Upload Selfie:</label>
                    <input type="file" id="selfie" name="selfie" accept="image/*" required>
                </div>
                
                <button type="submit">Register</button>
            </form>
            <p>By clicking the Register button, you agree to our</p>
            <p><a href="tandc.html">Terms and Condition</a> and <a href="privacy_policy.html">Privacy Policy</a></p>
            <p>Already have an account? <a href="index.php">Log In here</a></p>
        </div>
        <script>
            function toggleDropdown() {
                var dropdown = document.getElementById("user-dropdown");
                dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
            }
        </script>
        <footer>
            <small>Copyright &copy; 2023 BidMo. All rights reserved.</small> <br>
            <a href="index.php" target="_blank">www.bidmo.com</a>
        </footer>
        <script src="carousel.js"></script>
    </body>
</html>
