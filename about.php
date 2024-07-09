<?php
session_start();
include("dbconn.php");
include("checklog.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to BidMo: Where Bidding Meets Trust</title>
    <link rel="stylesheet" href="main.css">
    <link rel="icon" type="image/png" href="Image/icontab.png">
</head>

<nav>
    <a href="home.php">Home</a>
    <a href="about.php">About</a>
</nav>


<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        background-color: #f5f5f5;
        color: #333;
    }

    li {
        text-align: justify;
    }

    p {
        text-align: justify;
    }

    body
    .container {
        max-width: 800px;
        margin: 100px auto;
        padding: 50px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    h1, h2, h3 {
        color: #312f52;
    }

    ul {
        list-style-type: disc;
        margin-left: 20px;
    }

    .contact-info {
        margin-top: 30px;
    }

    .contact-info li {
        margin-bottom: 10px;
    }


    .social-links {
        margin-top: 20px;
    }

    .social-links a {
        display: inline-block;
        margin-right: 10px;
        color: #007bff;
        text-decoration: none;
    }

    footer {
        color: #fff;
    }
</style>

<body>
    <div class="container">
        <h1 style="text-align: center;">Welcome to BidMo: Where Bidding Meets Trust</h1>

        <p style="text-indent: 5%;">At BidMo, we believe in fostering fair and trustworthy bidding transactions for all users. Our platform is dedicated to creating an environment where buyers and sellers can engage in auctions with confidence, transparency, and integrity.</p>

        <h2>Our Mission</h2>
        <p style="text-indent: 5%;">BidMo's mission is to revolutionize the bidding experience by prioritizing fairness and trust. We strive to provide a platform where every user, whether buying or selling, feels secure and respected throughout the entire transaction process.</p>

        <h2>Key Features</h2>
        <ul>
            <li><strong>Transparent Bidding:</strong> BidMo ensures that all bidding processes are transparent, providing users with real-time updates and information about ongoing auctions.</li>
            <br>
            <li><strong>Verified Sellers:</strong> We thoroughly vet sellers to ensure they meet our standards of reliability and trustworthiness. Each seller undergoes a verification process to guarantee they are legitimate and committed to ethical business practices.</li>
            <br>
            <li><strong>Secure Transactions:</strong> BidMo employs cutting-edge security measures to protect users' personal and financial information. Our platform utilizes encryption and other advanced technologies to safeguard transactions and prevent fraud.</li>
            <br>
            <li><strong>Dispute Resolution:</strong> In the rare event of a dispute, BidMo offers a fair and impartial resolution process. Our dedicated support team is available to assist users in resolving conflicts and reaching satisfactory outcomes.</li>
            <br>
            <li><strong>Community Engagement:</strong> BidMo fosters a vibrant and supportive community of buyers and sellers. Users can interact with one another, share experiences, and provide feedback to continually improve the platform.</li>
            <br>
            <li><strong>Educational Resources:</strong> We provide educational resources and guidance to help users navigate the bidding process successfully. From tips on effective bidding strategies to insights into market trends, BidMo equips users with the knowledge they need to make informed decisions.</li>
        </ul>

        <h2>Why Choose BidMo?</h2>
        <ul>
            <li><strong>Trust:</strong> We prioritize trust above all else, ensuring that every transaction on our platform is conducted fairly and honestly.</li>
            <br>
            <li><strong>Security:</strong> BidMo employs state-of-the-art security measures to protect users' sensitive information and prevent unauthorized access.</li>
            <br>
            <li><strong>Transparency:</strong> Our commitment to transparency means that users have access to all the information they need to make informed decisions.</li>
            <br>
            <li><strong>Community:</strong> Join a community of like-minded individuals who share a passion for bidding and ethical business practices.</li>
        </ul>

        <h2>Join Us Today!</h2>
        <p style="text-indent: 5%;">Experience the difference of bidding with confidence on BidMo. Whether you're a seasoned bidder or new to the world of auctions, we welcome you to join our community and discover a better way to buy and sell. Together, let's redefine the bidding experience.</p>
        
        <div class="contact-info">
            <h2>Contact Us</h2>
            <ul>
                <li>Email: <a href="#"m>bidmo@gmail.com</a></li>
                <li>Phone: 1-800-700-90</li>
                <li>Facebook: <a href="#"> BidMo Official</a></li>
                <li>Instagram: <a href="#"> BidMo_Official</a></li>
                <li>YouTube: <a href="#"> BidMo Official</a></li>
                <li>X: <a href="#"> BidMo_Official</a></li>
            </ul>
        </div>

    </div>

    <footer>
        <p style="text-align: center;">&copy; 2024 BidMo. All rights reserved.</p>
    </footer>

</body>
</html>
