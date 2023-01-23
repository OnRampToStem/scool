<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

/*
    This php script will return a php associate array, the key being the [section number]
    and the value being the [section name].
*/

// for display purposes
//header("Content-type: text/plain");

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