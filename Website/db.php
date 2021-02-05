<?php
    $db = mysqli_connect('localhost', 'root', '');
    if ( $db === FALSE ) die('Fail message');
    if ( mysqli_select_db($db, "a2g-campaign") === FALSE ) die("Fail message");
?>  