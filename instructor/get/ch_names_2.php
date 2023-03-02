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

// globals
$los = []; // 1D array containing all unique learning outcomes in the 'questions' db
$chs = []; // Associative array in the format: "chapter number" => "chapter name" ("1" => "Chapter One")

// connect to the db
require_once "../../register_login/config.php";

// pg query
$query = "SELECT tags FROM questions;";
$res = pg_query($con, $query);
// error check the pg query
if (!$res) {
    echo "Could not execute: " . $query . "\n Error: " . pg_last_error($con) . "\n";
    exit;
}
else {
    // loop through each row
    while ($row = pg_fetch_row($res)) {
        // only insert the lo into los array if it does not exist
        if (!in_array($row[0], $los)) {
            array_push($los, $row[0]);
        }
    }
}

// loop through each learning outcome and extract just the chapter number
foreach ($los as $lo) {
    $idx = strpos($lo, ".");
    $ch = substr($lo, 0, $idx);
    // only insert the ch into chs array if it does not exist
    if (!in_array($ch, $chs)) {
        $chs[$ch] = "";
    }
}

// sort keys numerically
ksort($chs);

// now extract from openStax the chapter names corresponding to those chapters
// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json_txt = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$openStax = json_decode($json_txt, true);

// loop through each chapter
foreach ($openStax as $chapter){
    if (array_key_exists(strval($chapter["Index"]), $chs)) {
        $chs[strval($chapter["Index"])] = $chapter["Name"];
    }
}

// send back chs
echo json_encode($chs);

?>