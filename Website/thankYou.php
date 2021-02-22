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
                        <a href="index.php"><img src="Assets/Images/피싱태그 로고만.png" alt=""></a>
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

            <!-- What's Next -->
            <div class="how-it-works" id="how-it-works">
                <h1>You're all set!</h1>
                <h2 id="thankYouText">You'll recieve an email with confirmation of your donation. We will also keep you posted with any updates to the campaign via email and our campaign website</h2>
                <div class="whatNext">
                    <div class="image-contents">
                        <img id = "QR_img" src="Assets\Images\ios 큐알코드.png" alt="">
                        <a href="https://apps.apple.com/app/id1459843522?l=en"><h3 id="downloadApp">Download from the Apple App Store</h3></a>
                    </div>
                    <div class="text-contents">
                        <img id = "QR_img" src="Assets\Images\안드로이드qr코드.png" alt="">
                        <a href="https://play.google.com/store/apps/details?id=com.fishingtag"><h3 id="downloadApp">Download from the Google Play Store</h3></a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer id="thankYouFooter" class="parallax">
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