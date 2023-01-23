<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if($_SESSION["type"] !== "Learner"){
    header("location: ../register_login/logout.php");
    exit;
}

// for display purposes
//header("Content-type: text/plain");

// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
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