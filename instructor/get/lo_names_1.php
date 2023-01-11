<?php
/*
    This php script will return a php associate array, the key being the [learning outcome number]
    and the value being the [learning outcome name].
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

// receiving $_POST inputs
$ch_index = (int)$_POST["chapter"]; // holds single chapter digit (ex: 1)
$sec_index = (int)$_POST["section"]; // holds single section digit (ex: 2)

// globals
$los_data = []; // $los_data will be an assoc array holding: "lo number" => "lo name" / (1.2.3 => Math Name)

// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);
// loop through every chapter until finding selected chapter
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        // loop through every section until finding selected section
        foreach($chapter["Sections"] as $section){
            if($section["Index"] === $sec_index){
                // loop through every learning outcome and collect data
                foreach($section["LearningOutcomes"] as $lo){
                    $los_data[$chapter["Index"] . "." . $section["Index"] . "." . $lo["Index"]] = $lo["Name"];
                }
                break;
            }
        }
        break;
    }
}

//print_r($los_data);

// send back json_encode los_data
echo json_encode($los_data);

?>