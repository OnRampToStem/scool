<?php
// initialize the session 
// (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id)
session_start();

// for display purposes
header('Content-type: text/plain');

// globals
$result = false;

// receiving $_POST inputs
$user = $_POST["user"]; // holds student email
$ch = (int)$_POST["ch"]; // holds single chapter digit
$sec = (int)$_POST["sec"]; // holds single section digit
$lo = (int)$_POST["lo"]; // holds single lo digit

// read and decode the student's respective openStax JSON file (text => PHP assoc array)
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "-" . $_SESSION['section_id'] . "/openStax/" . $user . ".json";
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