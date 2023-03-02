<?php
// start the session 
// keys: (loggedIn, name, email, type, pic, course_name, course_id)
// or
// keys: (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// display / testing purposes
//header("Content-type: text/plain");

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor"){
    header("location: ../../register_login/logout.php");
    exit;
}

$chs = [];
$new_chs = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // accept POST inputs
    $chs = json_decode($_POST["chs"]);

    // now extract from openStax the chapter names corresponding to those chapters
    // filepath
    $json_filename = "../../assets/json_data/openStax.json";
    // read the openStax.json file to text
    $json_txt = file_get_contents($json_filename);
    // decode the text into a PHP assoc array
    $openStax = json_decode($json_txt, true);

    // loop through each chapter
    foreach ($openStax as $chapter){
        if (in_array(strval($chapter["Index"]), $chs)) {
            $new_chs[strval($chapter["Index"])] = $chapter["Name"];
        }
    }

    // send back chs
    echo json_encode($new_chs);
}

?>