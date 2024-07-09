<?php
session_start();
include("dbconn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $_POST['fname'];
    $lastname = $_POST['lname'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $contactnumber = $_POST['cnumber'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirm_password'];

    if (!empty($email) && !empty($password) && !is_numeric($email) && $password == $confirmpassword) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO auctioneer (fname, lname, gender, birthdate, cnumber, email, password, dateCreated) VALUES ('$firstname', '$lastname', '$gender', '$birthdate', '$contactnumber', '$email', '$hashed_password', NOW())";
        $result = mysqli_query($con, $query);

        if ($result) {
            echo "<script type='text/javascript'> alert('Successfully Registered')</script>";
        } else {
            echo "<script type='text/javascript'> alert('Error: " . mysqli_error($con) . "')</script>";
        }
    } else {
        echo "<script type='text/javascript'> alert('Please provide valid information')</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BidMo</title>
        <link rel="stylesheet" href="logreg.css">
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
            <h1>Auctioneer Registration</h1>
            <h4>Welcome to BidMo</h4>
            <form method="POST">
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
                
                <button type="submit">Register</button>
            </form>
            <p>By clicking the Register button, you agree to our</p>
            <p><a href="#">Terms and Condition</a> and <a href="#">Privacy Policy</a></p>
            <p>Already have an account? <a href="login.php">Log In here</a></p>
        </div>
        <script>
            function toggleDropdown() {
                var dropdown = document.getElementById("user-dropdown");
                dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
            }
        </script>
        <footer>
            <small>Copyright &copy; 2023 BidMo. All rights reserved.</small> <br>
            <a href="login.php" target="_blank">www.bidmo.com</a>
        </footer>
        <script src="carousel.js"></script>
    </body>
</html>