<?php
session_start();
include("dbconn.php");

// Check if the CMS admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header("location: index.php");
    exit();
}

// Function to get most popular items
function getMostPopularItems() {
    global $con;
    $query = "SELECT items.item_id, item_name, COUNT(bids.bid_id) AS bid_count
              FROM items
              LEFT JOIN bids ON items.item_id = bids.item_id
              GROUP BY items.item_id
              ORDER BY bid_count DESC
              LIMIT 5";
    $result = mysqli_query($con, $query);
    return $result;
}

// Function to get highest bids
function getHighestBids() {
    global $con;
    $query = "SELECT items.item_id, item_name, MAX(bid_amount) AS highest_bid
              FROM items
              JOIN bids ON items.item_id = bids.item_id
              GROUP BY items.item_id
              ORDER BY highest_bid DESC";
    $result = mysqli_query($con, $query);
    return $result;
}

// Function to get total bids
function getTotalBids() {
    global $con;
    $query = "SELECT COUNT(*) AS total_bids FROM bids";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total_bids'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BidMo CMS - Dashboard</title>
    <link rel="stylesheet" href="content.css">
    <link rel="icon" type="image/png" href="Image/icontab.png">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        // Define fetchUserDetails function in the global scope
        function fetchUserDetails() {
            // Your existing code here...
        }

        document.addEventListener('DOMContentLoaded', function () {
                fetchUserDetails();

                // Display Analytics by default
                document.getElementById('analyticsContainer').style.display = 'block';
                document.getElementById('userDetailsContainer').style.display = 'none';

                // Handle Analytics link click
                document.querySelector('nav a[href="cms.php"]').addEventListener('click', function (event) {
                    event.preventDefault();
                    console.log('Analytics link clicked');
                    document.getElementById('analyticsContainer').style.display = 'block';
                    document.getElementById('userDetailsContainer').style.display = 'none';
                });

                // Handle Users link click
                document.querySelector('nav a[href="javascript:void(0);"]').addEventListener('click', function (event) {
                    event.preventDefault();
                    console.log('Users link clicked');
                    document.getElementById('analyticsContainer').style.display = 'none';
                    document.getElementById('userDetailsContainer').style.display = 'block';
                    document.getElementById('userDetails').style.display = 'block';
                    fetchUserDetails();
                });
            });

            document.getElementById('user-tab').addEventListener('click', toggleDropdown);

            // Add this part to close the dropdown when clicking outside of it
            document.addEventListener('click', function (event) {
                var userTab = document.getElementById('user-tab');
                var dropdown = document.getElementById('user-dropdown');

                if (!userTab.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });

            function toggleDropdown() {
                var dropdown = document.getElementById('user-dropdown');
                dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            }
    </script>
</head>

<body>  
    <header>
            <div class="logo">
                <img src="Image/BidMo_Logo.png" alt="Website Logo">
            </div>
            <div class="header-content"> 
            <h1>BidMo Admin Dashboard</h1>
            <h3><strong>Current Status Update</strong></h3>
            </div>

            <?php
            // Check if the user is logged in
            if (isset($_SESSION['admin_id'])) {
                $admin_id = $_SESSION['admin_id'];

                // Fetch user information from the database
                $query = "SELECT fname FROM cms_admins WHERE admin_id = ?";
                $stmt = $con->prepare($query);

                // Check if the prepare statement was successful
                if ($stmt) {
                    $stmt->bind_param("i", $admin_id);
                    $stmt->execute();
                    $stmt->bind_result($fname);
                    $stmt->fetch();
                    $stmt->close();

                    $greeting = (!empty($fname)) ? "Hi, $fname" : "Hi, User";

                    echo <<<HTML
                    <div id="logged-in-name">$greeting</div>
                    <div id="user-tab">⚙️</div>
                    <!-- User dropdown menu -->
                    <div id="user-dropdown">
                        <a href="#" onclick="logout()">Logout</a>
                    </div>
            HTML;
                } else {
                    echo '<div id="logged-in-name">Hi, User</div>';
                }
            }
            ?>
    </header>

        <nav>
            <a href="cms.php">Analytics</a>
            <a href="javascript:void(0);" onclick="fetchUserDetails()">Users</a>
        </nav>

        <div id="userDetailsContainer" class="user-details-container" style="display:block;"></div>
        <div class="analytics-container" id="analyticsContainer" style="display: block;">
            <div class="analytics">
            
                <!-- Most Popular Items -->
                <div class="analytics-item">
                    <h3>Most Popular Items</h3>
                        <ul>
                            <?php
                                $popularItems = getMostPopularItems();
                                $previousItemName = ''; // Initialize a variable to store the previous item name
                                while ($row = mysqli_fetch_assoc($popularItems)) {
                                echo '<li onclick="showItemDetails(' . $row['item_id'] . ', \'' . $row['item_name'] . '\', ' . $row['bid_count'] . ')">';

                                // Display item name only if it's different from the previous one
                                if ($row['item_name'] != $previousItemName) {
                                    echo '<strong>' . $row['item_name'] . '</strong> - ';
                                    $previousItemName = $row['item_name']; // Update the previous item name
                                }

                                echo 'Bids: ' . $row['bid_count'] . '</li>';
                                }                    
                            ?>
                        </ul>
                </div>

                <div class="analytics-item">
                    <h3>Highest Bid</h3>
                    <ul>
                        <?php
                        $highestBids = getHighestBids();
                        while ($row = mysqli_fetch_assoc($highestBids)) {
                            echo "<li onclick='showItemDetails({$row['item_id']}, \"{$row['item_name']}\", {$row['highest_bid']})'>";
                            // Display item name in strong tags
                            echo '<strong>' . $row['item_name'] . '</strong> - Highest Bid: ' . $row['highest_bid'] . '</li>';
                        }                  
                        ?>
                    </ul>                          
                </div>
                        
                <!-- Display total bids -->
                <div class="analytics-item">
                    <h3>Total Bids</h3>
                        <?php
                            $totalBids = getTotalBids();
                            echo "<p><strong>Total Bids:</strong> {$totalBids}</p>";
                        ?>
                </div>
            </div>

        </div>

        <div class="floating-container" id="itemDetailsContainer" data-user-id="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>"></div>

    <script>
            function fetchUserDetails() {
                analyticsContainer.style.display = 'none';
                itemDetailsContainer.style.display = 'none';
                // Use AJAX to load user details
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            // Parse the JSON response
                            var userDetails = JSON.parse(xhr.responseText);

                            // Update the container with user details
                            var container = document.getElementById('userDetailsContainer');
                            container.innerHTML = '<div class="userDetailsContainer"><h2>User Details</h2>';

                            if ('error' in userDetails) {
                                // Handle the case where there's an error
                                container.innerHTML += '<p class="error">' + userDetails.error + '</p>';
                            } else if ('message' in userDetails) {
                                // Handle the case where no user details are found
                                container.innerHTML += '<p class="message">' + userDetails.message + '</p>';
                            } else {
                                // Display user details
                                userDetails.forEach(function (user) {
                                    container.innerHTML += '<div class="user-details" id="userDetails">';
                                    container.innerHTML += '<p><strong><br>Name:</strong> ' + user.fname + ' ' + user.lname + '</p>';
                                    container.innerHTML += '<p><strong>Email:</strong> ' + user.email + '</p>';
                                    container.innerHTML += '<p><strong>Gender:</strong> ' + user.gender + '</p>';
                                    container.innerHTML += '<p><strong>Birthdate:</strong> ' + user.birthdate + '</p>';
                                    container.innerHTML += '<p><strong>Address:</strong> ' + user.address + '</p>';
                                    container.innerHTML += '<p><strong>Contact Number:</strong> ' + user.cnumber + '</p>';
                                    container.innerHTML += '<p><strong>Date Created:</strong> ' + user.dateCreated + '</p>';
                                    container.innerHTML += '<p><strong>Time Created:</strong> ' + user.timeCreated + '</p><br>';
                                    container.innerHTML += '</div>'; // Close the user-details container
                                });
                            }

                            // Close the white container
                            container.innerHTML += '</div>';
                        } else {
                            console.error('Error fetching user details:', xhr.statusText);
                        }
                    }
                };
                xhr.open("GET", "fetch_user_details.php", true);
                xhr.send();
            }

            // Function to handle showing item details
            function showItemDetails(itemId) {
                document.getElementById('itemDetailsContainer').style.display = 'block';
                const itemDetailsContainer = document.getElementById('itemDetailsContainer');

                // Display loading message
                itemDetailsContainer.innerHTML = '<div class="loading-message">Loading...</div>';

                // Make an AJAX request to fetch item details based on itemId
                fetch(`fetch_item_details.php?itemId=${itemId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network response was not ok (${response.status}: ${response.statusText})`);
                        }
                        return response.json();
                    })
                    .then(itemDetails => {
                        // Log the item details to the console for debugging
                        console.log('Item Details:', itemDetails);

                        // Check if the response has the expected structure
                        if (itemDetails && itemDetails.item_name !== undefined) {
                            // Build HTML content with item details
                            const content = `
                                <div class="item-details">
                                    <h2><strong>${itemDetails.item_name}</strong></h2>
                                    <p><strong>Description:</strong> ${itemDetails.description}</p>
                                    <p><strong>Starting Price</strong>: ${itemDetails.starting_price}</p>
                                    <p><strong>Start Date:</strong> ${itemDetails.start_date}</p>
                                    <p><strong>Start Time:</strong> ${itemDetails.start_time}</p>
                                    <p><strong>End Date:</strong> ${itemDetails.end_date}</p>
                                    <p><strong>End Time:</strong> ${itemDetails.end_time}</p>
                                    <img src="data:image/jpeg;base64,${itemDetails.item_picture}" alt="Item Picture">
                                </div>
                            `;

                            // Update the content in the #itemDetailsContainer
                            itemDetailsContainer.innerHTML = content;
                        } else {
                            console.error('Invalid item details format:', itemDetails);
                            itemDetailsContainer.innerHTML = '<div class="loading-message">Error: Invalid item details format</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching item details:', error.message);
                        itemDetailsContainer.innerHTML = `<div class="loading-message">Error: ${error.message}</div>`;
                    });
            }

            document.getElementById('user-tab').addEventListener('click', toggleDropdown);

            document.querySelectorAll('.category').forEach(function (category) {
                category.addEventListener('click', function () {
                    var categoryText = this.querySelector('h3').innerText;

                    // Use AJAX to load content for the selected category
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4) {
                            if (xhr.status == 200) {
                                // Update the container with the loaded content
                                document.getElementById('dynamic-content-container').innerHTML = xhr.responseText;
                            } else {
                                console.error('Error loading category:', xhr.statusText);
                            }
                        }
                    };
                    xhr.open("GET", "item.php?category=" + encodeURIComponent(categoryText), true);
                    xhr.send();
                });
            });

            function toggleDropdown() {
                var dropdown = document.getElementById('user-dropdown');
                dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            }

                    // Add this part to close the dropdown when clicking outside of it
                    document.addEventListener('click', function (event) {
                        var userTab = document.getElementById('user-tab');
                        var dropdown = document.getElementById('user-dropdown');

                        if (!userTab.contains(event.target) && !dropdown.contains(event.target)) {
                            dropdown.style.display = 'none';
                        }
                    });

            function logout() {
            // Use AJAX to perform the logout without reloading the page
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Redirect to index.php after successful logout
                    window.location.href = "index.php";
                }
            };
                xhr.open("GET", "logout.php", true); // Use GET method to trigger the logout
                xhr.send();
        }                    
    </script>
</body>