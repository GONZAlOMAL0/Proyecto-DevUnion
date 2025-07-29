
<?php
    // session_start();

    $servername = "52.0.219.41";
    $usernamedb = "mauro";
    $passworddb = "eccbc87e4b5ce2fe28308fd9f2a7baf3";
    $dbname = "devuniontest";
    
    $conn = new mysqli($servername, $usernamedb, $passworddb, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    ?>