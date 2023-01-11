<?php
// for display purposes
//header("Content-type: text/plain");
/*
    This PHP script is being used to 
*/

// initialize the session
// (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id)
session_start();
  
// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../../index.html");
    exit;
}

// receiving input
$user = $_POST["user"];

// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "-" . $_SESSION['section_id'] . "/openStax/" . $user . ".json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

$chapters_data = [];

foreach($json_openStax as $chapter) {
    if($chapter["Access"] === "True") {
        $chapters_data[$chapter["Index"]] = "A" . $chapter["Name"];
    }
    else {
        $chapters_data[$chapter["Index"]] = "N" . $chapter["Name"];
    }
}

//print_r($chapters_data);

// send back chapters_data 
$json_encoded_chapters_data = json_encode($chapters_data);
echo $json_encoded_chapters_data;

?>