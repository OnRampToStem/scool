<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // receive data
    $students = json_decode($_POST["students"]);

    $tot_arr = []; // will hold subarrays containing questions answered by the student

    foreach ($students as $key => $value) {
        // initialize the file path (student's static questions json file)
        $filepath = "../../user_data/{$_SESSION['selected_course_name']}-{$_SESSION['selected_course_id']}/questions/{$value}.json";
        // read the text from the file
        $json_text = file_get_contents($filepath);
        // convert text to PHP assoc array
        $json_data = json_decode($json_text, true);

        // local array
        $arr = [];

        // loop through each question in the file
        for ($i = 0; $i < count($json_data); $i++) {
            if ($json_data[$i]["datetime_answered"] !== "") {
                // create PHP object containing student name & email + question data
                $obj = new stdClass();
                $obj->name = $key;
                $obj->email = $value;
                $obj->tags = $json_data[$i]["tags"];
                $obj->text = $json_data[$i]["text"];
                $obj->numCurrentTries = $json_data[$i]["numCurrentTries"];
                $obj->numTries = $json_data[$i]["numTries"];
                $obj->correct = $json_data[$i]["correct"];
                $obj->datetime_started = $json_data[$i]["datetime_started"];
                $obj->datetime_answered = $json_data[$i]["datetime_answered"];
                /*
                $obj->pkey = $json_data[$i]["pkey"];
                $obj->title = $json_data[$i]["title"];
                $obj->pic = $json_data[$i]["pic"];
                $obj->options = $json_data[$i]["options"];
                $obj->rightAnswer = $json_data[$i]["rightAnswer"];
                $obj->isImage = $json_data[$i]["isImage"];
                $obj->difficulty = $json_data[$i]["difficulty"];
                $obj->selected = $json_data[$i]["selected"];
                $obj->createdOn = $json_data[$i]["createdOn"];
                */
                array_push($arr, $obj);
            }
        }

        array_push($tot_arr, $arr);
    }

    echo json_encode($tot_arr);
}
