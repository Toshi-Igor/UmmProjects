<?php
    session_start();
    include("dbconn.php");
    include("checklog.php");

    // Check if the auctioneer ID is set in the session
    if (!isset($_SESSION['auctioneer_id'])) {
        echo json_encode(array('success' => false, 'error' => 'Unauthorized access: Auctioneer ID not set in session'));
        exit();
    }

    // Handle item submission
    if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submitItem'])) {
        $item_name = $_POST['item_name'];
        $description = $_POST['description'];
        $starting_price = $_POST['starting_price'];
        $start_date = $_POST['start_date'];
        $start_time = $_POST['start_time'];
        $end_date = $_POST['end_date'];
        $end_time = $_POST['end_time'];

        if (isset($_FILES['item_picture'])) {
            $item_picture = file_get_contents($_FILES['item_picture']['tmp_name']);
        } else {
            $item_picture = null; 
        }

        $query = "INSERT INTO items (item_name, description, starting_price, start_date, start_time, end_date, end_time, item_picture, auctioneer_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssdsssss", $item_name, $description, $starting_price, $start_date, $start_time, $end_date, $end_time, $item_picture);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script type='text/javascript'> alert('Item uploaded successfully!')</script>";
        } else {
            echo "<script type='text/javascript'> alert('Error adding item.')</script>";
        }
    }

    // Handle user approval or decline
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['auctioneer_id'])) {
        $auctioneerId = $_SESSION['auctioneer_id'];

        // Check if the auctioneer is an approver
        $queryApprover = "SELECT is_approver FROM auctioneers WHERE auctioneer_id = ?";
        $stmtApprover = $con->prepare($queryApprover);

        if ($stmtApprover) {
            $stmtApprover->bind_param("i", $auctioneerId);

            if ($stmtApprover->execute()) {
                $stmtApprover->bind_result($isApprover);

                if ($stmtApprover->fetch() && $isApprover) {
                    // Auctioneer is an approver, handle user approval actions
                    if (isset($_GET['approveUserId'])) {
                        $userIdToApprove = $_GET['approveUserId'];
                        approveUser($con, $userIdToApprove);
                    } elseif (isset($_GET['declineUserId'])) {
                        $userIdToDecline = $_GET['declineUserId'];
                        declineUser($con, $userIdToDecline);
                    }
                }
            }

            // Close the statement
            $stmtApprover->close();
        }
    }

    // Fetch item listings
    $query_fetch_items = "SELECT item_id, item_name, start_date, start_time, end_date, end_time FROM items";
    $result_fetch_items = mysqli_query($con, $query_fetch_items);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Handler</title>
        <link rel="stylesheet" href="home.css">
        <link rel="icon" type="image/png" href="Image/icontab.png">
    </head>

    <body>
    <header>
        <div class="logo">
            <img src="Image/BidMo_Logo.png" alt="Website Logo">
        </div>
        <div class="header-content"> 
            <h1>BidMo</h1>
            <p>Your Destination for Fashion Bidding</p>
        </div>
        <?php
            // Check if the auctioneer is logged in
            if (isset($_SESSION['auctioneer_id'])) {
                $user_id = $_SESSION['auctioneer_id'];

                // Fetch user information from the database
                $query = "SELECT fname FROM auctioneers WHERE auctioneer_id = ?";
                $stmt = $con->prepare($query);

                // Check if the prepare statement was successful
                if ($stmt) {
                    $stmt->execute([$user_id]); 
                    $stmt->bind_result($fname);
                    $stmt->fetch();
                    $stmt->close();

                    $greeting = (!empty($fname)) ? "Hi, $fname" : "Hi, User";

                    echo <<<HTML
                        <div id="logged-in-name">$greeting</div>
                        <div id="user-tab" onclick="toggleDropdown()">⚙️</div>
                        <div id="user-dropdown">
                            <a href="#" onclick="showProfile()">Profile</a>
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
            <a href="handler.php">Home</a>
            <a id="upload-content" href="javascript:void(0);" onclick="showUploadContainer()">Upload</a>
            <a id="upload-content" href="javascript:void(0);" onclick="showUserApproval()">Approval</a>
            
        </nav>
        <div class ="profile-container" id="profileContainer" style="display: none;"></div>

        <div class="container" id="mainContainer">
            <!-- Left Container: Item Uploading -->
            <div class="upload-container" id="uploadContainer" style="display: none;">
                <h2 style="text-align: center;">Upload Item</h2>
                <form method="POST" enctype="multipart/form-data">
                    <label for="item_name">Item Name:</label>
                    <input type="text" name="item_name" required placeholder="Enter item name">
                    
                    <label for="description">Description:</label>
                    <textarea name="description" required placeholder="Enter item description"></textarea>
                    
                    <label for="starting_price">Starting Price:</label>
                    <input type="text" name="starting_price" required placeholder="Enter starting price">
                    
                    <label for="start_date">Auction Start Date:</label>
                    <input type="date" name="start_date" required>

                    <label for="start_time">Auction Start Time:</label>
                    <input type="time" name="start_time" required>

                    <label for="end_date">Auction End Date:</label>
                    <input type="date" name="end_date" required>

                    <label for="end_time">Auction End Time:</label>
                    <input type="time" name="end_time" required>

                    <label for="item_picture">Item Picture:</label>
                    <input type="file" name="item_picture" id="edit-item-picture" accept="image/*" placeholder="Choose an image file">
                    
                    <button type="submit" name="submitItem">Submit</button>
                </form>
            </div>
            <!-- Right Container: Item Listings -->
            <div class="listings-container"id="listingsContainer" >
            <h2 style="text-align: center;">Item Listings</h2>
                <ul>
                <?php
                    while ($row = mysqli_fetch_assoc($result_fetch_items)) {
                        echo "<li>
                                <a href='#' onclick='showItemDetails({$row['item_id']})'>{$row['item_name']}</a>
                                <button onclick='editItem({$row['item_id']})'>Edit</button>
                                <button onclick=\"updateWinner({$row['item_id']}); showWinner({$row['item_id']});\">Show the winner</button>
                                <button onclick='transactions({$row['item_id']})'>Show the transactions</button>
                            </li>";
                    }          
                    ?>
                </ul>
            </div>
            <div class="approval-container" id="approvalContainer" style="display: none;">
                <div class="approval-section" id="approvalDetails">
                    <h2 style="text-align: center;">USER APPROVALS</h2>
                    <ul>
                        <?php
                            // Fetch users awaiting approval
                            $query_pending_users = "SELECT uv.user_id, u.fname, u.lname FROM user_validations uv JOIN user u ON uv.user_id = u.user_id WHERE uv.approval_status = 0";
                            $result_pending_users = mysqli_query($con, $query_pending_users);

                            while ($row = mysqli_fetch_assoc($result_pending_users)) {
                                echo "<li>
                                        <a href='#' onclick='fetchUserDetails({$row['user_id']})'>{$row['fname']} {$row['lname']}</a>
                                        <div class=\"approve-btn\">
                                            <button onclick='approveUser({$row['user_id']})'>Approve</button>
                                        </div>
                                        <div class=\"decline-btn\">
                                            <button onclick='declineUser({$row['user_id']})'>Decline</button>
                                        </div>
                                    </li>";
                            }                        
                        ?>
                    </ul>
                </div>
            </div>

            <div id="userDetailsContainer" style="display: none;">
                <h2>User Details</h2>
                <div id="userDetails"></div>
                <button onclick="goBack()">Back</button>
            </div>

        <!-- Floating Container for Item Details -->
        <div class="item-container" id="itemDetailsContainer" style="display: none;">
        </div>
    </div>
    
        <footer>
            <p>&copy; 2024 BidMo. All rights reserved.</p>
        </footer>

        <script>
            function approveUser(userId) {
                // Make an AJAX request to approve the user
                fetch('approve_user.php?user_id=' + userId)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('User approved successfully.');
                        } else {
                            alert('Error approving user: ' + result.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error approving user:', error.message);
                        alert('Error approving user: ' + error.message);
                    });
            }

            function declineUser(userId) {
                // Make an AJAX request to decline the user
                fetch('decline_user.php?userId=' + userId)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('User declined successfully.');
                        } else {
                            alert('Error declining user: ' + result.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error declining user:', error.message);
                        alert('Error declining user: ' + error.message);
                    });
            }

            // Function to handle click on user name
            document.querySelectorAll('.user-link').forEach(item => {
                item.addEventListener('click', event => {
                    const userId = item.getAttribute('data-user-id'); // Use item instead of event.target
                    fetchUserDetails(userId);
                });
            });

            // Function to fetch user details
            function fetchUserDetails(userId) {
                fetch('fetch_user_validations.php?userId=' + userId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        displayUserDetails(data);
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                    });
            }

            // Function to display user details
            function displayUserDetails(userData) {
                const userDetailsContainer = document.getElementById('userDetails');
                userDetailsContainer.innerHTML = `
                    <p><strong>Name:</strong> ${userData.fname} ${userData.lname}</p>
                    <p><strong>Birthdate:</strong> ${userData.birthdate}</p>
                    <p><strong>Address:</strong> ${userData.address}</p>
                    <p><strong>Cellphone Number:</strong> ${userData.cnumber}</p>
                    <p><strong>Email:</strong> ${userData.email}</p>
                    <div>
                        <label>Valid ID:</label>
                        <img src="data:image/jpeg;base64,${userData.validId}" alt="Valid ID">
                    </div>
                    <div>
                        <label>Selfie:</label>
                        <img src="data:image/jpeg;base64,${userData.selfiepic}" alt="Selfie">
                    </div>
                `;
                document.getElementById('approvalContainer').style.display = 'none';
                document.getElementById('userDetailsContainer').style.display = 'block';
            }

            // Function to go back to the approval list
            function goBack() {
                document.getElementById('userDetailsContainer').style.display = 'none';
                document.getElementById('approvalContainer').style.display = 'block';
            }


            function toggleDropdown() {
                var dropdown = document.getElementById('user-dropdown');
                dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                }

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
                xhr.open("GET", "logout.php", true); 
                xhr.send();
            }

            function showUploadContainer() {
                // Show the upload container
                document.getElementById('uploadContainer').style.display = 'block';
                
                itemDetailsContainer.style.display = 'none';
                approvalContainer.style.display = 'none';
                listingsContainer.style.display = 'none';
                profileContainer.style.display = 'none';
                userDetailsContainer.style.display = 'none';
            }

            function showUserApproval() {
                // Show the userApproval container
                document.getElementById('approvalContainer').style.display = 'block';
                
                itemDetailsContainer.style.display = 'none';
                listingsContainer.style.display = 'none';
                profileContainer.style.display = 'none';
                uploadContainer.style.display = 'none';
                userDetailsContainer.style.display = 'none';
            }

            function showItemDetails(itemId) {
                document.getElementById('itemDetailsContainer').style.display = 'block';
                const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                const bidFormContainer = document.getElementById('bidFormContainer');
                const bidItemIdInput = document.getElementById('bidItemId');

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

                            // Check if bidFormContainer is not null before accessing 'style'
                            if (bidFormContainer) {
                                // Show the bid form container
                                bidFormContainer.style.display = 'block';
                            }

                            // Check if bidItemIdInput is not null before accessing 'value'
                            if (bidItemIdInput) {
                                // Set the item ID in the bid form
                                bidItemIdInput.value = itemId;
                            }

                            // Add the latest bid details
                            const latestBidDetails = `
                                <div class="latest-bid-details">
                                    <h3>Latest Bid Information</h3>
                                    <p>Latest Bid Amount: ${itemDetails.latest_bid_amount}</p>
                                    <p>Latest Bidder: ${itemDetails.latest_bidder_fname} ${itemDetails.latest_bidder_lname}</p>
                                    <p>Contact Number: ${itemDetails.latest_bidder_cnumber}</p>
                                </div>
                            `;
                            itemDetailsContainer.innerHTML += latestBidDetails;

                    // Fetch the list of bids for the item using fetch_item_bids.php
                    fetch(`fetch_item_bids.php?itemId=${itemId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network response was not ok (${response.status}: ${response.statusText})`);
                        }
                        return response.json();
                    })
                    .then(bids => {
                        // Log the bid details to the console for debugging
                        console.log('Bids:', bids);

                        if (bids && bids.hasOwnProperty('bids')) {
                            // Add the list of bids to the itemDetailsContainer
                            const bidsList = `
                                <div class="bids-list">
                                    <h3>List of Bids</h3>
                                    <ul>
                                        ${bids.bids.map(bid => `
                                            <li>
                                                <br>
                                                <p>Bidder: ${bid.fname} ${bid.lname}</p>
                                                <p>Amount: ${bid.bid_amount}</p>
                                                <p>Contact Number: ${bid.cnumber}</p>
                                                <p>Email: ${bid.email}</p>
                                                <p>Address: ${bid.address}</p>
                                                <p>Date: ${bid.bid_date} Time: ${bid.bid_time}</p>
                                                <hr>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;
                            itemDetailsContainer.innerHTML += bidsList;
                        } else {
                            console.error('Invalid or missing bids format in the response:', bids);
                            itemDetailsContainer.innerHTML += '<div class="loading-message">Error: Invalid or missing bids format</div>';
                        }
                    })
                .catch(error => {
                    console.error('Error fetching bids:', error.message);
                    itemDetailsContainer.innerHTML += '<div class="loading-message">Error: Unable to fetch bids</div>';
                });

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

            function editItem(itemId) {
                document.getElementById('itemDetailsContainer').style.display = 'block';
                console.log('Received item ID:', itemId);
                // Make an AJAX request to fetch item details based on itemId
                fetch('fetch_item_details.php?itemId=' + itemId)
                    .then(response => {
                        console.log('Response status:', response.status);

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        return response.json();
                    })
                    .then(itemDetails => {
                        console.log('Item details:', itemDetails);

                        if (itemDetails.hasOwnProperty('error')) {
                            throw new Error(itemDetails.error);
                        }

                        console.log('Item ID before updateItem call:', itemDetails.item_id);
                        // Build HTML content with editable fields
                        const content = `
                            <div class="item-details">
                                <h2>Edit Item: ${itemDetails.item_name}</h2>
                                <form id="editItemForm" onsubmit="event.preventDefault(); updateItem('${itemDetails.item_id}');" enctype="multipart/form-data">

                                    <label for="edit_item_name">Item Name:</label>
                                    <input type="text" id="edit_item_name" value="${itemDetails.item_name}" required>
                                    
                                    <label for="edit_description">Description:</label>
                                    <textarea id="edit_description" required>${itemDetails.description}</textarea>
                                    
                                    <label for="edit_starting_price">Starting Price:</label>
                                    <input type="text" id="edit_starting_price" value="${itemDetails.starting_price}" required>
                                    
                                    <label for="edit_start_date">Start Date:</label>
                                    <input type="date" id="edit_start_date" value="${itemDetails.start_date}" required>
                                    
                                    <label for="edit_start_time">Start Time:</label>
                                    <input type="time" id="edit_start_time" value="${itemDetails.start_time}" required>
                                    
                                    <label for="edit_end_date">End Date:</label>
                                    <input type="date" id="edit_end_date" value="${itemDetails.end_date}" required>
                                    
                                    <label for="edit_end_time">End Time:</label>
                                    <input type="time" id="edit_end_time" value="${itemDetails.end_time}" required>

                                    <button type="submit">Update</button>
                                </form>
                            </div>
                        `;

                        // Update the content in the #itemDetailsContainer
                        document.getElementById('itemDetailsContainer').innerHTML = content;
                    })
                    .catch(error => {
                        console.error('Error fetching item details:', error.message);
                    });
            }

            function updateItem(itemId) {

                // Get form data
                const itemName = document.getElementById('edit_item_name').value;
                const description = document.getElementById('edit_description').value;
                const startingPrice = document.getElementById('edit_starting_price').value;
                const startDate = document.getElementById('edit_start_date').value;
                const startTime = document.getElementById('edit_start_time').value;
                const endDate = document.getElementById('edit_end_date').value;
                const endTime = document.getElementById('edit_end_time').value;

                // Combine date and time strings and format them
                const formattedStartDate = startDate + ' ' + startTime;
                const formattedEndDate = endDate + ' ' + endTime;

                // Create a FormData object
                const formData = new FormData();
                formData.append('itemId', itemId);
                formData.append('itemName', itemName);
                formData.append('description', description);
                formData.append('startingPrice', startingPrice);
                formData.append('startDateTime', formattedStartDate);
                formData.append('endDateTime', formattedEndDate);

                // Log the FormData before sending the request
                console.log('FormData:', formData);

                // Make an AJAX request to update item details
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_item.php', true);

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        console.log('Item updated successfully:', xhr.responseText);
                        alert('Item updated successfully');

                    } else {
                        console.error('Error updating item:', xhr.statusText);
                        console.log('Response:', xhr.responseText);
                    }
                };

                xhr.onerror = function () {
                    console.error('Network error occurred');
                };

                xhr.send(formData);
            }
            
            
            function updateWinner(itemId) {
                // Make an AJAX request to stop the bidding
                fetch('insert_winner.php?itemId=' + itemId)
                    .then(response => response.text())
                    .then(message => {
                        alert(message);
                    })
                    .catch(error => {
                        console.error('Error stopping bidding:', error.message);
                        alert('Error stopping bidding: ' + error.message);
                    });
            }

            function showWinner(item_id) {
                // Make an AJAX request to fetch the winner's information
                fetch('fetch_winner.php?itemId=' + item_id)
                .then(response => response.json())
                .then(winner => {
                    // Create a container for the winner's information
                    const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                    itemDetailsContainer.innerHTML = ''; // Clear previous content
                    document.getElementById('itemDetailsContainer').style.display = 'block';

                    const winnerContainer = document.createElement('div');
                    winnerContainer.classList.add('floating-container');
                    winnerContainer.classList.add('winner-container');

                    const winnerInfo = `
                        <div class="winner-info">
                            <h3>Winner Information</h3>
                            <p><strong>Name:</strong> ${winner.fname} ${winner.lname}</p>
                            <p><strong>Contact Number:</strong> ${winner.cnumber}</p>
                            <p><strong>Address:</strong> ${winner.address}</p>
                            <p><strong>Email:</strong> ${winner.email}</p>
                            <hr>
                            <p><strong>Item Name:</strong> ${winner.item_name}</p>
                            <p><strong>Description:</strong> ${winner.description}</p>
                            <p><strong>Bid Amount:</strong> ${winner.bid_amount}</p>
                            <p><strong>Win Date:</strong> ${winner.win_date}</p>
                            <p><strong>Win Time:</strong> ${winner.win_time}</p>
                            <img src="data:image/jpeg;base64,${winner.item_picture}" alt="Item Picture">
                        </div>
                    `;

                    winnerContainer.innerHTML = winnerInfo;
                    itemDetailsContainer.appendChild(winnerContainer);
                })
                .catch(error => {
                    console.error('Error fetching winner:', error.message);
                    alert('Error fetching winner: ' + error.message);
                });
            }

            function transactions(item_id) {
                fetch('fetch_winner.php?itemId=' + item_id)
                .then(response => response.json())
                .then(winner => {
                    // Create a container for the winner information
    
                    const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                    itemDetailsContainer.innerHTML = ''; // Clear previous content
                    document.getElementById('itemDetailsContainer').style.display = 'block';

                    const winnerContainer = document.createElement('div');
                    winnerContainer.classList.add('floating-container');
                    winnerContainer.classList.add('transactions-container');

                    // Create HTML for winner information
                    let winnerInfoHTML = `
                        <h3 style="color: black;">Transaction Information</h3>
                        <p style="color: black;"><strong>Name:</strong> ${winner.fname} ${winner.lname}</p>
                        <p style="color: black;"><strong>Contact Number:</strong> ${winner.cnumber}</p>
                        <p style="color: black;"><strong>Address:</strong> ${winner.address}</p>
                        <p style="color: black;"><strong>Email:</strong> ${winner.email}</p>
                        <hr>
                        <p style="color: black;"><strong>Item Name:</strong> ${winner.item_name}</p>
                        <p style="color: black;"><strong>Description:</strong> ${winner.description}</p>
                        <p style="color: black;"><strong>Bid Amount:</strong> ${winner.bid_amount}</p>
                        <p style="color: black;"><strong>Win Date:</strong> ${winner.win_date}</p>
                        <p style="color: black;"><strong>Win Time:</strong> ${winner.win_time}</p>
                        <img src="data:image/jpeg;base64,${winner.item_picture}" alt="Item Picture" style="width: 50%;">
                        <hr>
                    `;

                    winnerContainer.innerHTML = winnerInfoHTML;

                    // Check if a transaction has been placed
                    if (winner.status !== null && winner.status !== '') {
                // Transaction details available, add them to the container
                winnerContainer.innerHTML = winnerInfoHTML += `
                    <h4 style="color: black;">Transaction Details</h4>
                    <p style="color: black;"><strong>Payment Method:</strong> ${winner.transaction_type}</p>
                    <p style="color: black;"><strong>Status:</strong> ${winner.status}</p>
                `;
            
                // Allow handler to change the status if a transaction is placed
                const statusOptions = ['Ongoing', 'Shipped', 'Delivered'];
                const statusSelect = document.createElement('select');
                statusOptions.forEach(option => {
                    const statusOption = document.createElement('option');
                    statusOption.value = option;
                    statusOption.textContent = option;
                    if (option === winner.status) {
                        statusOption.selected = true;
                    }
                    statusSelect.appendChild(statusOption);
                });

                // Variable to store the selected status value
                let selectedStatus = winner.status;

                // Add event listener for changing status
                statusSelect.addEventListener('change', function() {
                    selectedStatus = statusSelect.value;
                });

                // Add a button to save the status change
                const saveButton = document.createElement('button');
                saveButton.textContent = 'Save Status';
                saveButton.addEventListener('click', function() {
                    fetch('update_transaction_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            transactionId: winner.transaction_id,
                            status: selectedStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Display a success message or handle the response as needed
                        alert('Status updated successfully');
                    })
                    .catch(error => {
                        console.error('Error updating status:', error.message);
                        alert('Error updating status: ' + error.message);
                    });
                });

                winnerInfoHTML += `<br>`;
                winnerInfoHTML += `<p style="color: black;"><strong>Status:</strong> ${winner.status}</p>`;
                winnerInfoHTML += `<br>`;

                winnerContainer.appendChild(statusSelect);
                winnerContainer.appendChild(saveButton);
            } else {
                // No transaction details available, inform the user
                winnerInfoHTML += `<p style="color: black;">No transaction details available.</p>`;
            }
                    // Append the winner container to the item details container
                    itemDetailsContainer.appendChild(winnerContainer);
                })
                .catch(error => {
                    console.error('Error fetching winner information:', error.message);
                    alert('Error fetching winner information: ' + error.message);
                });
            }

            function showProfile() {
                // Make an AJAX request to fetch the user's profile details
                fetch('fetch_handler_data.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(userData => {
                        // Check if there's an error in fetching user data
                        if (userData.hasOwnProperty('error')) {
                            throw new Error(userData.error);
                        }
                        
                        itemDetailsContainer.style.display = 'none';
                        approvalContainer.style.display = 'none';
                        listingsContainer.style.display = 'none';
                        uploadContainer.style.display = 'none';
                        userDetailsContainer.style.display = 'none';

                        var container = document.getElementById("profileContainer");
                        container.style.display = 'block';
                        container.innerHTML = "";
                        container.classList.add("profile-container");
                        
                        // Display user profile details
                        const profileInfo = `
                            <h2>User Profile</h2>
                            <p>First Name: ${userData.fname}</p>
                            <p>Last Name: ${userData.lname}</p>
                            <p>Gender: ${userData.gender}</p>
                            <p>Birthdate: ${userData.birthdate}</p>
                            <p>Address: ${userData.address}</p>
                            <p>Contact Number: ${userData.cnumber}</p>
                            <p>Email: ${userData.email}</p>
                        `;

                        // Append profile information to the profileContainer
                        document.getElementById('profileContainer').innerHTML = profileInfo;
                    })
                    .catch(error => {
                        console.error('Error fetching user profile:', error.message);
                        alert('Error fetching user profile: ' + error.message);
                    });
            }

        </script>

    </body>
    
</html>
