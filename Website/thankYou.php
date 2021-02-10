<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thank You - #A2G Campaign - FishingTAG</title>

        <!-- Custom CSS Stylesheet -->
        <link rel="stylesheet" href="Assets\CSS\styles.css">
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
                        <a href="index.html"><img src="Assets/Images/피싱태그 로고만.png" alt=""></a>
                    </div>

                    <!-- Re-directs to section of webpage -->
                    <ul class="pageLink displayPageLink">
                        <!-- About the campaign -->
                        <li><button onclick="AboutCampaign();" class="buttonHoverMargin"><p>About the campaign</p></button></li>
                        <!-- How it works -->
                        <li><button onclick="HowItWorks();" class="buttonHoverMargin"><p>How it works</p></button></li>
                        <!-- Donate Now Button -->
                        <li>
                            <button id="buttonNav" onclick="DonateNow();"><p>Donate</p></button>
                        </li>
                    </ul>

                    <!-- Donate button for mobile view -->
                    <div class="display-on-mobile-only" onclick="openNav()">
                        <button id="buttonNav" onclick="scrollToDonateNow();"><p class="mobile_donate_button">Donate</p></button> 
                    </div>
                </div>
            </nav>

            <!-- Donation Form -->
            <div class="how-it-works" id="how-it-works">
                <h1>You're all set!</h1>
                <h2 id="thankYouText">You'll recieve an email with confirmation of your donation. We will also keep you posted with any updates to the campaign via email and our campaign website</h2>
                <div class="steps">
                    <div class="image-contents">
                        <img src="Assets\Images\개인정보(600x400).png" alt="">
                    </div>
                    <div class="text-contents">
                        <h3>Donate and fill in shipping details</h3>
                        <p>Click on the donate button and fill in your shipping details to receive your smart measure tag and certificate of donation</p>
                    </div>
                </div>

                <div class="steps">
                    <div class="text-contents">
                        <h3>Receive the certificate and smart measure</h3>
                        <p>You'll receive your certificate of donation with steps to registering for the tournament and your smart measure tag</p>
                    </div>
                    <div class="image-contents">
                        <img src="Assets\Images\줄자_기부증서_600x400.png" alt="">
                    </div>
                </div>
                    
                <div class="steps">
                    <div class="image-contents">
                        <img src="Assets\Images\핸드폰이미지_600x400.png" alt="">
                    </div>
                    <div class="text-contents">
                        <h3>Download 'FishingTAG' and join the tournament</h3>
                        <ol>
                            <li id="ordNum"><p>Download the 'FishingTAG' app on iOS and Android using the QR Codes bellow</p></li>
                            <li id="ordNum">
                                <p>Click on the pop-up for the A2G Tournament (Or go to Tournament -> Official -> A2G tournament)</p>
                            </li>
                            <li id="ordNum"><p>Finally click on the 'register’ button and check the participants list to make sure you are in the tournament</p></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="parallax">
                <!-- FishingTAG Logo -->
                <div class="footerLogo">
                    <a href="index.php"><img src="Assets/Images/피싱태그 로고_150x100.png" alt=""></a>
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