<?php
/*
    This php script will receive a learning outcome (ex: 1.2.3) and then will loop through
    the static questions json file and will count and sum every instance of that learning
    outcome and return the sum.
*/

// for display purposes
header("Content-type: text/plain");

// initialize the session (access to user: loggedIn, first_name, email, type)
session_start();
  
// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// receiving $_POST input
$lo = $_POST["lo"];
//$lo = "1.2.3";

// globals
// $los_data will be a regular array with just 1 element representing the 
// total number of los
$los_data = [0]; 


// filepath
$json_filename = "../../data/final.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$questions = json_decode($json, true);

// summing number of questions per los
foreach($questions as $question){
    // only the selected lo
    if($question["tags"] === $lo){
        $los_data[0]++;
    }
}

//print_r($los_data);

// send back json_encode los_data
echo json_encode($los_data);

?>