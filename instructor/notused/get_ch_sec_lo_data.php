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
$search_tags = "";             // represents the tag(LO) being searched for
$email = "";                // represents the student email the instructor is searching for
$selected_questions = "";      // represents the json response string for questions' tags that match with $search_tags

// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // inputs
    $email = $_POST["email"];
    $search_tags = $_POST["search_tags"];

    // starting json response string for questions matching tags
    $selected_questions .= "[";

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

            // insert the question into $selected_questions if tags match
            if($question["tags"] === $search_tags && $question["selected"] === "true"){

                $selected_questions .= '{"pkey":' . $question["pkey"] . ', "title":"' . $question["title"] . '", "text":"' . $question["text"] . '", "pic":"' . $question["pic"] . '", "numTries":"' . $question["numTries"] . '", "options":[';
                // inserting options
                for($i = 0; $i < count($question["options"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["options"]) - 1){
                        $selected_questions .= '"' . $question["options"][$i] . '"], ';
                    }
                    // add comma to the option
                    else{
                        $selected_questions .= '"' . $question["options"][$i] . '",';
                    }
                }
                // inserting rightAnswer
                $selected_questions .= '"rightAnswer":[';
                for($i = 0; $i < count($question["rightAnswer"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["rightAnswer"]) - 1){
                        if($question["rightAnswer"][$i] == 1){
                            $selected_questions .= 'true], ';
                        }
                        else{
                            $selected_questions .= 'false], ';
                        }
                    }
                    // add comma to the option
                    else{
                        if($question["rightAnswer"][$i] == 1){
                            $selected_questions .= 'true,';
                        }
                        else{
                            $selected_questions .= 'false,';
                        }
                    }
                }
                // inserting isImage
                $selected_questions .= '"isImage":[';
                for($i = 0; $i < count($question["isImage"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["isImage"]) - 1){
                        if($question["isImage"][$i] == 1){
                            $selected_questions .= 'true], ';
                        }
                        else{
                            $selected_questions .= 'false], ';
                        }
                    }
                    // add comma to the option
                    else{
                        if($question["isImage"][$i] == 1){
                            $selected_questions .= 'true,';
                        }
                        else{
                            $selected_questions .= 'false,';
                        }
                    }
                }
                $selected_questions .= '"tags":"' . $question["tags"] . '", "difficulty":"' . $question["difficulty"] . '", "selected":"' . $question["selected"] . '", "numCurrentTries":"' . $question["numCurrentTries"] . '", "correct":"' . $question["correct"] . '", "datetime_started":"' . $question["datetime_started"] . '", "datetime_answered":"' . $question["datetime_answered"] . '", "createdOn":"' . $question["createdOn"] . '"},';

            }

        }

        // if no data in $selected_questions
        if($selected_questions === "["){
            echo("No tags match in JSON file.\n");
        }
        // tags found
        else{
            // removing last comma from the string
            $selected_questions = substr($selected_questions, 0, -1);
            // completing the json response string
            $selected_questions .= "]";
            // $selected_questions can now be parsed in the client-side to display data
            echo $selected_questions;
        }

    }

}

?>