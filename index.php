<?php
session_start();
include("dbconn.php");

// Check if the user is not logged in and not on the login page
if (!isset($_SESSION['auctioneer_id']) && !isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'index.php') {
    header("location: index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if it's the pre-created auctioneer account
    if ($email == "josephnavalauctioneer@gmail.com" && $password == "josephpogi22!") {
        // Redirect to the auctioneer's page
        $_SESSION['auctioneer_id'] = 1; 
        header("location: handler.php");
        exit();
    }

    // Check in the auctioneers table
    $query_auctioneer = "SELECT auctioneer_id FROM auctioneers WHERE email = ? AND password = ?";
    $stmt_auctioneer = mysqli_prepare($con, $query_auctioneer);
    mysqli_stmt_bind_param($stmt_auctioneer, "ss", $email, $password);
    mysqli_stmt_execute($stmt_auctioneer);

    // Check if there's a result and bind the variable
    mysqli_stmt_store_result($stmt_auctioneer);
    if (mysqli_stmt_num_rows($stmt_auctioneer) > 0) {
        mysqli_stmt_bind_result($stmt_auctioneer, $auctioneerId);
        mysqli_stmt_fetch($stmt_auctioneer);
        // Login successful for auctioneer
        $_SESSION['auctioneer_id'] = $auctioneerId; 
        header("location: handler.php");
        exit();
    }

   // Check in the user_validations table
$query_user_validation = "SELECT uv.user_id, uv.approval_status, u.password FROM user_validations uv JOIN user u ON uv.user_id = u.user_id WHERE u.email = ?";
$stmt_user_validation = mysqli_prepare($con, $query_user_validation);
mysqli_stmt_bind_param($stmt_user_validation, "s", $email);
mysqli_stmt_execute($stmt_user_validation);

$result_user_validation = mysqli_stmt_get_result($stmt_user_validation);

if ($result_user_validation && $user_validation = mysqli_fetch_assoc($result_user_validation)) {
    // Check if the account is approved
    if ($user_validation['approval_status'] == 1) {
        // Verify the hashed password
        if (password_verify($password, $user_validation['password'])) {
            // Login successful for an approved regular user
            $_SESSION['user_id'] = $user_validation['user_id'];
            header("location: home.php");
            exit();
        } else {
            echo "Password verification failed for a regular user.";
        }
    } else {
        echo "<script type='text/javascript'> alert('Your account is pending approval. Please wait for confirmation.')</script>";
    }
}

echo "<script type='text/javascript'> alert('Invalid username/password or unapproved account! Please try again.')</script>";

    if ($email == "bidmoadmin@gmail.com" && $password == "bidmo") {
        // Redirect to the CMS admin's page
        $_SESSION['admin_id'] = 1; // Assign some admin ID
        header("location: cms.php");
        exit();
    }

    // Check in the cms_admins table
    $query_admin = "SELECT admin_id FROM cms_admins WHERE email = ? AND password = ?";
    $stmt_admin = mysqli_prepare($con, $query_admin);
    mysqli_stmt_bind_param($stmt_admin, "ss", $email, $password);
    mysqli_stmt_execute($stmt_admin);


    // Check if there's a result and bind the variable
    mysqli_stmt_store_result($stmt_admin);
    if (mysqli_stmt_num_rows($stmt_admin) > 0) {
        mysqli_stmt_bind_result($stmt_admin, $adminId);
        mysqli_stmt_fetch($stmt_admin);
        // Login successful for CMS admin
        $_SESSION['admin_id'] = $adminId; // Set session here
        header("location: cms.php");
        exit();
    }

    // Invalid username/password or unapproved account
    echo "<script type='text/javascript'> alert('Invalid username/password or unapproved account! Please try again.')</script>";

    // Close prepared statements
    mysqli_stmt_close($stmt_auctioneer);
    mysqli_close($con);


}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        <h1>Login</h1>
        <h4>Welcome to BidMo</h4>
        <form method="POST">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="email" id="email" name="email" placeholder="Email address" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <img src="Image/eyeclose.png" alt="" id="eye">
            </div>

            <button type="submit">Log In</button>
        </form>
        <p>By clicking the Register button, you agree to our</p>
        <p><a href="tandc.html">Terms and Condition</a> and <a href="privacy_policy.html">Privacy Policy</a></p>
        <p>You don't have an account? <a href="user.php">Register here</a></p>
    </div>
    <footer>
        <small>Copyright &copy; 2023 BidMo. All rights reserved.</small> <br>
        <a href="login.php" target="_blank">www.bidmo.com</a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let eye = document.getElementById("eye");
            let password = document.getElementById("password");

            eye.addEventListener("click", function () {
                if (password.type === "password") {
                    password.type = "text";
                    eye.src = "Image/eyeopen.png";
                } else {
                    password.type = "password";
                    eye.src = "Image/eyeclose.png";
                }
            });
        });
    </script>
    <script src="carousel.js"></script>

</body>
</html>
