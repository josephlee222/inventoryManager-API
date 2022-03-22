<?php

// SQL Connect for PHP CommonTools
// Joseph Lee 2020-2021
// Configuration file for connecting to the database

//Connection details
$db_hostname = "localhost";
$db_username = "days_project";
$db_password = "daysproject123";
$db_name = "days_project";
$content_src = "localhost";


//Connect to the database
$db_connect = mysqli_connect($db_hostname, $db_username, $db_password, $db_name);

if (!$db_connect) {
    //In case mySQL connection failed
    die("Connection Failed" . mysqli_connect_error($db_connect));
}
?>