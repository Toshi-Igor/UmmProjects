<?php
session_start();
include("dbconn.php");
include("checklog.php");

// Handle the bid submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    // Get user ID from the session
    $user_id = $_SESSION['user_id'];

    // Get bid amount and item ID from the POST data
    $bid_amount = filter_var($_POST['bidAmount'], FILTER_VALIDATE_FLOAT);
    $item_id = filter_var($_POST['itemId'], FILTER_VALIDATE_INT);

    // Insert bid into the database
    $query = "INSERT INTO bids (user_id, item_id, bid_amount, bid_date, bid_time) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $con->prepare($query);

    if ($stmt) {
        $stmt->bind_param("iid", $user_id, $item_id, $bid_amount);

        if ($stmt->execute()) {
            // Bid successfully inserted
            echo json_encode(['success' => true, 'message' => 'Bid submitted successfully']);
        } else {
            // Handle execution error
            echo json_encode(['success' => false, 'message' => 'Error executing statement: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle the case where the prepare statement failed
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $con->error]);
    }
}

// Fetch item listings
$query_fetch_items = "SELECT item_id, item_name, item_picture, start_date, start_time, end_date, end_time FROM items";
$result_fetch_items = mysqli_query($con, $query_fetch_items);


?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>BidMo</title>
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
            // Check if the user is logged in
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Fetch user information from the database
                $query = "SELECT fname FROM user WHERE user_id = ?";
                $stmt = $con->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($fname);
                    $stmt->fetch();
                    $stmt->close();

                    $greeting = (!empty($fname)) ? "Hi, $fname" : "Hi, User";

                    echo <<<HTML
                    <div id="logged-in-name">$greeting</div>
                    <div id="user-tab">⚙️</div>
                    <div id="user-dropdown">
                        <a href="#" onclick="fetchUserData(event)">Profile</a>
                        <a href="#" onclick="fetchUserBids()">History</a>
                        <a href="#" onclick="changePass()">Change password</a>
                        <a href="#" onclick="fetchAuctioneerProof()">Auctioneer Proof Pictures</a>
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
            <a href="home.php">Home</a>
            <a href="about.php">About</a>
        </nav>

        <section id="section" style="display: block;">
            <h2>Explore Current Bids</h2>
        </section>

        <div class="listings-container" id="listingsContainer">
        <h2>Item Listings</h2>
        <ul>
            <?php
                date_default_timezone_set('Asia/Manila');

                while ($row = mysqli_fetch_assoc($result_fetch_items)) {
                    $imageData = $row['item_picture'];
                    $base64Image = base64_encode($imageData);
                    $imageSrc = 'data:image/jpeg;base64,' . $base64Image;
                    
                    // Pass end date and time to JavaScript
                    echo "<li>";
                    echo "<a href='#' onclick='showItemDetails({$row['item_id']}); return false;'>";
                    echo "<img class='item-image' src='{$imageSrc}' alt='Item Picture'>";
                    echo "<h3>{$row['item_name']}</h3>";
                    echo "<p>Countdown: <span id='countdown_{$row['item_id']}'></span></p>";
                    echo "</a>";
                    echo "</li>";
                }
            ?>            

        </ul>
        </div>

        <!-- Floating Container for Item Details -->
        <div class="floating-container" id="itemDetailsContainer" data-user-id="<?php echo $_SESSION['user_id']; ?>">
        </div>

        <!-- Floating Container for Proof of Handler -->
        <div id="proofPicturesContainer"></div>

        <!-- Floating Container for Bid Form -->
        <div class="floating-container" id="bidFormContainer" style="display: none;">
            <h2>Place Bid</h2>
            <form id="bidForm">
                <label for="bidAmount">Bid Amount:</label>
                <input type="number" step="1.0" name="bidAmount" id="bidAmount" required>
                <input type="hidden" name="itemId" id="bidItemId" value="">
                <button type="button" onclick="submitBid()">Submit Bid</button>
            </form>
        </div>

        <!-- Floating Container for User Profile -->
        <div class="floating-container" id="userProfileEditContainer" style="display: none;">
            <h2>User Profile</h2>
            <p id="user-profile-edit-fname"></p>
            <p id="user-profile-edit-lname"></p>
            <p id="user-profile-edit-gender"></p>
            <p id="user-profile-edit-birthdate"></p>
            <p id="user-profile-edit-address"></p>
            <p id="user-profile-edit-cnumber"></p>
            <p id="user-profile-edit-email"></p>
            <button onclick="modifyProfile()">Modify Profile</button>
        </div>

        <!-- Floating Container for Item Details -->
        <div class="floating-container" id="changePassContainer" style="display: none;">
        </div>
    <script>
    // Assume winnerData contains the winner's information and the item details
    var winner = ${JSON.stringify(winnerData)};
    var proceedButton = document.createElement('button');
    proceedButton.textContent = 'Proceed';
    proceedButton.addEventListener('click', function() {
        document.getElementById('transactionContainer').style.display = 'block';
    });

    // Check if the user won the particular item
    if (winner.wonItem) {
        document.body.appendChild(proceedButton);
    }

    // Handle form submission for placing bid transaction
    document.getElementById('transactionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var paymentMethod = document.getElementById('paymentMethod').value;
        // Use fetch or AJAX to submit the form data to the server
        fetch('placeBidTransaction.php', {
            method: 'POST',
            body: JSON.stringify({
                winnerId: winner.winner_id,
                paymentMethod: paymentMethod
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data); // Handle response from the server
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
</script>


        <footer>
            <p>&copy; 2024 BidMo. All rights reserved.</p>
        </footer>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
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

                // Function to fetch auctioneer proof pictures
                function fetchAuctioneerProof() {
                    bidFormContainer.style.display = 'none';
                    userProfileEditContainer.style.display = 'none';
                    changePassContainer.style.display = 'none';
                    listingsContainer.style.display = 'none';
                    itemDetailsContainer.style.display = 'none';
                    section.style.display = 'none';

                    // Perform an AJAX request to fetch auctioneer proof pictures
                    $.ajax({
                        url: 'fetch_auctioneer_proof.php', // PHP script to handle the database query
                        type: 'GET',
                        success: function(response) {
                            // Handle successful response
                            var proofPictures = JSON.parse(response);
                            var container = document.getElementById('proofPicturesContainer');

                            // Clear previous content
                            container.innerHTML = '';

                            // Loop through each proof picture and description
                            proofPictures.forEach(function(proof) {
                                // Create container for each proof picture and description
                                var proofItem = document.createElement('div');
                                proofItem.classList.add('proof-picture-item');

                                // Create image element
                                var img = document.createElement('img');
                                img.src = 'data:image/jpeg;base64,' + proof.proofpic; // Assuming images are JPEG format

                                // Create description paragraph
                                var description = document.createElement('p');
                                description.textContent = proof.description;

                                // Append image and description to the container
                                proofItem.appendChild(img);
                                proofItem.appendChild(description);

                                // Append container to the main container
                                container.appendChild(proofItem);
                            });
                        },
                        error: function(xhr, status, error) {
                            // Handle errors
                            console.error(xhr.responseText);
                        }
                    });
                }

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
                xhr.open("GET", "logout.php", true); 
                xhr.send();
            }

  
            document.addEventListener("DOMContentLoaded", function() {
                <?php
                mysqli_data_seek($result_fetch_items, 0); // Reset the pointer to the beginning of the result set
                while ($row = mysqli_fetch_assoc($result_fetch_items)) {
                    echo "startCountdown({$row['item_id']}, '{$row['end_date']}', '{$row['end_time']}');";
                }
                ?>
            });

            function startCountdown(itemId, endDate, endTime) {
                // Set the end date and time
                var endDateTime = new Date(endDate + ' ' + endTime).getTime();

                // Update the countdown every second
                var x = setInterval(function() {
                    // Get the current date and time
                    var now = new Date().getTime();

                    // Calculate the time remaining
                    var distance = endDateTime - now;

                    // Calculate days, hours, minutes, and seconds
                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Display the countdown timer
                    var countdownElement = document.getElementById('countdown_' + itemId);
                    countdownElement.innerHTML = days + " days, " + hours + " hours, " + minutes + " minutes, " + seconds + " seconds";

                    // If the countdown is over, display a message
                    if (distance < 0) {
                        clearInterval(x);
                        countdownElement.innerHTML = "Auction has ended";
                    }
                }, 1000); // Update every second
            }

            // JavaScript function to show item details in the floating container
            function showItemDetails(itemId) {
                document.getElementById('itemDetailsContainer').style.display = 'block';
                const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                const bidFormContainer = document.getElementById('bidFormContainer');
                const bidForm = document.getElementById('bidForm');
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

                            // Check if bidding is allowed
                            const currentDateTime = new Date();
                            const endDateTime = new Date(`${itemDetails.end_date} ${itemDetails.end_time}`);
                            if (currentDateTime > endDateTime) {
                                // Disable the bid form
                                bidForm.reset(); // Reset the form
                                bidFormContainer.style.display = 'none'; 
                                // Show winner information
                                showWinner(itemId);
                            } else {
                                // Enable the bid form
                                bidFormContainer.style.display = 'block'; 

                                // Set the item ID in the bid form
                                bidItemIdInput.value = itemId;

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
                            }
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

            // Function to fetch the user's bid history
            function fetchUserBids() {
                bidFormContainer.style.display = 'none';
                userProfileEditContainer.style.display = 'none';
                changePassContainer.style.display = 'none';
                listingsContainer.style.display = 'none';
                itemDetailsContainer.style.display = 'none';
                section.style.display = 'none';
                
                // Use AJAX to fetch user bids
                fetch('fetch_user_bids.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Network response was not ok (${response.status}: ${response.statusText})`);
                        }
                        return response.json();
                    })
                    .then(bidHistory => {
                        // Log the bid history to the console for debugging
                        console.log('Bid History:', bidHistory);

                        // Check if the response has the expected structure
                        if (bidHistory && bidHistory.length > 0) {
                            // HTML content with bid history
                            const content = `
                                <div class="bid-history">
                                    <h2>Your Bidding History</h2>
                                    <ul>
                                        ${bidHistory.map(bid => `
                                            <li style="color: black; list-style-type: none;">
                                                <br>
                                                <p>Item Name: ${bid.item_name}</p>
                                                <p>Description: ${bid.description}</p>
                                                <p>Bid Amount: ${bid.bid_amount}</p>
                                                <p>Date: ${bid.bid_date}  Time: ${bid.bid_time}</p>
                                                <hr>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;

                            // Update the content in the #itemDetailsContainer
                            document.getElementById('itemDetailsContainer').innerHTML = content;

                            // Show the bid history container
                            document.getElementById('itemDetailsContainer').style.display = 'block';
                        } else {
                            console.error('Invalid bid history format:', bidHistory);
                            document.getElementById('itemDetailsContainer').innerHTML = '<div class="loading-message">Error: Invalid bid history format</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching bid history:', error.message);
                        document.getElementById('itemDetailsContainer').innerHTML = `<div class="loading-message">Error: ${error.message}</div>`;
                    });
            }

            // Function to submit bid
           function submitBid() {
                // Get bid amount and item ID from the form
                const bidAmount = document.getElementById('bidAmount').value;
                const itemId = document.getElementById('bidItemId').value;

                // Log bid data to the console for debugging
                console.log('Bid Amount:', bidAmount);
                console.log('Item ID:', itemId);

                // You can now make an AJAX request to submit the bid with bidAmount and itemId
                fetch('submit_bid.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `bidAmount=${bidAmount}&itemId=${itemId}`,
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(responseData => {
                        // Handle the response data 
                        console.log(responseData);
                        if (responseData.success) {
                            alert('Bid submitted successfully!');
                        } else {
                            alert('Error submitting bid. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting bid:', error.message);
                        alert('Error submitting bid. Please try again.');
                    });
            }
            
            function fetchUserData(event) {
                event.stopPropagation();
                bidFormContainer.style.display = 'none';
                section.style.display = 'none';
                bidFormContainer.style.display = 'none'
                itemDetailsContainer.style.display = 'none'
                listingsContainer.style.display = 'none'
                changePassContainer.style.display = 'none'

                // Use AJAX to fetch user data
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            try {
                                // Parse the JSON response
                                var userData = JSON.parse(xhr.responseText);

                                // Check if the response has the expected structure
                                if (userData && userData.fname) {
                                    // Update the UI with the fetched user data
                                    var profileContainer = document.getElementById('userProfileEditContainer');
                                    profileContainer.style.display = 'block'; 
                                    profileContainer.innerHTML = ''; 

                                    // Add user data to the profile container
                                    profileContainer.innerHTML += `<h2>User Profile</h2>`;
                                    addProfileDetail(profileContainer, 'First Name', 'user-profile-edit-fname', userData.fname);
                                    addProfileDetail(profileContainer, 'Last Name', 'user-profile-edit-lname', userData.lname);
                                    addProfileDetail(profileContainer, 'Gender', 'user-profile-edit-gender', userData.gender);
                                    addProfileDetail(profileContainer, 'Birthdate', 'user-profile-edit-birthdate', userData.birthdate);
                                    addProfileDetail(profileContainer, 'Address', 'user-profile-edit-address', userData.address);
                                    addProfileDetail(profileContainer, 'Contact Number', 'user-profile-edit-cnumber', userData.cnumber);
                                    addProfileDetail(profileContainer, 'Email', 'user-profile-edit-email', userData.email);

                                    // Button to modify profile
                                    profileContainer.innerHTML += `<button onclick="modifyProfile()">Modify Profile</button>`;
                                } else {
                                    console.error('Invalid user data format:', userData);
                                    alert('Error: Unable to fetch user data. Invalid user data format.');
                                }
                            } catch (error) {
                                console.error('Error parsing JSON:', error);
                                alert('Error: Unable to fetch user data. Error parsing JSON response.');
                            }
                        } else {
                            console.error('Error fetching user data. HTTP status:', xhr.status);
                            alert('Error: Unable to fetch user data. HTTP status ' + xhr.status);
                        }
                    }
                };
                xhr.open("GET", "fetch_user_data.php", true); 
                xhr.send();
            }

            // Function to add profile details
            function addProfileDetail(container, label, id, value) {
                var p = document.createElement('p');
                p.textContent = `${label}: `;

                var span = document.createElement('span');
                span.id = id;
                span.textContent = value;

                p.appendChild(span);
                container.appendChild(p);
            }

            // Function to modify the user profile
            function modifyProfile() {
                var profileContainer = document.getElementById('userProfileEditContainer');

                // Form for editing profile
                var editForm = document.createElement('form');
                editForm.id = 'editProfileForm';

                // Add input fields for each profile detail
                addProfileEditField(editForm, 'First Name', 'user-profile-edit-fname', 'fname', 'text');
                addProfileEditField(editForm, 'Last Name', 'user-profile-edit-lname', 'lname', 'text');
                addProfileEditField(editForm, 'Gender', 'user-profile-edit-gender', 'gender', 'text');
                addProfileEditField(editForm, 'Birthdate', 'user-profile-edit-birthdate', 'birthdate', 'date');
                addProfileEditField(editForm, 'Address', 'user-profile-edit-address', 'address', 'text');
                addProfileEditField(editForm, 'Contact Number', 'user-profile-edit-cnumber', 'cnumber', 'tel');
                addProfileEditField(editForm, 'Email', 'user-profile-edit-email', 'email', 'email');

                // Button to submit the changes
                var submitButton = document.createElement('button');
                submitButton.type = 'button'; 
                submitButton.textContent = 'Save Changes';

                // Append the form and button to the profile container
                profileContainer.innerHTML = ''; 
                profileContainer.appendChild(editForm);
                profileContainer.appendChild(submitButton);

                // Add click event listener to the submit button
                submitButton.addEventListener('click', saveChanges);
            }

            // Function to add input fields for editing profile
            function addProfileEditField(form, label, id, name, type) {
                var labelElement = document.createElement('label');
                labelElement.textContent = `${label}: `;

                var inputElement = document.createElement('input');
                inputElement.id = id;
                inputElement.name = name; 
                inputElement.type = type;

                // Set the default value based on the current displayed value
                inputElement.value = document.getElementById(id).textContent;

                labelElement.appendChild(inputElement);
                form.appendChild(labelElement);
            }

            // Function to save the changes made to the profile
            function saveChanges() {
                // Get the form data
                var formData = new FormData(document.getElementById('editProfileForm'));

                // Use AJAX to save changes
                fetch('update_profile.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => response.json())
                    .then(responseData => {
                        // Log response data for debugging
                        console.log('Response Data:', responseData);

                        if (responseData.success) {
                            // Update profile details in the DOM
                            updateProfileDetail('user-profile-edit-fname', responseData.userData.fname);
                            updateProfileDetail('user-profile-edit-lname', responseData.userData.lname);
                            updateProfileDetail('user-profile-edit-gender', responseData.userData.gender);
                            updateProfileDetail('user-profile-edit-birthdate', responseData.userData.birthdate);
                            updateProfileDetail('user-profile-edit-address', responseData.userData.address);
                            updateProfileDetail('user-profile-edit-cnumber', responseData.userData.cnumber);
                            updateProfileDetail('user-profile-edit-email', responseData.userData.email);

                            // Display success message
                            alert('Profile updated successfully');
                        } else {
                            // Display error message
                            alert('Error updating profile: ' + responseData.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving changes:', error);
                        alert('Error updating profile. Please try again.');
                    });
            }


            // Function to update profile details in the DOM
            function updateProfileDetail(id, value) {
                document.getElementById(id).textContent = value;
            }

            // JavaScript function to change password
            function changePass() {
                section.style.display = 'none';
                bidFormContainer.style.display = 'none';
                itemDetailsContainer.style.display = 'none';
                userProfileEditContainer.style.display = 'none';
                listingsContainer.style.display = 'none';

                // Show the changePassContainer
                var container = document.getElementById("changePassContainer");
                container.style.display = 'block';
                container.innerHTML = ""; 
                container.classList.add("password-change-container");

                // Create input elements for old password, new password, and confirm new password
                var oldPasswordInput = document.createElement("input");
                oldPasswordInput.type = "password";
                oldPasswordInput.placeholder = "Old Password";
                oldPasswordInput.classList.add("password-input");

                var newPasswordInput = document.createElement("input");
                newPasswordInput.type = "password";
                newPasswordInput.placeholder = "New Password";
                newPasswordInput.classList.add("password-input");

                var confirmPasswordInput = document.createElement("input");
                confirmPasswordInput.type = "password";
                confirmPasswordInput.placeholder = "Confirm New Password";
                confirmPasswordInput.classList.add("password-input");

                // Save button
                var saveButton = document.createElement("button");
                saveButton.textContent = "Save";
                saveButton.onclick = function () {
                    savePassword(oldPasswordInput.value, newPasswordInput.value, confirmPasswordInput.value);
                };

                // Append input elements and save button to the container
                container.appendChild(oldPasswordInput);
                container.appendChild(newPasswordInput);
                container.appendChild(confirmPasswordInput);
                container.appendChild(saveButton);
            }

            // Function to save the password
            function savePassword(oldPassword, newPassword, confirmPassword) {
                // Check if the new password matches the confirm new password
                if (newPassword !== confirmPassword) {
                    alert("New password and confirm password do not match. Please try again.");
                    return; // Exit the function if passwords don't match
                }

                // Use AJAX to send the old and new passwords to the server for validation and processing
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            // Check if the password change was successful
                            if (xhr.responseText === "success") {
                                // Inform the user that the password has been changed successfully
                                alert("Password changed successfully!");
                            } else if (xhr.responseText === "error") {
                                // Inform the user that an error occurred
                                alert("Failed to change password. Please try again later.");
                            } else if (xhr.responseText === "invalid_old_password") {
                                // Inform the user that the old password is invalid
                                alert("Old password is incorrect. Please try again.");
                            } else {
                                // Inform the user about any other unexpected response
                                alert("Unexpected response from the server. Please try again later.");
                            }
                        } else {
                            // Inform the user about the HTTP error
                            alert("Failed to communicate with the server. Please try again later.");
                        }
                    }
                };
                xhr.open("POST", "change_pass.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send("oldPassword=" + oldPassword + "&newPassword=" + newPassword);
            }

            function showWinner(item_id) {
                // Make an AJAX request to fetch the winner's information
                fetch('fetch_winner.php?itemId=' + item_id)
                .then(response => response.json())
                .then(winner => {
                    // Create a container for the winner's information
                    const itemDetailsContainer = document.getElementById('itemDetailsContainer');
                    itemDetailsContainer.innerHTML = ''; // Clear previous content

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

                    // Add the "Proceed" button if the logged-in user is the winner
                    const loggedInUserId = itemDetailsContainer.dataset.userId;
                    if (winner.user_id === parseInt(loggedInUserId)) {
                        const proceedButton = document.createElement('button');
                        proceedButton.textContent = 'Proceed';
                        proceedButton.addEventListener('click', function() {
                            // Display the full information
                            winnerContainer.innerHTML = `
                                <div class="winner-info">
                                    <h3>Bidding Transaction</h3>
                                    <hr>
                                    <p><strong>Item Name:</strong> ${winner.item_name}</p>
                                    <p><strong>Description:</strong> ${winner.description}</p>
                                    <p><strong>Total Bid Amount:</strong> ${winner.bid_amount}</p>
                                    <p><strong>Win Date:</strong> ${winner.win_date}</p>
                                    <p><strong>Win Time:</strong> ${winner.win_time}</p>
                                    <img src="data:image/jpeg;base64,${winner.item_picture}" alt="Item Picture">
                                    <hr>
                                    <h4>Sender:</h4>
                                    <p><strong>Name:</strong> ${winner.a_fname} ${winner.a_lname}</p>
                                    <p><strong>Contact Number:</strong> ${winner.a_cnumber}</p>
                                    <p><strong>Address:</strong> ${winner.a_address}</p>
                                    <p><strong>Email:</strong> ${winner.a_email}</p>
                                    <hr>
                                    <h4>Recipient:</h4>
                                    <p><strong>Name:</strong> ${winner.fname} ${winner.lname}</p>
                                    <p><strong>Contact Number:</strong> ${winner.cnumber}</p>
                                    <p><strong>Address:</strong> ${winner.address}</p>
                                    <p><strong>Email:</strong> ${winner.email}</p>
                                </div>
                            `;

                            // Check if winner has a bid transaction
                            if (winner.status !== null && winner.status !== '') {
                                // User has already placed a bid transaction
                                const transactionContainer = document.createElement('div');
                                transactionContainer.id = 'transactionContainer';
                                transactionContainer.innerHTML = `
                                    <hr>
                                    <h4>Transaction Details</h4>
                                    <p><strong>Payment Method:</strong> ${winner.transaction_type}</p>
                                    <p><strong>Status:</strong> ${winner.status}</p>
                                `;
                                // Add color indication based on status
                                let statusColor;
                                switch (winner.status.toLowerCase()) {
                                    case 'ongoing':
                                        statusColor = 'lightyellow';
                                        break;
                                    case 'shipped':
                                        statusColor = 'lightblue';
                                        break;
                                    case 'delivered':
                                        statusColor = 'lightgreen';
                                        break;
                                    default:
                                        statusColor = 'black'; // Default color if status is not recognized
                                }
                                transactionContainer.style.backgroundColor = statusColor;
                                proceedButton.style.display = 'none';

                                winnerContainer.appendChild(transactionContainer);
                            } else {
                                // User has not yet placed a bid transaction
                                const transactionForm = `
                                    <div id="transactionContainer">
                                        <hr>
                                        <h4>Transaction Details</h4>
                                        <br>
                                        <form id="transactionForm" method="POST">
                                            <label for="paymentMethod">Select Payment Method:</label>
                                            <select id="paymentMethod" name="paymentMethod">
                                                <option value="GCash">GCash</option>
                                                <option value="Paymaya">Paymaya</option>
                                                <option value="Cash on Delivery">Cash on Delivery</option>
                                            </select>
                                            <button type="submit" name="placeBidTransaction">Place Bid Transaction</button>
                                        </form>
                                    </div>
                                `;
                                winnerContainer.innerHTML += transactionForm;

                                // Hide the "Proceed" button
                                proceedButton.style.display = 'none';

                                // Add event listener for form submission
                                document.getElementById('transactionForm').addEventListener('submit', function(event) {
                                    event.preventDefault();
                                    const paymentMethod = document.getElementById('paymentMethod').value;
                                    // Make an AJAX request to store the transaction details
                                    fetch('storedTransac.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json'
                                        },
                                        body: JSON.stringify({
                                            item_id: item_id,
                                            winner_id: winner.user_id,
                                            transaction_type: paymentMethod
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        // Handle the response
                                        alert('Transaction successful. Transaction ID: ' + data.transaction_id);
                                        // Update UI with transaction details
                                        const transactionContainer = document.createElement('div');
                                        transactionContainer.id = 'transactionContainer';
                                        transactionContainer.innerHTML = `
                                            <hr>
                                            <h4>Transaction Details</h4>
                                            <br>
                                            <p><strong>Payment Method:</strong> ${paymentMethod}</p>
                                            <p><strong>Status:</strong> Ongoing</p>
                                        `;
                                        transactionContainer.style.backgroundColor = 'yellow'; 
                                        winnerContainer.appendChild(transactionContainer);
                                    })
                                    .catch(error => {
                                        console.error('Error storing transaction:', error.message);
                                        alert('Error storing transaction: ' + error.message);
                                    });
                                });
                            }
                        });
                        itemDetailsContainer.appendChild(proceedButton);
                    }
                })
                .catch(error => {
                    console.error('Error fetching winner:', error.message);
                    alert('Error fetching winner: ' + error.message);
                });
            }

        </script>

    </body>

</html>
