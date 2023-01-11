<?php
/*
    This php script will return a php associate array, the key being the [chapter number]
    and the value being the [chapter name].
*/

// for display purposes
//header("Content-type: text/plain");

// initialize the session (access to user: loggedIn, first_name, email, type)
session_start();
  
// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// globals
$chapters_data = []; // $chapters_data will be an assoc array holding: "chapter number" => "chapter name"

// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);
// loop through each chapter
foreach($json_openStax as $chapter){
    $chapters_data[$chapter["Index"]] = $chapter["Name"];
}

//print_r($chapters_data);

// send back chapters_data 
echo json_encode($chapters_data);

?>