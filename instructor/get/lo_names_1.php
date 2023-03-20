<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor"){
    header("location: ../../register_login/logout.php");
    exit;
}

/*
    This php script will return a php associate array, the key being the [learning outcome number]
    and the value being the [learning outcome name].
*/

// for display purposes
//header("Content-type: text/plain");

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