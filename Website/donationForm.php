<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Donation Form - #A2G Campaign - FishingTAG</title>

        <!-- Custom CSS Stylesheet -->
        <link rel="stylesheet" href="Assets\CSS\styles.css">
        <link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet"> 
    </head>
    
    <body>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <nav id="navbar">
                <div class="wrapper nav_container">
                    <!-- Logo -->
                    <div class="logo">
                        <a href="index.html"><img src="Assets/Images/피싱태그 로고_150x100.png" alt=""></a>
                    </div>

                    <!-- Re-directs to section of webpage -->
                    <ul class="pageLink displayPageLink">
                        <!-- About the campaign -->
                        <li><button onclick="scrollToAboutCampaign();" class="buttonHoverMargin"><p>About the campaign</p></button></li>
                        <!-- How it works -->
                        <li><button onclick="scrollToHowItWorks();" class="buttonHoverMargin"><p>How it works</p></button></li>
                        <!-- Donate Now Button -->
                        <li>
                            <button id="buttonNav" onclick="scrollToDonateNow();"><p>Donate</p></button>
                        </li>
                    </ul>

                    <!-- Donate button for mobile view -->
                    <div class="display-on-mobile-only" onclick="openNav()">
                        <button id="buttonNav" onclick="scrollToDonateNow();"><p class="mobile_donate_button">Donate</p></button> 
                    </div>
                </div>
            </nav>

            <!-- Donation Form -->
            <div class="donation" id="donate-now">
                <h1>Thank you for your donation ^_^</h1>
                <h2>All that's left to do is to fill in your shipping details to recieve your Smart Measure Tag!</h2>
                <div class="donation-form">    
                    <?php
                        require_once "db.php";
                        if(isset($_POST['firstName']) && isset($_POST['lastName']) && isset($_POST['emailAddress']) && isset($_POST['mobileNumber']) && isset($_POST['addressLine1']) && isset($_POST['addressLine2']) && isset($_POST['city']) && isset($_POST['state']) && isset($_POST['zipPostcode']))
                        {
                            // Declaring and Initializing variables from the registration form
                            $fname = $_POST['firstName'];
                            $lname = $_POST['lastName'];
                            $email = $_POST['emailAddress'];
                            $mob = $_POST['mobileNumber'];
                            $add1 = $_POST['addressLine1'];
                            $add2 = $_POST['addressLine2'];
                            $c = $_POST['city'];
                            $s = $_POST['state'];
                            $zip = $_POST['zipPostcode'];
                            
                            // Error checking the form to make sure it complies with the specifications given
                            if($fname == '' || $lname == '' || $email == '' || $mob == '' || $add1 == '' || $add2 == '' || $c == '' || $s == '' || $zip == '')
                            {
                                echo "<p class='error'>All fields must be filled in order to continue</p>";
                            }
                            else
                            {
                                $sql = "INSERT INTO shippingList (FirstName, LastName, EmailAddress, MobileNumber, AddressLine1, AddressLine2, City, State, Zip) VALUES ('$fname', '$lname', '$email', '$mob', '$add1', '$add2', '$c', '$s', '$zip')";
                                // Inserting the user's details into the database and displaying a confirmation message 
                                // to the user with a link to return to the login page
                                mysqli_query($db, $sql);
                                header('Location:thankYou.php');
                            }
                        }
                    ?>
                    <form method="POST">
                        <div class="user-box">
                            <input type="text" name="firstName" id="firstName" required="">
                            <label for="firstName">First Name:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="lastName" id="lastName" required="">
                            <label for="lastName">Last Name:</label>
                        </div>

                        <div class="user-box">
                            <input type="email" name="emailAddress" id="emailAddress" required="">
                            <label for="emailAddress">Email Address:</label>
                        </div>

                        <div class="user-box">
                            <input type="tel" name="mobileNumber" id="mobileNumber" required=""> 
                            <label for="mobileNumber">Mobile Number:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="addressLine1" id="addressLine1" required="">
                            <label for="addressLine1">Address Line 1:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="addressLine2" id="addressLine2" required="">
                            <label for="addressLine2">Address Line 2:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="city" id="city" required="">
                            <label for="city">City:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="state" id="state" required="">
                            <label for="state">State:</label>
                        </div>

                        <div class="user-box">
                            <input type="text" name="zipPostcode" id="zipPostcode" required="">
                            <label for="zipPostcode">Zip / Postcode:</label>
                        </div>

                        <button id="dfb" type="submit">Submit</button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <footer class="parallax">
                <!-- FishingTAG Logo -->
                <div class="footerLogo">
                    <img src="Assets/Images/피싱태그 로고_150x100.png" alt="">
                </div>
                
                <!-- Copyright Information -->
                <p>Copyright © 2021 all rights reserved</p>
                <a href="https://www.fishingtag.com" id="footerLink"><p>www.fishingtag.com</p></a>
            </footer>
        </div>

        <!-- Custom Javascripts -->
        <script src="Assets/Javascript/scroll.js"></script>
    </body>
</html>