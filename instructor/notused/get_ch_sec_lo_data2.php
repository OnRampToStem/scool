<?php
// initialize the session (access to user: loggedIn, first_name, email, type)
session_start();

// check if user is logged in, if they are not then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: index.html");
    exit;
}

// if user account type is student redirect to student page (only "instructor" allowed)
if($_SESSION["type"] === "student"){
    header("location: index.php");
    exit;
}

// globals
$email = "";                // represents the student email the instructor is searching for
$chapter_selected = "Select Chapter";
$section_selected = "Select Section";
$learningoutcome_selected = "Select Learning Outcome";
$chapter_info_form = "";
$section_info_form = "";
$learningoutcome_info_form = "";
$total_time_spent = 0;
$total_questions_correct = 0;
$total_questions_incorrect = 0;

$response = "[";


// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // inputs
    $email = $_POST["email"];

    $chapter_selected = $_POST["chapter_selected"];
    $section_selected = $_POST["section_selected"];
    $learningoutcome_selected = $_POST["learningoutcome_selected"];

    // temporarily hold just the number (ch, sec, lo)
    $chapter_info_form = $_POST["chapter_info_form"];
    $section_info_form = $_POST["section_info_form"];
    $learningoutcome_info_form = $_POST["learningoutcome_info_form"];


    // local variables

    // counters for chapter
    $chapter_correct = 0;
    $chapter_incorrect = 0;
    $chapter_time = 0;

    // counters for section
    $section_correct = 0;
    $section_incorrect = 0;
    $section_time = 0;

    // counters for learningoutcome
    $learningoutcome_correct = 0;
    $learningoutcome_incorrect = 0;
    $learningoutcome_time = 0;


    // read email filename
    $json_filename = "../user_data/questions/" . $email . ".json";
    $json = file_get_contents($json_filename);
    // if user input a non existing email
    if($json === false){
        echo($email . " is not in the system.");
        exit;
    }
    else{
        // decode the email JSON file (text => PHP assoc array)
        $json_data = json_decode($json, true);

        // loop through the PHP assoc array
        foreach($json_data as $question){

            /* CHECKING CHAPTER */
            if(strtok($question["tags"], ".") === $chapter_info_form){
                if($question["correct"] === "Yes"){
                    $chapter_correct++;
                }
                if($question["correct"] === "No"){
                    $chapter_incorrect++;
                }
                if($question["datetime_started"] !== ""){
                    // convert timestamp strings to time
                    $start_time = strtotime($question["datetime_started"]);
                    $end_time = strtotime($question["datetime_answered"]);
                    $diff_seconds = abs($end_time - $start_time);
                    $chapter_time += $diff_seconds;
                }
            }

            /* CHECKING SECTION */
            $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
            if(substr($question["tags"], 0, $pos) === $section_info_form){
                if($question["correct"] === "Yes"){
                    $section_correct++;
                }
                if($question["correct"] === "No"){
                    $section_incorrect++;
                }
                if($question["datetime_started"] !== ""){
                    // convert timestamp strings to time
                    $start_time = strtotime($question["datetime_started"]);
                    $end_time = strtotime($question["datetime_answered"]);
                    $diff_seconds = abs($end_time - $start_time);
                    $section_time += $diff_seconds;
                }
            }

            /* CHECKING LEARNINGOUTCOME */
            if($question["tags"] === $learningoutcome_info_form){
                if($question["correct"] === "Yes"){
                    $learningoutcome_correct++;
                }
                if($question["correct"] === "No"){
                    $learningoutcome_incorrect++;
                }
                if($question["datetime_started"] !== ""){
                    // convert timestamp strings to time
                    $start_time = strtotime($question["datetime_started"]);
                    $end_time = strtotime($question["datetime_answered"]);
                    $diff_seconds = abs($end_time - $start_time);
                    $learningoutcome_time += $diff_seconds;
                }
            }

            /* CHECKING TOTALS */
            if($question["datetime_started"] !== ""){
                // convert timestamp strings to time
                $start_time = strtotime($question["datetime_started"]);
                $end_time = strtotime($question["datetime_answered"]);
                $diff_seconds = abs($end_time - $start_time);
                $total_time_spent += $diff_seconds;

                if($question["correct"] === "Yes"){
                    $total_questions_correct++;
                } else{
                    $total_questions_incorrect++;
                }

            }

        }

    // converting from seconds time to H:i:s
    $chapter_time = gmdate("H:i:s", $chapter_time);
    $section_time = gmdate("H:i:s", $section_time);
    $learningoutcome_time = gmdate("H:i:s", $learningoutcome_time);
    $total_time_spent = gmdate("H:i:s", $total_time_spent);

    $response .= '{"total_time": "' . $total_time_spent . '", "total_correct": "' . $total_questions_correct . '", "total_incorrect": "' . $total_questions_incorrect
              . '", "chapter_time": "' . $chapter_time . '", "chapter_correct": "' . $chapter_correct . '", "chapter_incorrect": "' . $chapter_incorrect 
              . '", "section_time": "' . $section_time . '", "section_correct": "' . $section_correct . '", "section_incorrect": "' . $section_incorrect 
              . '", "learningoutcome_time": "' . $learningoutcome_time . '", "learningoutcome_correct": "' . $learningoutcome_correct . '", "learningoutcome_incorrect": "' . $learningoutcome_incorrect . '"}]';
    echo $response;

    }


}






?>