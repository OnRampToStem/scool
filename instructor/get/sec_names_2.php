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

// checking for POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // receiving $_POST input
    $post_ch = $_POST["chapter"]; // holds single chapter digit (ex: 1)
    $los = [];  // 1D array containing all unique learning outcomes in the 'questions' db
    $secs = []; // Associative array in the format: "section number" => "section name" ("1.2" => "Section One")

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

    // loop through each learning outcome
    foreach ($los as $lo) {
        // get index of first period
        $idx1 = strpos($lo, ".");
        // get the chapter number (ex: 1)
        $ch = substr($lo, 0, $idx1);

        // continue only if the current chapter is equal to the chapter received via POST
        if ($ch === $post_ch) {
            // get index of second period
            $idx2 = strpos($lo, ".", $idx1 + strlen("."));
            // get section number (ex: 1.2)
            $sec = substr($lo, 0, $idx2);
            // only insert the sec number into secs array if it does not exist
            if (!in_array($sec, $secs)) {
                $secs[$sec] = "";
            }
        }
    }

    // sort keys numerically
    ksort($secs);

    // now extract from openStax the section names corresponding to those sections
    // filepath
    $json_filename = "../../assets/json_data/openStax.json";
    // read the openStax.json file to text
    $json_txt = file_get_contents($json_filename);
    // decode the text into a PHP assoc array
    $openStax = json_decode($json_txt, true);

    // loop through each chapter until finding selected chapter
    foreach($openStax as $chapter){
        if($chapter["Index"] === (int)$post_ch){
            // loop through each section and save the section name if that section exists in $secs array
            foreach($chapter["Sections"] as $section){
                if (array_key_exists(($chapter["Index"] . "." . $section["Index"]), $secs)) {
                    $secs[$chapter["Index"] . "." . $section["Index"]] = $section["Name"];
                }
            }
        }
    }

    // send back secs
    echo json_encode($secs);
}

?>