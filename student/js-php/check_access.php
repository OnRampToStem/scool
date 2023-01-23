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

// globals
$result = false;

// receiving $_POST inputs
$ch = (int)$_POST["ch"]; // holds single chapter digit
$sec = (int)$_POST["sec"]; // holds single section digit
$lo = (int)$_POST["lo"]; // holds single lo digit

// read and decode the student's respective openStax JSON file (text => PHP assoc array)
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// loop through openStax to check access
foreach ($json_data as $chapter) {

    if ($chapter["Index"] === $ch && $chapter["Access"] === "True") {

        foreach ($chapter["Sections"] as $section) {

            if ($section["Index"] === $sec && $section["Access"] === "True") {

                foreach ($section["LearningOutcomes"] as $learningoutcome) {

                    if ($learningoutcome["Index"] === $lo && $learningoutcome["Access"] === "True") {
                        $result = true;
                    }
                }
            }
        }
    }
}

// send back result
echo json_encode($result);

?>