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
                        <a href="index.html"><img src="Assets/Images/navLogo.jpg" alt=""></a>
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
            <div class="how-it-works" id="how-it-works">
                <h1>You're all set!</h1>
                <h2 id="thankYouText">You'll recieve an email with confirmation of your donation. We will also keep you posted with any updates to the campaign via email and our campaign website</h2>
                <div class="steps">
                    <div class="image-contents">
                        <img src="Assets\Images\how it works.jpg" alt="">
                    </div>
                    <div class="text-contents">
                        <h3>Step </h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum unde natus deserunt necessitatibus, in aut voluptate quasi fuga labore quaerat hic recusandae dolorum cupiditate nobis facilis voluptatibus, doloribus at enim?</p>
                    </div>
                </div>

                <div class="steps">
                    <div class="text-contents">
                        <h3>Step </h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum unde natus deserunt necessitatibus, in aut voluptate quasi fuga labore quaerat hic recusandae dolorum cupiditate nobis facilis voluptatibus, doloribus at enim?</p>
                    </div>
                    <div class="image-contents">
                        <img src="Assets\Images\how it works.jpg" alt="">
                    </div>
                </div>
                    
                <div class="steps">
                    <div class="image-contents">
                        <img src="Assets\Images\how it works.jpg" alt="">
                    </div>
                    <div class="text-contents">
                        <h3>Step </h3>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum unde natus deserunt necessitatibus, in aut voluptate quasi fuga labore quaerat hic recusandae dolorum cupiditate nobis facilis voluptatibus, doloribus at enim?</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="parallax">
                <!-- FishingTAG Logo -->
                <div class="footerLogo">
                    <img src="Assets/Images/displayLogo.jpg" alt="">
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