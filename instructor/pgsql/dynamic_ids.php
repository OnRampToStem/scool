<?php
// for display purposes
header('Content-type: text/plain');

// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is 'Mentor' redirect to main page
if ($_SESSION["type"] === "Mentor") {
    header("location: ../instr_index1.php");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

// globals
$dynamic_ids = []; // list of all dynamic question ids extracted from db

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // converting string to php assoc arr
    $assessment_json = json_decode($_POST['assessment_json'], true);

    // connect to the db
    require_once "../../register_login/config.php";

    // create list of randomly chosen dynamic questions
    for ($i = 0; $i < count($assessment_json); $i++) {

        // get rows at random with selected lo
        $query = "SELECT problem_number FROM dynamic_questions WHERE lo_tag = '{$assessment_json[$i]["LearningOutcomeNumber"]}'
                  order by random() limit '{$assessment_json[$i]["NumberQuestions"]}';";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");

        // push data into array
        while ($row = pg_fetch_row($res)) {
            // add 0s to the front of the problem number if the length of the problem number is not 8
            if (strlen($row[0]) !== 8) {
                switch (strlen($row[0])) {
                    case 1:
                        $row[0] = "0000000" . $row[0];
                        break;
                    case 2:
                        $row[0] = "000000" . $row[0];
                        break;
                    case 3:
                        $row[0] = "00000" . $row[0];
                        break;
                    case 4:
                        $row[0] = "0000" . $row[0];
                        break;
                    case 5:
                        $row[0] = "000" . $row[0];
                        break;
                    case 6:
                        $row[0] = "00" . $row[0];
                        break;
                    case 7:
                        $row[0] = "0" . $row[0];
                        break;
                }
            }

            if (!isset($dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]])) {
                $dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]] = [$assessment_json[$i]["NumberPoints"], $row[0]];
            }
            else {
                array_push($dynamic_ids[$assessment_json[$i]["LearningOutcomeNumber"]], $row[0]);
            }
        }

    }
    //print_r($dynamic_ids);
    echo json_encode($dynamic_ids);

}


?>