<?php 
    // Import PHPMailer classes into the global namespace
    // These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    require( 'Assets/Library/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require( 'Assets/Library/vendor/phpmailer/phpmailer/src/SMTP.php');
    require( 'Assets/Library/vendor/phpmailer/phpmailer/src/Exception.php');
    require( 'Assets/Library/vendor/autoload.php');
?>

<?php
    //look if the parameter 'tx' is set in the GET request and that it does not have a null or empty value
    if(isset($_GET['tx']) && ($_GET['tx'])!=null && ($_GET['tx'])!= "") 
    {
        $pp_hostname = "www.sandbox.paypal.com"; // Change to www.sandbox.paypal.com to test against sandbox

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-synch';
        $tx_token = $_GET['tx'];
        $auth_token = "qZ3l08UybzfDIdyYvz4Oo-b1qtKPfpgJ1gWXcEx1SDFSkLBXoKF4vGL2rYu";
        $req .= "&tx=$tx_token&at=$auth_token";


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        //set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
        //if your server does not bundled with default verisign certificates.
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
        $res = curl_exec($ch);
        curl_close($ch);

        if(!$res)
        {
            //HTTP ERROR
            exit();
        }
        else
        {
            // parse the data
            $lines = explode("\n", trim($res));
            $keyarray = array();
            if (strcmp ($lines[0], "SUCCESS") == 0) 
            {
                for ($i = 1; $i < count($lines); $i++) 
                {
                    $temp = explode("=", $lines[$i],2);
                    $keyarray[urldecode($temp[0])] = urldecode($temp[1]);
                }
                
                // check the payment_status is Completed
                $paymentStatus = $keyarray["payment_status"];
                if($paymentStatus!="Completed") 
                {
                    exit();
                }

                // check that txn_id has not been previously processed
                require_once "db.php";
                $sql = mysqli_query($db,"SELECT ShippingID FROM shippingList WHERE TransactionID='$tx_token' LIMIT 1");
                $numRows = mysqli_num_rows($sql);
                if ($numRows > 0) 
                {
                    exit();
                }

                // check that receiver_email is your Primary PayPal email
                $myEmail = $keyarray['receiver_email'];
                if ($myEmail != "djibril.coulybaly@gmail.com") 
                {
                    exit();
                }

                // check that payment_amount/payment_currency are correct
                $amount = $keyarray['payment_gross'];
                $currency = $keyarray["mc_currency"];
                

                // Proceed with filling out form details
            }
            else if (strcmp ($lines[0], "FAIL") == 0) 
            {
                // log for manual investigation
            }
        }
    }
    else 
    {
        exit();
    }
?>


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
                                $sql = "INSERT INTO shippingList (FirstName, LastName, EmailAddress, MobileNumber, AddressLine1, AddressLine2, City, State, Zip, TransactionID) VALUES ('$fname', '$lname', '$email', '$mob', '$add1', '$add2', '$c', '$s', '$zip', '$tx_token')";
                                // Inserting the user's details into the database and displaying a confirmation message 
                                // to the user
                                mysqli_query($db, $sql);

                                $mail = new PHPMailer;

                                //Server settings
                                $mail->SMTPDebug = 2;                      // Enable verbose debug output
                                $mail->isSMTP();                                            // Send using SMTP
                                $mail->Host = 'smtp.gmail.com';                                // Set the SMTP server to send through
                                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                                $mail->Username = 'emailtestac2021@gmail.com';                    // SMTP username
                                $mail->Password = 'Emailtestac2021!';                            // SMTP password
                                $mail->SMTPSecure = 'ssl';            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                                $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                                //Recipients
                                $mail->setFrom('emailtestac2021@gmail.com', 'FishingTAG');
                                $mail->addAddress($email);
                        
                                // Content
                                $mail->isHTML(true);                                  // Set email format to HTML
                                $mail->Subject = 'FishingTAG #A2G Campaign Donation Confirmation';
                                $body = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                                <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:v=\"urn:schemas-microsoft-com:vml\">
                                   <head>
                                      <!--[if gte mso 9]>
                                      <xml>
                                         <o:OfficeDocumentSettings>
                                            <o:AllowPNG/>
                                            <o:PixelsPerInch>96</o:PixelsPerInch>
                                         </o:OfficeDocumentSettings>
                                      </xml>
                                      <![endif]-->
                                      <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\"/>
                                      <meta content=\"width=device-width\" name=\"viewport\"/>
                                      <!--[if !mso]><!-->
                                      <meta content=\"IE=edge\" http-equiv=\"X-UA-Compatible\"/>
                                      <!--<![endif]-->
                                      <title></title>
                                      <!--[if !mso]><!-->
                                      <!--<![endif]-->
                                      <style type=\"text/css\">
                                         body {
                                         margin: 0;
                                         padding: 0;
                                         }
                                         table,
                                         td,
                                         tr {
                                         vertical-align: top;
                                         border-collapse: collapse;
                                         }
                                         * {
                                         line-height: inherit;
                                         }
                                         a[x-apple-data-detectors=true] {
                                         color: inherit !important;
                                         text-decoration: none !important;
                                         }
                                      </style>
                                      <style id=\"media-query\" type=\"text/css\">
                                         @media (max-width: 660px) {
                                         .block-grid,
                                         .col {
                                         min-width: 320px !important;
                                         max-width: 100% !important;
                                         display: block !important;
                                         }
                                         .block-grid {
                                         width: 100% !important;
                                         }
                                         .col {
                                         width: 100% !important;
                                         }
                                         .col_cont {
                                         margin: 0 auto;
                                         }
                                         img.fullwidth,
                                         img.fullwidthOnMobile {
                                         max-width: 100% !important;
                                         }
                                         .no-stack .col {
                                         min-width: 0 !important;
                                         display: table-cell !important;
                                         }
                                         .no-stack.two-up .col {
                                         width: 50% !important;
                                         }
                                         .no-stack .col.num2 {
                                         width: 16.6% !important;
                                         }
                                         .no-stack .col.num3 {
                                         width: 25% !important;
                                         }
                                         .no-stack .col.num4 {
                                         width: 33% !important;
                                         }
                                         .no-stack .col.num5 {
                                         width: 41.6% !important;
                                         }
                                         .no-stack .col.num6 {
                                         width: 50% !important;
                                         }
                                         .no-stack .col.num7 {
                                         width: 58.3% !important;
                                         }
                                         .no-stack .col.num8 {
                                         width: 66.6% !important;
                                         }
                                         .no-stack .col.num9 {
                                         width: 75% !important;
                                         }
                                         .no-stack .col.num10 {
                                         width: 83.3% !important;
                                         }
                                         .video-block {
                                         max-width: none !important;
                                         }
                                         .mobile_hide {
                                         min-height: 0px;
                                         max-height: 0px;
                                         max-width: 0px;
                                         display: none;
                                         overflow: hidden;
                                         font-size: 0px;
                                         }
                                         .desktop_hide {
                                         display: block !important;
                                         max-height: none !important;
                                         }
                                         }
                                      </style>
                                   </head>
                                   <body class=\"clean-body\" style=\"margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f8f8f9;\">
                                      <div class=\"preheader\" style=\"display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;\">Thank you for your donation in the #A2G Campaign</div>
                                      <!--[if IE]>
                                      <div class=\"ie-browser\">
                                         <![endif]-->
                                         <table bgcolor=\"#f8f8f9\" cellpadding=\"0\" cellspacing=\"0\" class=\"nl-container\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; min-width: 320px; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f8f8f9; width: 100%;\" valign=\"top\" width=\"100%\">
                                            <tbody>
                                               <tr style=\"vertical-align: top;\" valign=\"top\">
                                                  <td style=\"word-break: break-word; vertical-align: top;\" valign=\"top\">
                                                     <!--[if (mso)|(IE)]>
                                                     <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                        <tr>
                                                           <td align=\"center\" style=\"background-color:#f8f8f9\">
                                                              <![endif]-->
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #1ba8db;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#1ba8db;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#1ba8db\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#1ba8db;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:#f8f8f9;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #2b303a;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#2b303a;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#f8f8f9;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#2b303a\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#2b303a;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]-->
                                                                                                                       <div style=\"font-size:1px;line-height:22px\"> </div>
                                                                                                                       <a href=\"https://www.fishingtag.com/\" style=\"outline:none\" tabindex=\"-1\" target=\"_blank\"><img align=\"center\" alt=\"FishingTAG\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/emailLogo.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 150px; display: block;\" title=\"I'm an image\" width=\"150\"/></a>
                                                                                                                       <div style=\"font-size:1px;line-height:25px\"> </div>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #f8f8f9;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#f8f8f9;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#f8f8f9\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#f8f8f9;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <div align=\"center\" class=\"img-container center fixedwidth\" style=\"padding-right: 40px;padding-left: 40px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 40px;padding-left: 40px;\" align=\"center\">
                                                                                                                       <![endif]--><img align=\"center\" alt=\"I'm an image\" border=\"0\" class=\"center fixedwidth\" src=\"http://www.wemove.uno/Assets/Images/Img5_2x.jpg\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 352px; display: block;\" title=\"I'm an image\" width=\"352\"/>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 50px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 10px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                                                                                                                          <p style=\"font-size: 30px; line-height: 1.2; text-align: center; word-break: break-word; mso-line-height-alt: 36px; margin: 0;\"><span style=\"font-size: 30px; color: #2b303a;\"><strong>Thank you for donating<br/></strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 10px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:10px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; color: #555555; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 15px; line-height: 1.5; text-align: center; word-break: break-word; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 23px; margin: 0;\"><span style=\"color: #808389; font-size: 15px;\">Your donation of $30 will aid towards the fight against COVID-19. We will notify you when you'll receive the certificate of donation and smart measure tag and any updates to the campaign. </span></p>
                                                                                                                          <br>
                                                                                                                          <p style=\"font-size: 15px; line-height: 1.5; text-align: center; word-break: break-word; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 23px; margin: 0;\"><span style=\"color: #808389; font-size: 15px;\">Make sure to sign up for the #A2G Tournament on the <em>FishingTAG</em> app, available on iOS and Android.</span></p>
                                                                                                                          <br>
                                                                                                                          <p style=\"font-size: 15px; line-height: 1.5; text-align: center; word-break: break-word; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 23px; margin: 0;\"><span style=\"color: #808389; font-size: 15px;\">Don't forget to share the campaign with your friends and post your catch using #A2G on all our social platforms!<br/></span></p>
                                                                                                                          <br>
                                                                                                                          <p style=\"font-size: 17px; line-height: 1.5; word-break: break-word; text-align: right; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 26px; mso-ansi-font-size: 18px; margin: 0;\"><span style=\"font-size: 17px; color: #333333; mso-ansi-font-size: 18px;\"><strong><span style=\"\">~Team FishingTAG</span></strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 50px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>TRANSACTION ID<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$tx_token</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>DONATION AMOUNT</strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$30.00</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 3px dotted #2B303A;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; width: 317px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:3px dotted #2B303A; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]--><img align=\"center\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/ios_.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 200px; display: block;\" width=\"200\"/>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <div align=\"center\" class=\"button-container\" style=\"padding-top:40px;padding-right:10px;padding-bottom:0px;padding-left:10px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;\">
                                                                                                                 <tr>
                                                                                                                    <td style=\"padding-top: 40px; padding-right: 10px; padding-bottom: 0px; padding-left: 10px\" align=\"center\">
                                                                                                                       <v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\" href=\"https://apps.apple.com/app/id1459843522?l=en\" style=\"height:46.5pt; width:186pt; v-text-anchor:middle;\" arcsize=\"97%\" stroke=\"false\" fillcolor=\"#1ba8db\">
                                                                                                                          <w:anchorlock/>
                                                                                                                          <v:textbox inset=\"0,0,0,0\">
                                                                                                                             <center style=\"color:#ffffff; font-family:Tahoma, sans-serif; font-size:16px\">
                                                                                                                                <![endif]--><a href=\"https://apps.apple.com/app/id1459843522?l=en\" style=\"-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #1ba8db; border-radius: 60px; -webkit-border-radius: 60px; -moz-border-radius: 60px; width: auto; width: auto; border-top: 1px solid #1ba8db; border-right: 1px solid #1ba8db; border-bottom: 1px solid #1ba8db; border-left: 1px solid #1ba8db; padding-top: 15px; padding-bottom: 15px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;\" target=\"_blank\"><span style=\"padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:undefined;\"><span style=\"font-size: 16px; margin: 0; line-height: 2; word-break: break-word; mso-line-height-alt: 32px;\"><strong>Download for iOS<br/></strong></span></span></a>
                                                                                                                                <!--[if mso]>
                                                                                                                             </center>
                                                                                                                          </v:textbox>
                                                                                                                       </v:roundrect>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; width: 320px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]--><img align=\"center\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/qr.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 200px; display: block;\" width=\"200\"/>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <div align=\"center\" class=\"button-container\" style=\"padding-top:40px;padding-right:10px;padding-bottom:0px;padding-left:10px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;\">
                                                                                                                 <tr>
                                                                                                                    <td style=\"padding-top: 40px; padding-right: 10px; padding-bottom: 0px; padding-left: 10px\" align=\"center\">
                                                                                                                       <v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com:office:word\" href=\"https://play.google.com/store/apps/details?id=com.fishingtag\" style=\"height:46.5pt; width:212.25pt; v-text-anchor:middle;\" arcsize=\"97%\" stroke=\"false\" fillcolor=\"#1ba8db\">
                                                                                                                          <w:anchorlock/>
                                                                                                                          <v:textbox inset=\"0,0,0,0\">
                                                                                                                             <center style=\"color:#ffffff; font-family:Tahoma, sans-serif; font-size:16px\">
                                                                                                                                <![endif]--><a href=\"https://play.google.com/store/apps/details?id=com.fishingtag\" style=\"-webkit-text-size-adjust: none; text-decoration: none; display: inline-block; color: #ffffff; background-color: #1ba8db; border-radius: 60px; -webkit-border-radius: 60px; -moz-border-radius: 60px; width: auto; width: auto; border-top: 1px solid #1ba8db; border-right: 1px solid #1ba8db; border-bottom: 1px solid #1ba8db; border-left: 1px solid #1ba8db; padding-top: 15px; padding-bottom: 15px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; text-align: center; mso-border-alt: none; word-break: keep-all;\" target=\"_blank\"><span style=\"padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:undefined;\"><span style=\"font-size: 16px; margin: 0; line-height: 2; word-break: break-word; mso-line-height-alt: 32px;\"><strong>Download for Android<br/></strong></span></span></a>
                                                                                                                                <!--[if mso]>
                                                                                                                             </center>
                                                                                                                          </v:textbox>
                                                                                                                       </v:roundrect>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #f8f8f9;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#f8f8f9;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#f8f8f9\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#f8f8f9;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #2b303a;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#2b303a;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#2b303a\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#2b303a;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]-->
                                                                                                                       <div style=\"font-size:1px;line-height:40px\"> </div>
                                                                                                                       <a href=\"https://www.fishingtag.com/\" style=\"outline:none\" tabindex=\"-1\" target=\"_blank\"><img align=\"center\" alt=\"Alternate text\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/emailLogo.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 150px; display: block;\" title=\"Alternate text\" width=\"150\"/></a>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <table cellpadding=\"0\" cellspacing=\"0\" class=\"social_icons\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td style=\"word-break: break-word; vertical-align: top; padding-top: 28px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"social_table\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-tspace: 0; mso-table-rspace: 0; mso-table-bspace: 0; mso-table-lspace: 0;\" valign=\"top\">
                                                                                                                          <tbody>
                                                                                                                             <tr align=\"center\" style=\"vertical-align: top; display: inline-block; text-align: center;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.facebook.com/fishingtag/\" target=\"_blank\"><img alt=\"Facebook\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/facebook2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"Facebook\" width=\"32\"/></a></td>
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.instagram.com/fishingtag_official/\" target=\"_blank\"><img alt=\"Instagram\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/instagram2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"Instagram\" width=\"32\"/></a></td>
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.linkedin.com/company/tag-force\" target=\"_blank\"><img alt=\"LinkedIn\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/linkedin2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"LinkedIn\" width=\"32\"/></a></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 15px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:15px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"text-align: center; line-height: 1.5; word-break: break-word; font-size: 17px; mso-line-height-alt: 26px; mso-ansi-font-size: 18px; margin: 0;\"><span style=\"font-size: 17px; color: #ffffff; mso-ansi-font-size: 18px;\"><a href=\"https://www.fishingtag.com/\" rel=\"noopener\" style=\"text-decoration: none;\" target=\"_blank\"><span style=\"color: #ffffff;\">www.fishingtag.com</span></a></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 25px; padding-right: 40px; padding-bottom: 10px; padding-left: 40px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #555961; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 20px; padding-bottom: 30px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.2;padding-top:20px;padding-right:40px;padding-bottom:30px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.2; font-size: 12px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; color: #555555; mso-line-height-alt: 14px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.2; word-break: break-word; text-align: center; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 14px; margin: 0;\"><span style=\"color: #ffffff; font-size: 12px;\">Copyright © 2021 all rights reserved</span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <!--[if (mso)|(IE)]>
                                                           </td>
                                                        </tr>
                                                     </table>
                                                     <![endif]-->
                                                  </td>
                                               </tr>
                                            </tbody>
                                         </table>
                                         <!--[if (IE)]>
                                      </div>
                                      <![endif]-->
                                   </body>
                                </html>
                                ";
                                $message = mb_convert_encoding($body, 'HTML-ENTITIES', "UTF-8");
                                $mail->Body    = $message;
                                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';



                                $mail2 = new PHPMailer;

                                //Server settings
                                $mail2->SMTPDebug = 2;                      // Enable verbose debug output
                                $mail2->isSMTP();                                            // Send using SMTP
                                $mail2->Host = 'smtp.gmail.com';                                // Set the SMTP server to send through
                                $mail2->SMTPAuth   = true;                                   // Enable SMTP authentication
                                $mail2->Username = 'emailtestac2021@gmail.com';                    // SMTP username
                                $mail2->Password = 'Emailtestac2021!';                            // SMTP password
                                $mail2->SMTPSecure = 'ssl';            // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                                $mail2->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                                //Recipients
                                $mail2->setFrom('emailtestac2021@gmail.com', 'FishingTAG');
                                $mail2->addAddress('emailtestac2021@gmail.com');
                        
                                // Content
                                $mail2->isHTML(true);                                  // Set email format to HTML
                                $mail2->Subject = 'FishingTAG #A2G Campaign Donation Confirmation';
                                $body2 = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
                                <html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:v=\"urn:schemas-microsoft-com:vml\">
                                   <head>
                                      <!--[if gte mso 9]>
                                      <xml>
                                         <o:OfficeDocumentSettings>
                                            <o:AllowPNG/>
                                            <o:PixelsPerInch>96</o:PixelsPerInch>
                                         </o:OfficeDocumentSettings>
                                      </xml>
                                      <![endif]-->
                                      <meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\"/>
                                      <meta content=\"width=device-width\" name=\"viewport\"/>
                                      <!--[if !mso]><!-->
                                      <meta content=\"IE=edge\" http-equiv=\"X-UA-Compatible\"/>
                                      <!--<![endif]-->
                                      <title></title>
                                      <!--[if !mso]><!-->
                                      <!--<![endif]-->
                                      <style type=\"text/css\">
                                         body {
                                         margin: 0;
                                         padding: 0;
                                         }
                                         table,
                                         td,
                                         tr {
                                         vertical-align: top;
                                         border-collapse: collapse;
                                         }
                                         * {
                                         line-height: inherit;
                                         }
                                         a[x-apple-data-detectors=true] {
                                         color: inherit !important;
                                         text-decoration: none !important;
                                         }
                                      </style>
                                      <style id=\"media-query\" type=\"text/css\">
                                         @media (max-width: 660px) {
                                         .block-grid,
                                         .col {
                                         min-width: 320px !important;
                                         max-width: 100% !important;
                                         display: block !important;
                                         }
                                         .block-grid {
                                         width: 100% !important;
                                         }
                                         .col {
                                         width: 100% !important;
                                         }
                                         .col_cont {
                                         margin: 0 auto;
                                         }
                                         img.fullwidth,
                                         img.fullwidthOnMobile {
                                         max-width: 100% !important;
                                         }
                                         .no-stack .col {
                                         min-width: 0 !important;
                                         display: table-cell !important;
                                         }
                                         .no-stack.two-up .col {
                                         width: 50% !important;
                                         }
                                         .no-stack .col.num2 {
                                         width: 16.6% !important;
                                         }
                                         .no-stack .col.num3 {
                                         width: 25% !important;
                                         }
                                         .no-stack .col.num4 {
                                         width: 33% !important;
                                         }
                                         .no-stack .col.num5 {
                                         width: 41.6% !important;
                                         }
                                         .no-stack .col.num6 {
                                         width: 50% !important;
                                         }
                                         .no-stack .col.num7 {
                                         width: 58.3% !important;
                                         }
                                         .no-stack .col.num8 {
                                         width: 66.6% !important;
                                         }
                                         .no-stack .col.num9 {
                                         width: 75% !important;
                                         }
                                         .no-stack .col.num10 {
                                         width: 83.3% !important;
                                         }
                                         .video-block {
                                         max-width: none !important;
                                         }
                                         .mobile_hide {
                                         min-height: 0px;
                                         max-height: 0px;
                                         max-width: 0px;
                                         display: none;
                                         overflow: hidden;
                                         font-size: 0px;
                                         }
                                         .desktop_hide {
                                         display: block !important;
                                         max-height: none !important;
                                         }
                                         }
                                      </style>
                                   </head>
                                   <body class=\"clean-body\" style=\"margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #f8f8f9;\">
                                      <div class=\"preheader\" style=\"display:none;font-size:1px;color:#333333;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;\">You  received a new donation in the #A2G Campaign</div>
                                      <!--[if IE]>
                                      <div class=\"ie-browser\">
                                         <![endif]-->
                                         <table bgcolor=\"#f8f8f9\" cellpadding=\"0\" cellspacing=\"0\" class=\"nl-container\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; min-width: 320px; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f8f8f9; width: 100%;\" valign=\"top\" width=\"100%\">
                                            <tbody>
                                               <tr style=\"vertical-align: top;\" valign=\"top\">
                                                  <td style=\"word-break: break-word; vertical-align: top;\" valign=\"top\">
                                                     <!--[if (mso)|(IE)]>
                                                     <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                        <tr>
                                                           <td align=\"center\" style=\"background-color:#f8f8f9\">
                                                              <![endif]-->
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #1ba8db;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#1ba8db;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#1ba8db\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#1ba8db;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:#f8f8f9;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #2b303a;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#2b303a;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#f8f8f9;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#2b303a\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#2b303a;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]-->
                                                                                                                       <div style=\"font-size:1px;line-height:22px\"> </div>
                                                                                                                       <a href=\"https://www.fishingtag.com/\" style=\"outline:none\" tabindex=\"-1\" target=\"_blank\"><img align=\"center\" alt=\"I'm an image\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/emailLogo.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 150px; display: block;\" title=\"I'm an image\" width=\"150\"/></a>
                                                                                                                       <div style=\"font-size:1px;line-height:25px\"> </div>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #f8f8f9;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#f8f8f9;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#f8f8f9\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#f8f8f9;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <div align=\"center\" class=\"img-container center fixedwidth\" style=\"padding-right: 40px;padding-left: 40px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 40px;padding-left: 40px;\" align=\"center\">
                                                                                                                       <![endif]--><img align=\"center\" alt=\"I'm an image\" border=\"0\" class=\"center fixedwidth\" src=\"http://www.wemove.uno/Assets/Images/Img3_2x.jpg\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 352px; display: block;\" title=\"I'm an image\" width=\"352\"/>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 50px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 10px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.2;padding-top:10px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.2; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 14px;\">
                                                                                                                          <p style=\"font-size: 30px; line-height: 1.2; text-align: center; word-break: break-word; mso-line-height-alt: 36px; margin: 0;\"><span style=\"font-size: 30px; color: #2b303a;\"><strong>New Donation Request<br/></strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 10px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:10px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; color: #555555; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 15px; line-height: 1.5; text-align: center; word-break: break-word; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 23px; margin: 0;\"><span style=\"color: #808389; font-size: 15px;\">You've received a new new donation bellow. Please make sure to check on Paypal that the information is correct and proceed to ship out the certificate/smart measure tag.<br/></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 50px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>FIRST NAME<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$fname</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>LAST NAME<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$lname</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>EMAIL ADDRESS<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$email</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>MOBILE NUMBER<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$mob</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>ADDRESS LINE 1<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$add1</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>ADDRESS LINE 2<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$add2</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>CITY<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$c</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>STATE<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$s</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid two-up no-stack\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: none; border-bottom: 0px solid transparent; border-right: 8px solid #FFF;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:20px solid #FFF; border-bottom:0px solid transparent; border-right:8px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>ZIP<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>$zip</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                      <td align=\"center\" width=\"320\" style=\"background-color:#fff;width:320px; border-top: 0px solid transparent; border-left: 8px solid #FFF; border-bottom: 0px solid transparent; border-right: none;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;background-color:#e1f2f9;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num6\" style=\"display: table-cell; vertical-align: top; max-width: 320px; min-width: 318px; background-color: #e1f2f9; width: 292px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:8px solid #FFF; border-bottom:0px solid transparent; border-right:20px solid #FFF; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 5px; padding-left: 5px; padding-top: 35px; padding-bottom: 40px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:35px;padding-right:5px;padding-bottom:40px;padding-left:5px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 18px; margin: 0;\"><span style=\"color: #a2a9ad; font-size: 12px;\"><strong>TRANSACTION ID<br/></strong></span></p>
                                                                                                                          <p style=\"font-size: 20px; line-height: 1.5; text-align: center; word-break: break-word; mso-line-height-alt: 30px; margin: 0;\"><span style=\"color: #2b303a; font-size: 20px;\"><strong>#12345678</strong></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                               <td style='padding-top:0px;padding-bottom:0px' width='20' bgcolor='#FFF'>
                                                                                                  <table role='presentation' width='20' cellpadding='0' cellspacing='0' border='0'>
                                                                                                     <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                     </tr>
                                                                                                  </table>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #fff;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#fff;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#fff\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#fff;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 60px; padding-right: 0px; padding-bottom: 12px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #f8f8f9;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#f8f8f9;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#f8f8f9\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#f8f8f9;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 0px solid #BBBBBB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <div style=\"background-color:transparent;\">
                                                                 <div class=\"block-grid\" style=\"min-width: 320px; max-width: 640px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; Margin: 0 auto; background-color: #2b303a;\">
                                                                    <div style=\"border-collapse: collapse;display: table;width: 100%;background-color:#2b303a;\">
                                                                       <!--[if (mso)|(IE)]>
                                                                       <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\">
                                                                          <tr>
                                                                             <td align=\"center\">
                                                                                <table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:640px\">
                                                                                   <tr class=\"layout-full-width\" style=\"background-color:#2b303a\">
                                                                                      <![endif]-->
                                                                                      <!--[if (mso)|(IE)]>
                                                                                      <td align=\"center\" width=\"640\" style=\"background-color:#2b303a;width:640px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\">
                                                                                         <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                            <tr>
                                                                                               <td style=\"padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:0px;\">
                                                                                                  <![endif]-->
                                                                                                  <div class=\"col num12\" style=\"min-width: 320px; max-width: 640px; display: table-cell; vertical-align: top; width: 640px;\">
                                                                                                     <div class=\"col_cont\" style=\"width:100% !important;\">
                                                                                                        <!--[if (!mso)&(!IE)]><!-->
                                                                                                        <div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\">
                                                                                                           <!--<![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 4px solid #1BA8DB; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <div align=\"center\" class=\"img-container center autowidth\" style=\"padding-right: 0px;padding-left: 0px;\">
                                                                                                              <!--[if mso]>
                                                                                                              <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                                 <tr style=\"line-height:0px\">
                                                                                                                    <td style=\"padding-right: 0px;padding-left: 0px;\" align=\"center\">
                                                                                                                       <![endif]-->
                                                                                                                       <div style=\"font-size:1px;line-height:40px\"> </div>
                                                                                                                       <a href=\"https://www.fishingtag.com/\" style=\"outline:none\" tabindex=\"-1\" target=\"_blank\"><img align=\"center\" alt=\"Alternate text\" border=\"0\" class=\"center autowidth\" src=\"http://www.wemove.uno/Assets/Images/emailLogo.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; width: 100%; max-width: 150px; display: block;\" title=\"Alternate text\" width=\"150\"/></a>
                                                                                                                       <!--[if mso]>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </table>
                                                                                                              <![endif]-->
                                                                                                           </div>
                                                                                                           <table cellpadding=\"0\" cellspacing=\"0\" class=\"social_icons\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td style=\"word-break: break-word; vertical-align: top; padding-top: 28px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" cellpadding=\"0\" cellspacing=\"0\" class=\"social_table\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-tspace: 0; mso-table-rspace: 0; mso-table-bspace: 0; mso-table-lspace: 0;\" valign=\"top\">
                                                                                                                          <tbody>
                                                                                                                             <tr align=\"center\" style=\"vertical-align: top; display: inline-block; text-align: center;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.facebook.com/fishingtag/\" target=\"_blank\"><img alt=\"Facebook\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/facebook2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"Facebook\" width=\"32\"/></a></td>
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.instagram.com/fishingtag_official/\" target=\"_blank\"><img alt=\"Instagram\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/instagram2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"Instagram\" width=\"32\"/></a></td>
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; padding-bottom: 0; padding-right: 10px; padding-left: 10px;\" valign=\"top\"><a href=\"https://www.linkedin.com/company/tag-force\" target=\"_blank\"><img alt=\"LinkedIn\" height=\"32\" src=\"http://www.wemove.uno/Assets/Images/linkedin2x.png\" style=\"text-decoration: none; -ms-interpolation-mode: bicubic; height: auto; border: 0; display: block;\" title=\"LinkedIn\" width=\"32\"/></a></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 15px; padding-bottom: 10px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.5;padding-top:15px;padding-right:40px;padding-bottom:10px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.5; font-size: 12px; color: #555555; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 18px;\">
                                                                                                                          <p style=\"text-align: center; line-height: 1.5; word-break: break-word; font-size: 17px; mso-line-height-alt: 26px; mso-ansi-font-size: 18px; margin: 0;\"><span style=\"font-size: 17px; color: #ffffff; mso-ansi-font-size: 18px;\"><a href=\"https://www.fishingtag.com/\" rel=\"noopener\" style=\"text-decoration: none;\" target=\"_blank\"><span style=\"color: #ffffff;\">www.fishingtag.com</span></a></span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                              <tbody>
                                                                                                                 <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                    <td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 25px; padding-right: 40px; padding-bottom: 10px; padding-left: 40px;\" valign=\"top\">
                                                                                                                       <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"divider_content\" role=\"presentation\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-top: 1px solid #555961; width: 100%;\" valign=\"top\" width=\"100%\">
                                                                                                                          <tbody>
                                                                                                                             <tr style=\"vertical-align: top;\" valign=\"top\">
                                                                                                                                <td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><span></span></td>
                                                                                                                             </tr>
                                                                                                                          </tbody>
                                                                                                                       </table>
                                                                                                                    </td>
                                                                                                                 </tr>
                                                                                                              </tbody>
                                                                                                           </table>
                                                                                                           <!--[if mso]>
                                                                                                           <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
                                                                                                              <tr>
                                                                                                                 <td style=\"padding-right: 40px; padding-left: 40px; padding-top: 20px; padding-bottom: 30px; font-family: Tahoma, sans-serif\">
                                                                                                                    <![endif]-->
                                                                                                                    <div style=\"color:#555555;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;line-height:1.2;padding-top:20px;padding-right:40px;padding-bottom:30px;padding-left:40px;\">
                                                                                                                       <div class=\"txtTinyMce-wrapper\" style=\"line-height: 1.2; font-size: 12px; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; color: #555555; mso-line-height-alt: 14px;\">
                                                                                                                          <p style=\"font-size: 12px; line-height: 1.2; word-break: break-word; text-align: center; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; mso-line-height-alt: 14px; margin: 0;\"><span style=\"color: #ffffff; font-size: 12px;\">Copyright © 2021 all rights reserved</span></p>
                                                                                                                       </div>
                                                                                                                    </div>
                                                                                                                    <!--[if mso]>
                                                                                                                 </td>
                                                                                                              </tr>
                                                                                                           </table>
                                                                                                           <![endif]-->
                                                                                                           <!--[if (!mso)&(!IE)]><!-->
                                                                                                        </div>
                                                                                                        <!--<![endif]-->
                                                                                                     </div>
                                                                                                  </div>
                                                                                                  <!--[if (mso)|(IE)]>
                                                                                               </td>
                                                                                            </tr>
                                                                                         </table>
                                                                                         <![endif]-->
                                                                                         <!--[if (mso)|(IE)]>
                                                                                      </td>
                                                                                   </tr>
                                                                                </table>
                                                                             </td>
                                                                          </tr>
                                                                       </table>
                                                                       <![endif]-->
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                              <!--[if (mso)|(IE)]>
                                                           </td>
                                                        </tr>
                                                     </table>
                                                     <![endif]-->
                                                  </td>
                                               </tr>
                                            </tbody>
                                         </table>
                                         <!--[if (IE)]>
                                      </div>
                                      <![endif]-->
                                   </body>
                                </html>
                                ";
                                $message2 = mb_convert_encoding($body2, 'HTML-ENTITIES', "UTF-8");
                                $mail2->Body    = $message2;
                                $mail2->AltBody = 'This is the body in plain text for non-HTML mail clients';
                        
                                // var_dump($m->send());
                                if ($mail->send() && $mail2->send()) {
                                    header('Location:thankYou.php');
                                    die();
                                }
                                else {
                                    echo 'Sorry, could not send email. Try again later.'; 
                                }
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