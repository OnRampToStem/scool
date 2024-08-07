<?php

/*
 * Student Centered Open Online Learning (SCOOL) LTI Integration
 * Copyright (c) 2021-2024  Fresno State University, SCOOL Project Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

require_once "../../bootstrap.php";

// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if($_SESSION["type"] !== "Learner"){
    header("location: /register_login/logout.php");
    exit;
}

/*
    This PHP script will do 2 main things:
    1. Update the user's json file by rewriting it with the new data received by the post.
    2. Send back the updated list of updated questions all with the same tags(lo).
*/

// globals (sent by POST)
$pkey = $_POST["pkey"];
$numCurrentTries = $_POST["numCurrentTries"];
$correct = $_POST["correct"];
$tags = $_POST["tags"];
$startDate = $_POST["startDate"];
$endDate = $_POST["endDate"];

if($_SERVER["REQUEST_METHOD"] === "POST"){

    /* UPDATING USER JSON PHP ASSOC ARRAY */
    // read and decode the user JSON file (text => PHP assoc array)
    $json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
    $json = file_get_contents($json_filename);
    $json_data = json_decode($json, true);

    // loop through the PHP assoc array
    foreach($json_data as $key => $value){

        // update the question where pkeys match
        if($value["pkey"] == $pkey){
            // updating attribute values
            $json_data[$key]["numCurrentTries"] = $numCurrentTries;
            $json_data[$key]["correct"] = $correct;
            $json_data[$key]["datetime_started"] = $startDate;
            $json_data[$key]["datetime_answered"] = $endDate;

            // save iterations and break out of loop, because pkey is unique
            break;
        }

    }

    /* REWRITING USER JSON FILE */
    // update user file with new content in $json_data
    $myfile = fopen(USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json", "w") or die("Unable to open file!");

    fwrite($myfile, "[\n");

    // loop to write to file
    $totalQuestions = count($json_data);
    $counter = 1;
    foreach($json_data as $question){

        // get the total number of options in the question
        $options_length = count($question["options"]);

        if($counter == $totalQuestions){
            // no comma, because it is the last math question
            $db_string = "{\n\"pkey\":" . $question["pkey"] . ", \n\"title\":\"" . $question["title"] . "\", \n\"text\":\"" . $question["text"] . "\", \n\"pic\":\"" . $question["pic"] . "\", \n\"numTries\":\"" . $question["numTries"] . "\", \n\"options\": [";

            // insert each option into $db_string
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    $db_string .= "\"" . $question["options"][$i] . "\"], ";
                }
                else{
                    $db_string .= "\"" . $question["options"][$i] . "\",";
                }
            }
            // insert each rightAnswer into $db_string
            $db_string .= "\n\"rightAnswer\": [";
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    if($question["rightAnswer"][$i] == 1){
                        $db_string .= "true], ";
                    }
                    else{
                        $db_string .= "false], ";
                    }
                }
                else{
                    if($question["rightAnswer"][$i] == 1){
                        $db_string .= "true,";
                    }
                    else{
                        $db_string .= "false,";
                    }
                }
            }
            // insert each isImage into $db_string
            $db_string .="\n\"isImage\": [";
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    if($question["isImage"][$i] == 1){
                        $db_string .= "true], ";
                    }
                    else{
                        $db_string .= "false], ";
                    }
                }
                else{
                    if($question["isImage"][$i] == 1){
                        $db_string .= "true,";
                    }
                    else{
                        $db_string .= "false,";
                    }
                }
            }

            $db_string .=  "\n\"tags\":\"" . $question["tags"] . "\", \n\"difficulty\":\"" . $question["difficulty"] . "\", \n\"selected\":\"" . $question["selected"] . "\", \n\"numCurrentTries\":\"" . $question["numCurrentTries"] . "\", \n\"correct\":\"" . $question["correct"] . "\", \n\"datetime_started\":\"" . $question["datetime_started"] . "\", \n\"datetime_answered\":\"" . $question["datetime_answered"] . "\", \n\"createdOn\":\"" . $question["createdOn"] . "\"\n}\n";

            fwrite($myfile, $db_string);
        }
        else{
            // normal write
            $db_string = "{\n\"pkey\":" . $question["pkey"] . ", \n\"title\":\"" . $question["title"] . "\", \n\"text\":\"" . $question["text"] . "\", \n\"pic\":\"" . $question["pic"] . "\", \n\"numTries\":\"" . $question["numTries"] . "\", \n\"options\": [";

            // insert each option into $db_string
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    $db_string .= "\"" . $question["options"][$i] . "\"], ";
                }
                else{
                    $db_string .= "\"" . $question["options"][$i] . "\",";
                }
            }
            // insert each rightAnswer into $db_string
            $db_string .= "\n\"rightAnswer\": [";
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    if($question["rightAnswer"][$i] == 1){
                        $db_string .= "true], ";
                    }
                    else{
                        $db_string .= "false], ";
                    }
                }
                else{
                    if($question["rightAnswer"][$i] == 1){
                        $db_string .= "true,";
                    }
                    else{
                        $db_string .= "false,";
                    }
                }
            }
            // insert each isImage into $db_string
            $db_string .="\n\"isImage\": [";
            for($i = 0; $i < $options_length; $i++){
                if($i == $options_length - 1){
                    if($question["isImage"][$i] == 1){
                        $db_string .= "true], ";
                    }
                    else{
                        $db_string .= "false], ";
                    }
                }
                else{
                    if($question["isImage"][$i] == 1){
                        $db_string .= "true,";
                    }
                    else{
                        $db_string .= "false,";
                    }
                }
            }

            $db_string .=  "\n\"tags\":\"" . $question["tags"] . "\", \n\"difficulty\":\"" . $question["difficulty"] . "\", \n\"selected\":\"" . $question["selected"] . "\", \n\"numCurrentTries\":\"" . $question["numCurrentTries"] . "\", \n\"correct\":\"" . $question["correct"] . "\", \n\"datetime_started\":\"" . $question["datetime_started"] . "\", \n\"datetime_answered\":\"" . $question["datetime_answered"] . "\", \n\"createdOn\":\"" . $question["createdOn"] . "\"\n},\n";

            fwrite($myfile, $db_string);
        }

        $counter++;

    }
    fwrite($myfile, "]\n");
    fclose($myfile);

    /* SEND BACK UPDATED DATA */
    // assume $search_tags input is sanitized and clean
    $search_tags = $tags;

    // read and decode the new user JSON file (text => PHP assoc array)
    $json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
    $json = file_get_contents($json_filename);
    $json_data = json_decode($json, true);

    // starting json string
    $selected_questions = "[";

    // loop through the PHP assoc array
    foreach($json_data as $question){

        // insert the question into $selected_questions if $search_tags match
        if($question["tags"] == $search_tags && $question["selected"] === "true"){

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

    // removing last comma from the string
    $selected_questions = substr($selected_questions, 0, -1);
    // completing the json response string
    $selected_questions .= "]";
    // $selected_questions can now be parsed in the client-side to display data
    echo $selected_questions;

}
