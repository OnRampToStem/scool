<?php
/*
    This php script will return a php associate array, the key being the [section number]
    and the value being the [section name].
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

// receiving $_POST input
$ch_index = (int)$_POST["chapter"]; // holds single chapter digit (ex: 1)

// globals
$sections_data = []; // $sections_data will be an assoc array holding: "section number" => "section name"

// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// loop through each chapter until finding selected chapter
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        // loop through each section and collect data
        foreach($chapter["Sections"] as $section){
            $sections_data[$chapter["Index"] . "." . $section["Index"]] = $section["Name"];
        }
        break;
    }
}

//print_r($sections_data);

// send back sections_data
echo json_encode($sections_data);

?>