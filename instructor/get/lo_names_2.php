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

    // receiving $_POST inputs
    $post_ch = $_POST["chapter"];  // holds single chapter digit (ex: 1)
    $post_sec = $_POST["section"]; // holds single section digit (ex: 2)
    $los = [];          // array holding every learning outcome in the db
    $selected_los = []; // php associative array

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
        // convert the learning outcome string into an array based on ch, sec, lo digits
        $arr = explode(".", $lo);

        // continue only if the current chapter is equal to the chapter received via POST
        if ($arr[0] === $post_ch) {

            // continue only if the current section is equal to the section received via POST
            if ($arr[1] === $post_sec) {
                // insert lo number into array
                if (!in_array($lo, $selected_los)) {
                    $selected_los[$lo] = "";
                }
            }
        }
    }

    // sort keys numerically
    ksort($selected_los);

    // now extract from openStax the learning outcome names corresponding to those learning outcomes
    // filepath
    $json_filename = "../../assets/json_data/openStax.json";
    // read the openStax.json file to text
    $json_txt = file_get_contents($json_filename);
    // decode the text into a PHP assoc array
    $openStax = json_decode($json_txt, true);

    // loop through each chapter until finding selected chapter digit
    foreach ($openStax as $chapter){
        if ($chapter["Index"] === (int)$post_ch){
            // loop through each section until finding selected section digit
            foreach ($chapter["Sections"] as $section){
                if ($section["Index"] === (int)$post_sec) {
                    // loop through each lo and save the lo name if that lo exists in $selected_los array
                    foreach ($section["LearningOutcomes"] as $learningoutcome) {
                        if (array_key_exists(($chapter["Index"] . "." . $section["Index"] . "." . $learningoutcome["Index"]), $selected_los)) {
                            $selected_los[$chapter["Index"] . "." . $section["Index"] . "." . $learningoutcome["Index"]] = $learningoutcome["Name"];
                        }
                    }
                }
            }
        }
    }

    // send back secs
    echo json_encode($selected_los);
}

?>