<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>#A2G Campaign - FishingTAG</title>

        <!-- Custom CSS Stylesheet -->
        <link rel="stylesheet" href="Assets\CSS\styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet"> 
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet"> 
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

            <!-- About the campaign -->
            <div class="about parallax">

                <!-- Title, company logo and info about campaign -->
                <header id="about-campaign">
                    <h1>Apart to be Together Campaign</h1>
                    <h2>#A2G</h2>

                    <div class="companyLogo">
                        <!-- Promotional Video -->
                        <video src="Assets/Images/yt1s.com - FISHINGTAG Confirm 001 1_1080p.mp4"  autoplay="" controls="" loop="" muted="muted"></video>
                        <p>As an online fishing tournament platform, FishingTAG recognizes the need of a ‘remote platform’ for users. There's been a significant increase in new signed-up users during the COVID-19 pandemic. Our platform is a sanctuary for anglers that allow them to enjoy fishing and competing with others, without physically being around them.</p>
                        <p>We want all our users to overcome the hardship of this pandemic and contribute to fighting this virus in a smart way.</p>
                        <img src="Assets/Images/main image.png" alt="">
                    </div>

                    
                </header>

            </div>

            <!-- Process of participating in the campaign -->
            <div class="how-it-works" id="how-it-works">
                <h1>How does it work?</h1>
                <div class="steps">
                    <div class="image-contents">
                        <img src="Assets\Images\개인정보(600x400).png" alt="">
                    </div>
                    <div class="text-contents">
                        <h3>Donate and fill in shipping details</h3>
                        <br>
                        <p>Click on the donate button and fill in your shipping details to receive your smart measure tag and certificate of donation</p>
                    </div>
                </div>

                <div class="steps">
                    <div class="text-contents">
                        <h3>Receive the certificate and smart measure</h3>
                        <br>
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
                        <br>
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

            <!-- Amount Raised and Link to Donation Form -->
            <div class="donation" id="donate-now">
                <h1>Be a part of the solution!</h1>
                
                <div class="donation-container">
                    <div class="amount-raised">    
                        <p>So far we have raised a total of:</p>
                        <?php
                            require( 'Assets/Library/vendor/autoload.php');
                            require( 'db.php' );
                            
                            maybe_create_database_table($db);
                            
                            save_balance_to_db( 
                                'A2G_Campaign',  // this is just for internal usage in case you are using multiple accounts
                                "sb-dsdpv2192103_api1.business.example.com",  // api user name
                                "GT8U79ZCT9RKE2UU", // api password
                                "ANLMLOT4ZnnQU2HCtWK096e-wEoeAaWkJO1rTxmRs9uN1q6hn-4G3UUh", // signature
                                $db
                            );

                            function save_balance_to_db( $account, $user, $pwd, $signature, $db )
                            {
                                $API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
                                $version = urlencode('51.0');
                                $resArray = CallGetBalance ( $API_Endpoint, $version, $user, $pwd, $signature );
                                $ack = strtoupper ( $resArray ["ACK"] );

                                if ($ack == "SUCCESS") {
                                    for( $i = 0; $i<1; $i++ ){
                                        if( array_key_exists( 'L_AMT' . $i, $resArray ) && array_key_exists( 'L_CURRENCYCODE' . $i, $resArray ) ){
                                            $balance = urldecode ( $resArray[ 'L_AMT' . $i ] );
                                            $currency = urldecode ( $resArray[ 'L_CURRENCYCODE' . $i ] );

                                            $sql = "INSERT INTO paypal(Account, Currency, Balance)VALUES('$account', '$currency', $balance);";
                                            mysqli_query($db, $sql);
                                        }
                                        echo'<h1>'.$currency.' '.$balance.'</h1>';
                                    }
                                }
                            }

                            function maybe_create_database_table($db)
                            {
                                $sql = "CREATE TABLE IF NOT EXISTS paypal(
                                    ID INTEGER PRIMARY KEY AUTO_INCREMENT,
                                    ACCOUNT     CHAR(10)    NOT NULL,
                                    CURRENCY    CHAR(3),
                                    BALANCE     REAL,
                                    TIMESTAMP DATETIME DEFAULT CURRENT_TIMESTAMP
                                );";
                                mysqli_query($db, $sql);
                            }

                            function CallGetBalance($API_Endpoint, $version, $user, $pwd, $signature) 
                            {
                                // setting the curl parameters.
                                $ch = curl_init ();
                                curl_setopt ( $ch, CURLOPT_URL, $API_Endpoint );
                                curl_setopt ( $ch, CURLOPT_VERBOSE, 1 );
                                curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
                                curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
                                curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
                                curl_setopt ( $ch, CURLOPT_POST, 1 );

                                // NVPRequest for submitting to server
                                $nvpreq = "METHOD=GetBalance" . "&RETURNALLCURRENCIES=1" . "&VERSION=" . $version . "&PWD=" . $pwd . "&USER=" . $user . "&SIGNATURE=" . $signature;
                                curl_setopt ( $ch, CURLOPT_POSTFIELDS, $nvpreq );
                                $response = curl_exec ( $ch );

                                $nvpResArray = deformatNVP ( $response );

                                curl_close ( $ch );

                                return $nvpResArray;
                            }

                            /*
                            * This function will take NVPString and convert it to an Associative Array and it will decode the response. 
                              It is usefull to search for a particular key and displaying arrays. @nvpstr is NVPString. @nvpArray is Associative Array.
                            */
                            function deformatNVP($nvpstr) 
                            {
                                $intial = 0;
                                $nvpArray = array ();

                                while ( strlen ( $nvpstr ) ) {
                                    // postion of Key
                                    $keypos = strpos ( $nvpstr, '=' );
                                    // position of value
                                    $valuepos = strpos ( $nvpstr, '&' ) ? strpos ( $nvpstr, '&' ) : strlen ( $nvpstr );

                                    /* getting the Key and Value values and storing in a Associative Array */
                                    $keyval = substr ( $nvpstr, $intial, $keypos );
                                    $valval = substr ( $nvpstr, $keypos + 1, $valuepos - $keypos - 1 );
                                    // decoding the respose
                                    $nvpArray [urldecode ( $keyval )] = urldecode ( $valval );
                                    $nvpstr = substr ( $nvpstr, $valuepos + 1, strlen ( $nvpstr ) );
                                }
                                return $nvpArray;
                            }
                        ?>
                        <p>towards the fight against COVID-19!</p>
                    </div>

                    <div class="link-to-donation-form">    
                        <p>Click on the donate button to contribute and fill in your shipping details in the following pages <br><br>(You will be redirected to Paypal's website in order to proceed!)</p>
                        <br>
                        <!-- <form action="https://www.paypal.com/donate" method="post" target="_top">
                            <input type="hidden" name="hosted_button_id" value="6PQ7PGAM55VFW" />
                            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                            <img alt="" border="0" src="https://www.paypal.com/en_KR/i/scr/pixel.gif" width="1" height="1" />
                        </form> -->

                        <form action="https://www.sandbox.paypal.com/donate" method="post" target="_top">
                            <input type="hidden" name="hosted_button_id" value="7UENJHWANEL5U" />
                            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                            <img alt="" border="0" src="https://www.sandbox.paypal.com/en_KR/i/scr/pixel.gif" width="1" height="1" />
                        </form>  
                        <!-- <button id="dfb"><a id="dfbText" href="">Donate</a></button> -->
                    </div>
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