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

/* PHP GLOBALS */
$lo = "";                        // represents the lo being searched for
$selected_questions = "";        // represents the json response string
$final_selected_questions = "";  // represents the final selected json response string
$already_selected = false;       // used to keep track if questions have already been selected
$complete = "";                  // used to keep track if json response string is empty or contains data
$chapter_info_form = "";         // initial empty description of chapter info
$section_info_form = "";         // initial empty description of section info
$learningoutcome_info_form = ""; // initial empty description of lo info
$chapter_selected = "Select a Chapter";                   // initial chapter option selection display
$section_selected = "Select a Section";                   // initial section option selection display
$learningoutcome_selected = "Select a Learning Outcome";  // initial lo option selection display
// total counters
$total_questions_correct = 0;
$total_questions_incorrect = 0;
$total_time_spent = 0;
// chapter counters
$chapter_correct = 0;
$chapter_incorrect = 0;
$chapter_time = 0;
// section counters
$section_correct = 0;
$section_incorrect = 0;
$section_time = 0;
// learning outcome counters
$learningoutcome_correct = 0;
$learningoutcome_incorrect = 0;
$learningoutcome_time = 0;

// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] === "POST"){
                
    // receiving inputs from user
    $lo = $_POST["search_tags"];                                       // holds the lo selected (1.2.3)
    $chapter_selected = $_POST["chapter_selected"];                    // holds the chapter text selected (1. Functions)
    $section_selected = $_POST["section_selected"];                    // holds the section text selected (1.2. Domain and Range)
    $learningoutcome_selected = $_POST["learningoutcome_selected"];    // holds the lo text selected (1.2.3. Finding Domain and Range from Graphs)
    $chapter_info_form = $_POST["chapter_info_form"];                  // temporarily hold the ch selected (1)   
    $section_info_form = $_POST["section_info_form"];                  // temporarily hold the sec selected (1.2)
    $learningoutcome_info_form = $_POST["learningoutcome_info_form"];  // temporarily hold the lo selected (1.2.3)
    // starting the json response strings
    $selected_questions .= "[";
    $final_selected_questions .= "[";

    // filepath
    $json_filename = "../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION["email"] . ".json";
    // read the file to text
    $json = file_get_contents($json_filename);
    // decode the text into a PHP assoc arr
    $json_data = json_decode($json, true);

    // loop through the questions once to determine if there are already selected questions
    // for the lo being searched for
    foreach($json_data as $question){
        if($question["selected"] === "true" && $question["tags"] === $lo){
            $already_selected = true;
            break;
        }
    }

    // start pathway if no questions for that lo have been selected
    if(!$already_selected){

        // loop through the questions
        foreach($json_data as $question){

            // insert all questions into $selected_questions if lo matches
            if($question["tags"] === $lo){
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
            /* CHECKING LEARNINGOUTCOME */

            /* SUMMING TIME SPENT FOR EVERY QUESTION */
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

        // if $selected_questions only contains '[', this means that there was not any questions in the 
        // questions json file with the lo being searched for
        if($selected_questions === "["){
            //echo("No tags match in JSON file.\n");
            $complete = "false";
        }
        // else $selected_questions contains data that we need to deal with
        else{
            // removing last comma from the string
            $selected_questions = substr($selected_questions, 0, -1);
            // completing the json response string
            $selected_questions .= "]";
            // setting complete as true
            $complete = "true";
            // defining local variable
            $maxNumberAssessment = 0;

            // get 'MaxNumberAssessment' from openStax.json
            // read and decode the openStax JSON file (text => PHP assoc array)
            $json_filename = "../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION["email"] . ".json";
            $json = file_get_contents($json_filename);
            $json_data = json_decode($json, true);
        
            // need ch = 1 sec = 2 lo = 3 (single digits only)
            $section_info_form = substr($section_info_form, strpos($section_info_form, ".") + 1, strlen($section_info_form));
            $learningoutcome_info_form = substr($learningoutcome_info_form, strpos($learningoutcome_info_form, ".") + 1, strlen($learningoutcome_info_form));
            $learningoutcome_info_form = substr($learningoutcome_info_form, strpos($learningoutcome_info_form, ".") + 1, strlen($learningoutcome_info_form));

            foreach($json_data as $question){

                if($question["Index"] == $chapter_info_form){

                    for($i = 0; $i < count($question["Sections"]); $i++){

                        if($question["Sections"][$i]["Index"] == $section_info_form){

                            for($j = 0; $j < count($question["Sections"][$i]["LearningOutcomes"]); $j++){

                                if($question["Sections"][$i]["LearningOutcomes"][$j]["Index"] == $learningoutcome_info_form){

                                    $maxNumberAssessment = $question["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"];

                                }
                            }
                        }
                    }
                }
            }

            // use $maxNumberAssessment to remove questions from $selected_questions randomly
            $json_data = json_decode($selected_questions, true);
            $count_json_data = count($json_data);
            // will hold all of the pkeys of the selected questions
            $pkeys_arr = [];
            foreach($json_data as $question){
                array_push($pkeys_arr, $question["pkey"]);
            }

            // will only contain maxNumberAssessment amount of pkeys
            $final_pkeys_arr = [];
            $counter = 0;

            // number of selected questions greater or equal to the number of maxNumberAssessment
            if($count_json_data > $maxNumberAssessment){
                while($counter < $maxNumberAssessment){
                    // rand_num will be 0 - number of selected question minus one
                    $rand_num = rand(0, $count_json_data - 1);
                    $value = $pkeys_arr[$rand_num];
                    if(!in_array($value, $final_pkeys_arr)){
                        array_push($final_pkeys_arr, $value);
                        $counter++;
                    }
                }
            }
            // number of selected questions less than the number of maxNumberAssessment
            else{
                // no need to randomly select questions from $pkeys_arr, instead just obtain the whole number of pkeys
                $final_pkeys_arr = $pkeys_arr;
            }

            // only looping through the initial selected questions
            $json_data = json_decode($selected_questions, true);
            
            // loop through the PHP assoc array
            foreach($json_data as $question){

                if(in_array($question["pkey"], $final_pkeys_arr)){

                    $final_selected_questions .= '{"pkey":' . $question["pkey"] . ', "title":"' . $question["title"] . '", "text":"' . $question["text"] . '", "pic":"' . $question["pic"] . '", "numTries":"' . $question["numTries"] . '", "options":[';
                    // inserting options
                    for($i = 0; $i < count($question["options"]); $i++){
                        // last element -> do not add comma to the option
                        if($i === count($question["options"]) - 1){
                            $final_selected_questions .= '"' . $question["options"][$i] . '"], ';
                        }
                        // add comma to the option
                        else{
                            $final_selected_questions .= '"' . $question["options"][$i] . '",';
                        }
                    }
                    // inserting rightAnswer
                    $final_selected_questions .= '"rightAnswer":[';
                    for($i = 0; $i < count($question["rightAnswer"]); $i++){
                        // last element -> do not add comma to the option
                        if($i === count($question["rightAnswer"]) - 1){
                            if($question["rightAnswer"][$i] == 1){
                                $final_selected_questions .= 'true], ';
                            }
                            else{
                                $final_selected_questions .= 'false], ';
                            }
                        }
                        // add comma to the option
                        else{
                            if($question["rightAnswer"][$i] == 1){
                                $final_selected_questions .= 'true,';
                            }
                            else{
                                $final_selected_questions .= 'false,';
                            }
                        }
                    }
                    // inserting isImage
                    $final_selected_questions .= '"isImage":[';
                    for($i = 0; $i < count($question["isImage"]); $i++){
                        // last element -> do not add comma to the option
                        if($i === count($question["isImage"]) - 1){
                            if($question["isImage"][$i] == 1){
                                $final_selected_questions .= 'true], ';
                            }
                            else{
                                $final_selected_questions .= 'false], ';
                            }
                        }
                        // add comma to the option
                        else{
                            if($question["isImage"][$i] == 1){
                                $final_selected_questions .= 'true,';
                            }
                            else{
                                $final_selected_questions .= 'false,';
                            }
                        }
                    }
                    $final_selected_questions .= '"tags":"' . $question["tags"] . '", "difficulty":"' . $question["difficulty"] . '", "selected":"' . $question["selected"] . '", "numCurrentTries":"' . $question["numCurrentTries"] . '", "correct":"' . $question["correct"] . '", "datetime_started":"' . $question["datetime_started"] . '", "datetime_answered":"' . $question["datetime_answered"] . '", "createdOn":"' . $question["createdOn"] . '"},';
                }
            }
            
            // removing last comma from the string
            $final_selected_questions = substr($final_selected_questions, 0, -1);
            // completing the json response string
            $final_selected_questions .= "]";
            //echo $final_selected_questions;
            // $final_selected_questions can now be parsed in the client-side to display data

            // read and decode the user JSON file (text => PHP assoc array)
            $json_filename = "../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION["email"] . ".json";
            $json = file_get_contents($json_filename);
            $json_data = json_decode($json, true);
            
            // loop through the PHP assoc array to update the 'selected' attribute of selected questions
            foreach($json_data as $key => $value){
                // update the question where pkeys match
                if(in_array($value["pkey"], $final_pkeys_arr)){
                    // updating attribute values
                    $json_data[$key]["selected"] = "true";
                }
            }

            /* REWRITING USER JSON FILE */
            // update user file with new content in $json_data
            $myfile = fopen("../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION["email"] . ".json", "w") or die("Unable to open file!");

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
        }
    }
    // start pathway if questions have already been selected
    else{
        // read and decode the user JSON file (text => PHP assoc array)
        $json_filename = "../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION["email"] . ".json";
        $json = file_get_contents($json_filename);
        $json_data = json_decode($json, true);

        foreach($json_data as $question){

            // insert the question into json response string($final_selected_questions) if tags match
            if($question["tags"] === $lo && $question["selected"] == "true"){
                $final_selected_questions .= '{"pkey":' . $question["pkey"] . ', "title":"' . $question["title"] . '", "text":"' . $question["text"] . '", "pic":"' . $question["pic"] . '", "numTries":"' . $question["numTries"] . '", "options":[';
                // inserting options
                for($i = 0; $i < count($question["options"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["options"]) - 1){
                        $final_selected_questions .= '"' . $question["options"][$i] . '"], ';
                    }
                    // add comma to the option
                    else{
                        $final_selected_questions .= '"' . $question["options"][$i] . '",';
                    }
                }
                // inserting rightAnswer
                $final_selected_questions .= '"rightAnswer":[';
                for($i = 0; $i < count($question["rightAnswer"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["rightAnswer"]) - 1){
                        if($question["rightAnswer"][$i] == 1){
                            $final_selected_questions .= 'true], ';
                        }
                        else{
                            $final_selected_questions .= 'false], ';
                        }
                    }
                    // add comma to the option
                    else{
                        if($question["rightAnswer"][$i] == 1){
                            $final_selected_questions .= 'true,';
                        }
                        else{
                            $final_selected_questions .= 'false,';
                        }
                    }
                }
                // inserting isImage
                $final_selected_questions .= '"isImage":[';
                for($i = 0; $i < count($question["isImage"]); $i++){
                    // last element -> do not add comma to the option
                    if($i === count($question["isImage"]) - 1){
                        if($question["isImage"][$i] == 1){
                            $final_selected_questions .= 'true], ';
                        }
                        else{
                            $final_selected_questions .= 'false], ';
                        }
                    }
                    // add comma to the option
                    else{
                        if($question["isImage"][$i] == 1){
                            $final_selected_questions .= 'true,';
                        }
                        else{
                            $final_selected_questions .= 'false,';
                        }
                    }
                }
                $final_selected_questions .= '"tags":"' . $question["tags"] . '", "difficulty":"' . $question["difficulty"] . '", "selected":"' . $question["selected"] . '", "numCurrentTries":"' . $question["numCurrentTries"] . '", "correct":"' . $question["correct"] . '", "datetime_started":"' . $question["datetime_started"] . '", "datetime_answered":"' . $question["datetime_answered"] . '", "createdOn":"' . $question["createdOn"] . '"},';
            }

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

            /* SUMMING TIME SPENT FOR EVERY QUESTION */
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

        // if no $final_selected_questions contains just '[', means no lo found
        if($final_selected_questions === "["){
            //echo("No tags match in JSON file.\n");
            $complete = "false";
        }
        else{
            // removing last comma from the string
            $final_selected_questions = substr($final_selected_questions, 0, -1);
            // completing the json response string
            $final_selected_questions .= "]";
            //echo $final_selected_questions;
            // $final_selected_questions can now be parsed in the client-side to display data
            $complete = "true";
        }
    }

    // converting from time to H:i:s
    $total_time_spent = gmdate("H:i:s", $total_time_spent);
    $chapter_time = gmdate("H:i:s", $chapter_time);
    $section_time = gmdate("H:i:s", $section_time);
    $learningoutcome_time = gmdate("H:i:s", $learningoutcome_time);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Browse Practice Questions</title>
        <link rel="stylesheet" type="text/css" href="../assets/css/global/or2stem.css" />
        <link rel="stylesheet" type="text/css" href="../assets/css/global/global.css" />
        <link id="css-header" rel="stylesheet" type="text/css" href="" />
        <link id="css-mode" rel="stylesheet" type="text/css" href="" />
        <script type="text/javascript">
            const toggleBanner = () => {
                const cssHeader = document.getElementById("css-header");
                cssHeader.setAttribute("href", `../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
            }

            const toggleCSS = () => {
                const cssLink = document.getElementById("css-mode");
                cssLink.setAttribute("href", `../assets/css/student/student_browse-${window.localStorage.getItem("mode")}-mode.css`);
            }

            // mode
            let item = localStorage.getItem("mode");
            const cssLink = document.getElementById("css-mode");
            if (item === null) {
                window.localStorage.setItem('mode', 'OR2STEM');
                toggleCSS();
            }
            else {
                toggleCSS();
            }

            // banner
            item = localStorage.getItem("banner");
            const cssHeader = document.getElementById("css-header");
            if (item === null) {
                window.localStorage.setItem('banner', 'OR2STEM');
                toggleBanner();
            }
            else {
                toggleBanner();
            }
        </script>
        <script>
            MathJax = {
                loader: { load: ["input/asciimath", "output/chtml"] },
            };
        </script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
        <script type="text/javascript" id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/startup.js"></script>
        <script src="js-php/kmap.js"></script>
    </head>
    <body onload="initialize();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../navigation/settings/settings.php">Settings</a>
                            <a href="../register_login/logout.php">Logout</a>
                        </div>
                        <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A" href="student_index.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <!-- INITIAL LEARNING OUTCOME SELECTION -->
            <div id="group1">
                <h1>Browse Practice Questions</h1>
                <h3>Please select a Chapter, a Section, and a Learning Outcome.</h3>
                <div id="group1_1">
                    <div id="group1_1_1">
                        <p><strong>Chapter</strong></p>
                        <select id="chapter_options" onchange="chapterHelper1();getSectionOptions();">
                            <option selected="selected" disabled><?= $chapter_selected; ?></option>
                        </select>
                    </div>
                    <div id="group1_1_2">
                        <p><strong>Section</strong></p>
                        <select id="section_options" onchange="sectionHelper1();getLoOptions();">
                            <option selected="selected" disabled><?= $section_selected; ?></option>
                        </select>                                
                    </div>
                    <div id="group1_1_3">
                        <p><strong>Learning Outcome</strong></p>
                        <select id="learningoutcome_options">
                            <option selected="selected" disabled><?= $learningoutcome_selected; ?></option>
                        </select>
                    </div>
                </div>
                <!-- HIDDEN FORM USED TO TRANSFER DATA TO PHP CODE ABOVE -->
                <div id="form_div">
                    <form id="main_form" action="" method="post">
                        <input id="search_tags" name="search_tags" type="text" style="display:none">
                        <input id="chapter_selected" name="chapter_selected" type="text" style="display:none">
                        <input id="section_selected" name="section_selected" type="text" style="display:none">
                        <input id="learningoutcome_selected" name="learningoutcome_selected" type="text" style="display:none">
                        <input id="chapter_info_form" name="chapter_info_form" type="text" style="display:none">
                        <input id="section_info_form" name="section_info_form" type="text" style="display:none">
                        <input id="learningoutcome_info_form" name="learningoutcome_info_form" type="text" style="display:none">
                        <input class="btn btn-fsblue" id="go_btn" type="submit" value="Go" onclick="GetTag()"> <!--name="submit"-->
                    </form>
                </div>
            </div>

            <br>

            <!-- mainDiv CONTAINS THE MAIN CONTENT OF THE WEBPAGE -->
            <main id="main">

                <hr><br>

                <!-- LO SELECTION -->
                <div id="selectionInfoHeader">
                    <p id="selected_lo"><?= $learningoutcome_selected; ?></p>
                </div>

                <br><br>

                <!-- CHAPTER INFO -->
                <div class="blue_long_btn" onclick="ToggleChapterInformation()">
                    <p class="blue_long_content">Chapter Information</p>
                    <p id="chapterInfoHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="myChapter"></div>

                <br><br>

                <!-- LEARNING OUTCOME INFO -->
                <div class="blue_long_btn" onclick="ToggleLearningOutcomeInformation()">
                    <p class="blue_long_content">Learning Outcome Information</p>
                    <p id="loInfoHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="myLO">
                <p id="lo_tag"></p>
                <p id="lo_info_next"></p>
                <p id="lo_info_previous"></p>
                <ul>
                    <li style="display:inline; margin-right: 25px;">
                    <a id="lo_link" href="" target="_blank"> Learning Outcome Openstax document </a>
                    </li>
                    <li id="lo_video" style="display:inline; margin-right: 25px; margin-left: 25px;">Video</li>
                    <li id="lo_document" style="display:inline; margin-left: 25px;">Document</li>
                </ul>
                </div>
        
                <br><br><hr style="border: 1px dashed black">

                <!-- TIMER DISPLAY -->
                <div id="timerDiv">
                    <h4 class="timer">Timer:</h4>
                    <h4 class="timer" id="minutes">00</h4> : <h4 class="timer" id="seconds">00</h4>
                </div>

                <br><br>

                <!-- MAIN QUESTION + MOVEMENT BUTTONS + OPTIONS DISPLAY -->
                <div id="questionMain">
                    <div id="leftButtonsDiv">
                        <button class="btn btn-fsblue" id="previousButton" onclick="previous()">Previous Question</button>
                    </div>

                    <div id="questionDisplay">
                        <h3 id="questionHeader" style="text-decoration: underline;"></h3>
                        <h3 id="outcome" style="display:none;"></h3>
                        <div id="quiz">
                            <p id="text"></p>
                            <p id="numTries"></p>
                            <img id="mainImg" src="" alt="" />
                            <div id="optionsDiv"></div>
                        </div>
                    </div>

                    <div id="rightButtonsDiv">
                        <button class="btn btn-fsblue" id="nextButton" onclick="next()">Next Question</button>
                    </div>
                </div>

                <br><br>

                <!-- RESULTS DISPLAY -->
                <div id="resultsDiv">
                    <table id="resultsTable">
                        <thead>
                            <tr>
                                <th class="results_th" scope="col">Attempts</th>
                                <th class="results_th" scope="col">Correct</th>
                                <th class="results_th" scope="col">Correct Answer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="results_td"><p id="numCurrentTries"></p></td>
                                <td class="results_td"><p id="correct"></p></td>
                                <td class="results_td"><p id="correctAnswer"></p></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <br><br><hr style="border: 1px dashed black"><br><br>

                <!-- SHOW TOTAL PROGRESS -->
                <div class="blue_long_btn" onclick="showTotalProgress()">
                    <p class="blue_long_content">Total Progress</p>
                    <p id="totalProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="totalProgressDiv">
                    <p><b>Total Time Spent:</b> <?= $total_time_spent; ?>&emsp;&emsp;<b>Total Questions Correct:</b> <?= $total_questions_correct; ?>&emsp;&emsp;<b>Total Questions Incorrect:</b> <?= $total_questions_incorrect; ?></p>
                </div>        

                <br><br>

                <!-- SHOW CHAPTER PROGRESS -->
                <div class="blue_long_btn" onclick="getChapterData()">
                    <p class="blue_long_content">Chapter Progress</p>
                    <p id="chapterProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="chapterProgressDiv"></div>

                <br><br>

                <!-- SHOW SECTION PROGRESS -->
                <div class="blue_long_btn" onclick="getSectionData()">
                    <p class="blue_long_content">Section Progress</p>
                    <p id="sectionProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="sectionProgressDiv"></div>

                <br><br>

                <!-- SHOW LO PROGRESS -->
                <div class="blue_long_btn" onclick="getLoData()">
                    <p class="blue_long_content">Learning Outcome Progress</p>
                    <p id="loProgressHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="loProgressDiv"></div>

            </main>
      
            <br><br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="student_index.php"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="student_index.php">Home</a></li>
                                <li><a href="../navigation/about-us/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us/contact-us.php">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a href="student_index.php"> CSU SCALE </a></li>
                                <li><a href="http://fresnostate.edu/" target="_blank"> CSU Fresno Homepage </a></li>
                                <li><a href="http://www.fresnostate.edu/csm/csci/" target="_blank"> Department of Computer Science </a></li>
                                <li><a href="http://www.fresnostate.edu/csm/math/" target="_blank"> Department of Mathematics </a></li>
                            </ul>
                        </div>
                        <div class="contact">
                            <h4>Contact Us</h4>
                            <p> 5241 N. Maple Ave. <br /> Fresno, CA 93740 <br /> Phone: 559-278-4240 <br /></p>
                        </div>
                    </div>
                    <div class="footer-bottom">
                        <p>© 2021-2023 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>

        <script type="text/javascript">
            ////////////////
            // JS GLOBALS //
            ////////////////
            let obj;                    // math question content from student openStax question json file in relation to lo selected
            let index = 0;              // index of obj
            let totalQuestions;         // total amount of questions currently in obj
            let correctAnswers = [];    // array containing the correct answers for all the questions in obj
            let numCurrentAttempts = 0; // number of current attempts for the current displayed questions
            let timerID;                // ID of the timer (used to stop the timer)
            let startDate;              // start timestamp of a question
            let chBool = false;         // for selection purposes
            let secBool = false;        // for selection purposes


            const initialize = async () => {
                await GetData();
            }


            //////////////////////
            // HELPER FUNCTIONS //
            //////////////////////
            const readChapterDigit = () => {
                let select = document.getElementById("chapter_options");
                let chapter = select.options[select.selectedIndex].text;
                let idx = chapter.indexOf(".");
                return chapter.slice(0, idx);
            }
            const readSectionDigit = () => {
                let select = document.getElementById("section_options");
                let sectionText = select.options[select.selectedIndex].text;
                let idx1 = sectionText.indexOf(".");
                let idx2 = sectionText.indexOf(".", idx1 + 1);
                return sectionText.slice(idx1 + 1, idx2);
            }
            const chapterHelper1 = () => {
                chBool = true;
            }
            const chapterHelper2 = () => {
                document.getElementById("mainSectionOption").innerHTML = "Select a Section";
                if (document.getElementById("mainLoOption") !== null) {
                    document.getElementById("mainLoOption").innerHTML = "Select a Learning Outcome";
                }
            }
            const sectionHelper1 = () => {
                secBool = true;
            }
            const sectionHelper2 = () => {
                document.getElementById("mainLoOption").innerHTML = "Select a Learning Outcome";
            }
            let hideDisplay = () => {
                document.getElementById("main").style.display = "none";
            }
            let showDisplay = () => {
                document.getElementById("main").style.display = "";
            }



            ///////////////////
            // TIMER PORTION //
            ///////////////////
            let startTimer = () => {
                var sec = 0;
                let pad = (val) => {
                    return val > 9 ? val : "0" + val;
                }
                var timer = setInterval( function() {
                    document.getElementById("seconds").innerHTML=pad(++sec%60);
                    document.getElementById("minutes").innerHTML=pad(parseInt(sec/60,10));
                }, 1000);
                return timer;
            }
            // clearTimer stops the timer and resets the clock back to 0
            let clearTimer = (timerID) => {
                document.getElementById("seconds").innerHTML= "00";
                document.getElementById("minutes").innerHTML= "00";
                clearInterval(timerID);
            } 
            // stopTimer just stops the timer
            let stopTimer = (timerID) => {
                clearInterval(timerID);
            }
            // fxn to pad a number for display purposes of time
            function str_pad_left(string,pad,length) {
                return (new Array(length+1).join(pad)+string).slice(-length);
            }


            // imported from https://stackoverflow.com/questions/2450954/how-to-randomize-shuffle-a-javascript-array
            // will randomly shuffle options array to use for display
            function shuffle(array) {
                let currentIndex = array.length,  randomIndex;
                // While there remain elements to shuffle.
                while (currentIndex != 0) {
                    // Pick a remaining element.
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex--;
                    // And swap it with the current element.
                    [array[currentIndex], array[randomIndex]] = [array[randomIndex], array[currentIndex]];
                }
                return array;
            }


            // parseData function used once at page load 
            let parseData = () => {
                // parse the data from php
                obj = JSON.parse('<?= $final_selected_questions; ?>');
                // shuffle the questions in the obj
                //obj = shuffle(obj);
                // count the total number of questions in the learning objective obj
                totalQuestions = obj.length;
                // then store the correctAnswers
                storeCorrectAnswersAndShuffle();
                // then display the data
                displayData();
            }


            const storeCorrectAnswersAndShuffle = () => {
                // loop through each question in obj
                for (let i = 0; i < obj.length; i++) {

                    // get the correct index
                    let correctIndex = 0;
                    for (let j = 0; j < obj[i]["rightAnswer"].length; j++) {
                        if (obj[i]["rightAnswer"][j] === true) {
                            break;
                        }
                        correctIndex++;
                    }

                    // push the correct answer
                    correctAnswers.push(obj[i]["options"][correctIndex]);

                    // now shuffle the options 
                    obj[i]["options"] = shuffle(obj[i]["options"]);
                }
            }


            // display data from PHP, one question at a time according to index
            let displayData = () => {

                // 1.
                // question has not been answered
                if (obj[index]["datetime_answered"] === "") {

                    // 1. unhide the timer display and start the timer
                    document.getElementById("timerDiv").style.display = "";
                    timerID = startTimer();

                    // 2. set the startDate to be in format (yyyy-mm-dd hh:mm:ss)
                    let date = new Date();
                    startDate = date.getFullYear() + "-" +  ("0" + (date.getMonth() + 1)).slice(-2) + "-" + ("0" + date.getDate()).slice(-2) + " " + ("0" + date.getHours() ).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2) + ":" + ("0" + date.getSeconds()).slice(-2);

                    // 3. displaying number of tries
                    document.getElementById("numTries").innerHTML = "Allowed attempts: " + obj[index]["numTries"];

                }
                // question has been answered
                else {
                    document.getElementById("timerDiv").style.display = "none";
                    document.getElementById("numTries").innerHTML = "";
                }

                // 2.
                // display question index range & title
                document.getElementById("questionHeader").innerHTML = "Question (" + (index + 1) + "/" + totalQuestions + "): " + obj[index]["title"];

                // 3.
                // convert BR back to \n, then display question text prompt
                if (obj[index]["text"].includes("BR")) {
                    obj[index]["text"] = obj[index]["text"].replaceAll("BR", "\n");
                }
                document.getElementById("text").innerHTML = obj[index]["text"];

                // 4.
                // question does not contain images for options (regular presentation of question)
                if (obj[index]["isImage"][0] === false) {
                    // 1. display pic, only if pic file is present
                    if (obj[index]["pic"] === "") {
                        document.getElementById("mainImg").style.display = "none";
                    }
                    else {
                        document.getElementById("mainImg").src = "../assets/img/" + obj[index]["pic"];
                        document.getElementById("mainImg").alt = "main math picture";
                    }

                    // 2. get the correct index
                    let correctIndex = 0;
                    for (let i = 0; i < obj[index]["rightAnswer"].length; i++) {
                        if (obj[index]["rightAnswer"][i] === true) {
                            break;
                        }
                        correctIndex++;
                    }

                    // 5. display options
                    let optionsLength = obj[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++) {
                        str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + obj[index]["options"][i] + '"><label for="option' + i + '" id="label' + i + '">' + obj[index]["options"][i] + '</label><br>';
                    }
                    str += '<button class="btn btn-fsblue" id="checkAnswerButton" type="button" onclick="checkQuestion()">Submit Answer</button></form>';
                    document.getElementById("optionsDiv").innerHTML=str;

                    // 6. display results data
                    if (obj[index]["datetime_answered"] === "") {
                        document.getElementById("correctAnswer").innerHTML = "N/A";
                    }
                    else {
                        document.getElementById("correctAnswer").innerHTML = correctAnswers[index];
                    }

                }
                // question contains images for options
                else {
                    // 1. mainImg will be hidden bc images will be present in options
                    document.getElementById("mainImg").style.display = "none";

                    // 2. get the correct index
                    let correctIndex = 0;
                    for (let i = 0; i < obj[index]["rightAnswer"].length; i++) {
                        if (obj[index]["rightAnswer"][i] === true) {
                            break;
                        }
                        correctIndex++;
                    }

                    // 5. display options
                    let optionsLength = obj[index]["options"].length;
                    let str = '<form id="optionsForm">';
                    for (let i = 0; i < optionsLength; i++) {
                        // some options have ` in them, remove them if found
                        if (obj[index]["options"][i].includes("`")) {
                            obj[index]["options"][i] = obj[index]["options"][i].replaceAll("`", "");
                        }
                        if (i !== 2) {
                            str += '<input id="option' + i + '" type="radio" name="dynamic_option" value="' + obj[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + obj[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + obj[index]["options"][i] + '</label>
                        }
                        else {
                            str += '<br><input id="option' + i + '" type="radio" name="dynamic_option" value="' + obj[index]["options"][i] + '"><img style="width:250px; height:250px;" src="../assets/img/' + obj[index]["options"][i] + '" alt="options_image"/>';
                            //<label for="option' + i + '" id="label' + i + '">' + obj[index]["options"][i] + '</label>
                        }

                    }
                    str += '<br><button class="btn btn-fsblue" id="checkAnswerButton" type="button" onclick="checkQuestion()">Submit Answer</button></form>';
                    document.getElementById("optionsDiv").innerHTML = str;

                    // display results data
                    if (obj[index]["datetime_answered"] === "") {
                        document.getElementById("correctAnswer").innerHTML = "N/A";
                    }
                    else {
                        document.getElementById("correctAnswer").innerHTML = '<img src="../assets/img/' + correctAnswers[index] + '" alt="correct image option" style="width:150px; height:150px"/>';
                    }
                }

                // 5.
                // display results data
                if (obj[index]["numCurrentTries"] === "0") {
                    document.getElementById("numCurrentTries").innerHTML = "N/A";
                }
                else {
                    document.getElementById("numCurrentTries").innerHTML = obj[index]["numCurrentTries"];
                }
                if (obj[index]["correct"] === "") {
                    document.getElementById("correct").innerHTML = "N/A";
                }
                else {
                    document.getElementById("correct").innerHTML = obj[index]["correct"];
                }

                // 6.
                // To use at the end to refresh the presentation of the equations to account for dynamic data
                MathJax.typeset();
            }


            // will only display the bottom result fields
            let displayAfterSubmit = () => {
                document.getElementById("numCurrentTries").innerHTML = obj[index]["numCurrentTries"];

                document.getElementById("correct").innerHTML = obj[index]["correct"];

                document.getElementById("correctAnswer").innerHTML = correctAnswers[index];

                // To use at the end to refresh the presentation of the equations to account for dynamic data
                MathJax.typeset();

                // check for completion of totalQuestions amount of questions
                checkCompletion();
            }


            let checkCompletion = () => {
                // first check that all the questions in the current obj are answered
                let all_answered = true;
                for (let i = 0; i < obj.length; i++) {
                    if (obj[i]["datetime_answered"] === "") {
                        all_answered = false;
                        break;
                    }
                }

                // next only update the student's openStax file if all questions were answered
                if(all_answered) {
                    console.log("Updating openStax");
                    updateOpenStax();
                }
            }


            let req_update;
            let updateOpenStax = () => {
                // collect data to send
                let ch_num = getChapterInfo();

                let tempSection = getSectionInfo();
                let pos1 = tempSection.indexOf(".");
                let sec_num = tempSection.slice(pos1 + 1, tempSection.length);

                let tempLO = getLearningOutcomeInfo();
                pos1 = tempLO.indexOf(".");
                let pos2 = tempLO.indexOf(".", pos1 + 1);
                let lo_num = tempLO.slice(pos2 + 1, tempLO.length);

                // start XMLHttpRequest
                req_update = new XMLHttpRequest();
                req_update.open('POST', 'learning_map/update.php', true);
                req_update.onreadystatechange = updateOpenStaxResponse;
                req_update.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req_update.send("ch_num=" + ch_num + "&sec_num=" + sec_num + "&lo_num=" + lo_num);
            }
            let updateOpenStaxResponse = () =>{
                if (req_update.readyState == 4 && req_update.status == 200) {
                    //console.log("PHP sent back: " + req_update.responseText);
                }
            }


            // fully clears data from the necessary fields
            let clearData = () => {
                // clearing data from questionDisplay div (necessary because some questions might have more complete fields than others)
                document.getElementById("outcome").innerHTML = "";
                document.getElementById("text").innerHTML = "";
                document.getElementById("numTries").innerHTML = "";

                // image is empty
                document.getElementById("mainImg").src = "";
                document.getElementById("mainImg").alt = "";
                document.getElementById("mainImg").style.display = "";

                // clearing label color that may have been assigned
                if(obj[index]["isImage"][0] === false) {
                    let optionsLength = obj[index]["options"].length;
                    for (let i = 0; i < optionsLength; i++) {
                        document.getElementById("label" + i).style.color = "";
                    }              
                }

                document.getElementById("optionsDiv").innerHTML = "";
                document.getElementById("numCurrentTries").innerHTML = "";
                document.getElementById("correct").innerHTML = "";
                document.getElementById("correctAnswer").innerHTML = "";
            }


            let checkQuestion = () => {
                // reveal outcome element
                document.getElementById("outcome").style.display = "";

                // first check if question hasn't been answered yet
                if (obj[index]["datetime_answered"] === "") {

                    // check if user is on his last attempt, must save result after this
                    if (numCurrentAttempts === (parseInt(obj[index]["numTries"]) - 1)) {

                        let correct;
                        // grab the selected option
                        let selectedOption = document.querySelector('input[name="dynamic_option"]:checked').value;
                        
                        // compare the selected option to the correct answer
                        if (selectedOption === correctAnswers[index]) {
                            document.getElementById("outcome").innerHTML = "Correct!";
                            document.getElementById("outcome").style.color = "green";
                            correct = "Yes";
                            numCurrentAttempts++;

                            // only modify the color of a label if it exists
                            if (obj[index]["isImage"][0] === false) {
                                // grabbing input for attribute that is checked by user
                                let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                                // selecting associated label to the input selected changing to green
                                document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "green";                   
                            }
                        }
                        else {
                            document.getElementById("outcome").innerHTML = "Incorrect!";
                            document.getElementById("outcome").style.color = "red";
                            correct = "No";
                            numCurrentAttempts++;

                            // only modify the color of a label if it exists
                            if (obj[index]["isImage"][0] === false) {
                                // grabbing input for attribute that is checked by user
                                let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                                // selecting associated label to the input selected changing to green
                                document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "red";                   
                            }   
                        }

                        // stop timer
                        stopTimer(timerID);

                        /* UPDATE USER JSON FILE */
                        updateData(obj[index]["pkey"], numCurrentAttempts, correct, obj[index]["tags"], startDate);
                    }
                    // means user can attempt to answer, but result will not be saved if incorrect
                    else {
                        
                        let correct;
                        // grab the selected option
                        let selectedOption = document.querySelector('input[name="dynamic_option"]:checked').value;

                        // compare the selected option to the correct answer
                        if (selectedOption === correctAnswers[index]) {
                            document.getElementById("outcome").innerHTML = "Correct!"; 
                            document.getElementById("outcome").style.color = "green";            
                            correct = "Yes";
                            numCurrentAttempts++;

                            // only modify the color of a label if it exists
                            if (obj[index]["isImage"][0] === false) {
                                // grabbing input for attribute that is checked by user
                                let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                                // selecting associated label to the input selected changing to green
                                document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "green";                   
                            }
    
                            // stop timer
                            stopTimer(timerID);

                            /* UPDATE USER JSON FILE */
                            updateData(obj[index]["pkey"], numCurrentAttempts, correct, obj[index]["tags"], startDate);
                        }
                        else {
                            document.getElementById("outcome").innerHTML = "Incorrect, try again!";
                            document.getElementById("outcome").style.color = "red";
                            numCurrentAttempts++;

                            // only modify the color of a label if it exists
                            if (obj[index]["isImage"][0] === false) {
                                // grabbing input for attribute that is checked by user
                                let selector = document.querySelector('input[name="dynamic_option"]:checked').id;
                                // selecting associated label to the input selected changing to green
                                document.querySelector("label[for=" + CSS.escape(selector) + "]").style.color = "red";                   
                            }
                        }
                    }
                }
                // user has already answered this question
                else {
                    document.getElementById("outcome").innerHTML = "You have already answered this question!";
                    document.getElementById("outcome").style.color = "red";
                }
            }


            // sending data to update_user_json.php for modification of user json file
            let updateData = (pkey, numCurrentAttempts, correct, tags, startDate) => {
                request = new XMLHttpRequest();
                request.open('POST', 'js-php/update_user_json.php', true);
                request.onreadystatechange = respond;
                request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                request.send("pkey=" + pkey + "&numCurrentTries=" + numCurrentAttempts + "&correct=" + correct + "&tags=" + tags + "&startDate=" + startDate);
            }
            let respond = () => {
                if (request.readyState == 4 && request.status == 200) {
                    //console.log("PHP sent back: " + request.responseText);

                    // receive updated user json file and parse it to update the global obj
                    obj = JSON.parse(request.responseText);

                    // while on the same question that was just answered, display the answer portion
                    displayAfterSubmit();
                }
            }





            let total_clicked = false;
            let showTotalProgress = () => {
                if (total_clicked) {
                    document.getElementById("totalProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("totalProgressDiv").style.display = "none";
                    total_clicked = false;
                }
                else {
                    document.getElementById("totalProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("totalProgressDiv").style.display = "";
                    total_clicked = true;
                }
            }


            /* GET AND DISPLAY CHAPTER PROGRESS DATA */
            let chapter_clicked = false;
            let request_ch;
            let chapter_obj;
            let getChapterData = () => {
                // toggle chapterProgressButton innerHTML & display
                if (chapter_clicked){
                    document.getElementById("chapterProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("chapterProgressDiv").style.display = "none";
                    chapter_clicked = false;
                } 
                else {
                    document.getElementById("chapterProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("chapterProgressDiv").style.display = "";
                    chapter_clicked = true;
                    // start XMLHttpRequest
                    request_ch = new XMLHttpRequest();
                    request_ch.open('POST', 'get/get_ch_prog.php', true);
                    request_ch.onreadystatechange = getChapterDataResponse;
                    request_ch.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_ch.send();
                }
            }
            let getChapterDataResponse = () => {
                if (request_ch.readyState == 4 && request_ch.status == 200) {
                    //console.log("PHP sent back: " + request_ch.responseText);
                    chapter_obj = JSON.parse(request_ch.responseText);
                    showChapterProgress();
                }
            }
            let showChapterProgress = () => {
                // create a table with all the chapter data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Ch</th><th>Chapter Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th></tr>';
                for (const key in chapter_obj) {
                    const value = chapter_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += `<tr><td>${key}</td>`;
                    str += `<td>${value["Name"]}</td>`;
                    str += `<td>${value["TotalQuestions"]}</td>`;
                    str += `<td title="${value["NumberCorrect"]} / ${value["NumberComplete"]}"><progress value="${value["NumberCorrect"]}" max="${value["NumberComplete"]}"></progress><div>${firstPercent}%</div></td>`;
                    str += `<td title="${value["NumberComplete"]} / ${value["TotalQuestions"]}"><progress value="${value["NumberComplete"]}" max="${value["TotalQuestions"]}"></progress><div>${secondPercent}%</div></td>`;
                    str += `<td>${finalTime}</td></tr>`;
                }
                str += '</table>';
                document.getElementById("chapterProgressDiv").innerHTML = str;            
            }


            /* GET AND DISPLAY SECTION PROGRESS DATA */
            let section_clicked = false;
            let request_sec;
            let section_obj;
            let getSectionData = () =>{
                // toggle sectionProgressButton innerHTML & display
                if(section_clicked){
                    document.getElementById("sectionProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("sectionProgressDiv").style.display = "none";
                    section_clicked = false;
                } 
                else{
                    document.getElementById("sectionProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("sectionProgressDiv").style.display = "";
                    section_clicked = true;
                    // start XMLHttpRequest
                    let chapter = getChapterInfo();
                    request_sec = new XMLHttpRequest();
                    request_sec.open('POST', 'get/get_sec_prog.php', true);
                    request_sec.onreadystatechange = getSectionDataResponse;
                    request_sec.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_sec.send("chapter=" + chapter);
                }
            }
            let getSectionDataResponse = () =>{
                if (request_sec.readyState == 4 && request_sec.status == 200) {
                    //console.log("PHP sent back: " + request_sec.responseText);
                    section_obj = JSON.parse(request_sec.responseText);
                    showSectionProgress();
                }
            }
            let showSectionProgress = () =>{
                // create a table with all the section data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Sec</th><th>Section Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th></tr>';
                for(const key in section_obj){
                    const value = section_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += `<tr><td>${key}</td>`;
                    str += `<td>${value["Name"]}</td>`;
                    str += `<td>${value["TotalQuestions"]}</td>`;
                    str += `<td title="${value["NumberCorrect"]} / ${value["NumberComplete"]}"><progress value="${value["NumberCorrect"]}" max="${value["NumberComplete"]}"></progress><div>${firstPercent}%</div></td>`;
                    str += `<td title="${value["NumberComplete"]} / ${value["TotalQuestions"]}"><progress value="${value["NumberComplete"]}" max="${value["TotalQuestions"]}"></progress><div>${secondPercent}%</div></td>`;
                    str += `<td>${finalTime}</td></tr>`;
                }
                str += '</table>';
                document.getElementById("sectionProgressDiv").innerHTML = str;   
            }


            /* GET AND DISPLAY LO PROGRESS DATA */
            let lo_clicked = false;
            let request_lo;
            let learningoutcome_obj;
            let getLoData = () =>{
                // toggle loProgressButton innerHTML & display
                if(lo_clicked){
                    document.getElementById("loProgressHeaderArrow").innerHTML = "&#709;";
                    document.getElementById("loProgressDiv").style.display = "none";
                    lo_clicked = false;
                } 
                else{
                    document.getElementById("loProgressHeaderArrow").innerHTML = "&#708;";
                    document.getElementById("loProgressDiv").style.display = "";
                    lo_clicked = true;
                    // start XMLHttpRequest
                    let chapter = getChapterInfo();
                    let section = getSectionInfo();
                    request_lo = new XMLHttpRequest();
                    request_lo.open('POST', 'get/get_lo_prog.php', true);
                    request_lo.onreadystatechange = getLoDataResponse;
                    request_lo.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    request_lo.send("chapter=" + chapter + "&section=" + section);
                }
            }
            let getLoDataResponse = () =>{
                if (request_lo.readyState == 4 && request_lo.status == 200) {
                    //console.log("PHP sent back: " + request_lo.responseText);
                    learningoutcome_obj = JSON.parse(request_lo.responseText);
                    showLoProgress();
                }
            }
            let showLoProgress = () =>{
                // create a table with all the lo data to display on the client-side
                let str = '<table class="content_progress">';
                str += '<tr><th>Lo</th><th>Learning Outcome Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th></tr>';
                for(const key in learningoutcome_obj){
                    const value = learningoutcome_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += `<tr><td>${key}</td>`;
                    str += `<td>${value["Name"]}</td>`;
                    str += `<td>${value["TotalQuestions"]}</td>`;
                    str += `<td title="${value["NumberCorrect"]} / ${value["NumberComplete"]}"><progress value="${value["NumberCorrect"]}" max="${value["NumberComplete"]}"></progress><div>${firstPercent}%</div></td>`;
                    str += `<td title="${value["NumberComplete"]} / ${value["TotalQuestions"]}"><progress value="${value["NumberComplete"]}" max="${value["TotalQuestions"]}"></progress><div>${secondPercent}%</div></td>`;
                    str += `<td>${finalTime}</td></tr>`;
                }
                str += '</table>';
                document.getElementById("loProgressDiv").innerHTML = str;  
            }


            /* MOVEMENT */
            let next = () => {
                // making sure we are in legal index bound
                if (index !== totalQuestions - 1) {
                    // 1. clear previous question data
                    clearData();

                    // 2. clear timer
                    clearTimer(timerID);

                    // 3. update index to go forward
                    index++;

                    // 4. hide outcome element
                    document.getElementById("outcome").style.display = "none";

                    // 5. since we are in a new question, set current attempts back to 0
                    numCurrentAttempts = 0;

                    // 6. display new question data
                    displayData();
                }
            }
            let previous = () => {
                // making sure we are in legal index bound
                if(index !== 0){
                    // 1. clear previous question data
                    clearData();

                    // 2. clear timer
                    clearTimer(timerID);

                    // 3. update index to go back
                    index--;

                    // 4. hide outcome element
                    document.getElementById("outcome").style.display = "none";

                    // 5. since we are in a new question, set current attempts back to 0
                    numCurrentAttempts = 0;

                    // 6. display new question data
                    displayData();
                }
            }


            // functions to get info based on chapter, section, learningoutcome
            let getChapterInfo = () =>{
                // we will first access the chapter HTML element and then its text
                let select = document.getElementById("selected_lo").innerHTML;
                let firstPeriodIndex = select.indexOf(".");
                var chapterNumber = select.slice(0, firstPeriodIndex);
                console.log(chapterNumber);
                return chapterNumber;
                // chapterNumber = 1
            }
            let getSectionInfo = () =>{
                let select = document.getElementById("selected_lo").innerHTML;
                let pos1 = select.indexOf(".");
                let pos2 = select.indexOf(".", pos1 + 1);
                var sectionNumber = select.slice(0, pos2);
                return sectionNumber;
                // sectionNumber = 1.2
            }
            let getLearningOutcomeInfo = () =>{
                let select = document.getElementById("selected_lo").innerHTML;
                let pos1 = select.indexOf(".");
                let pos2 = select.indexOf(".", pos1 + 1);
                let pos3 = select.indexOf(".", pos2 + 1);
                var learningoutcomeNumber = select.slice(0, pos3);
                return learningoutcomeNumber;
                // learningoutcomeNumber = 1.2.3
            }


            // getting all chapters from openStax.json
            let ch_req;                        
            let getChapterOptions = () => {
                console.log("Getting all chapter options...");
                ch_req = new XMLHttpRequest();
                ch_req.open('POST', 'learning_map/get_chapters.php', true);
                ch_req.onreadystatechange = getChapterOptionsResponse;
                ch_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_req.send();
            }
            let getChapterOptionsResponse = () =>{
                if (ch_req.readyState == 4 && ch_req.status == 200) {
                    //console.log("PHP sent back: " + ch_req.responseText);
                    let ch_obj = JSON.parse(ch_req.responseText);

                    // display the received chapter options
                    let str = '<option id="ch_option_1" selected="selected" disabled>' + "<?= $chapter_selected; ?>" + '</option>';
                    let i = 2;
                    for (const [key, value] of Object.entries(ch_obj)) {
                        let temp = value.slice(1, value.length);
                        if (value[0] === 'A') {
                            str += `<option id="ch_option_${i}" value="${key}">${key}. ${temp}</option>`;
                        } 
                        else {
                            str += `<option id="ch_option_${i}" value="${key}" disabled>${key}. ${temp}</option>`;
                        }
                        i++;
                    }
                    document.getElementById("chapter_options").innerHTML = str;
                }
            }   

            // getting all sections from selected chapter from openStax.json
            let sec_req;      
            let getSectionOptions = (chapterDigit) => {
                console.log("Getting all section options...");
                sec_req = new XMLHttpRequest();
                sec_req.open('POST', 'learning_map/get_sections.php', true);
                sec_req.onreadystatechange = getSectionOptionsResponse;
                sec_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                if (chapterDigit === undefined) {
                    sec_req.send("chapter=" + readChapterDigit());
                }
                else {
                   sec_req.send("chapter=" + chapterDigit);
                }
            }
            let getSectionOptionsResponse = () =>{
                if (sec_req.readyState == 4 && sec_req.status == 200) {
                    //console.log("PHP sent back: " + sec_req.responseText);
                    let sec_obj = JSON.parse(sec_req.responseText);

                    // display the received section options
                    let str = '<option id="mainSectionOption" selected="selected" disabled>' + "<?= $section_selected; ?>" + '</option>';
                    let i = 2;
                    for(const [key, value] of Object.entries(sec_obj)){
                        let sec_num = key.slice(key.indexOf('.') + 1, key.length);
                        let temp = value.slice(1, value.length);
                        if(value[0] === 'A') {
                            str += `<option id="sec_option_${i}" value="${sec_num}">${key}. ${temp}</option>`;
                        } 
                        else {
                            str += `<option id="sec_option_${i}" value="${sec_num}" disabled>${key}. ${temp}</option>`;
                        }
                        i++;
                    }
                    document.getElementById("section_options").innerHTML = str;


                    if (chBool) {
                        chBool = false;
                        chapterHelper2();
                    }
                }
            }   

            // getting all los from selected section from openStax.json
            let lo_req;                
            let getLoOptions = (chapterDigit, sectionDigit) => {
                console.log("Getting all lo options...");
                lo_req = new XMLHttpRequest();
                lo_req.open('POST', 'learning_map/get_los.php', true);
                lo_req.onreadystatechange = getLoOptionsResponse;
                lo_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                if (chapterDigit === undefined && sectionDigit === undefined) {
                    lo_req.send("chapter=" + readChapterDigit() + "&section=" + readSectionDigit());
                }
                else {
                    lo_req.send("chapter=" + chapterDigit + "&section=" + sectionDigit);
                }
            }
            let getLoOptionsResponse = () =>{
                if (lo_req.readyState == 4 && lo_req.status == 200) {
                    //console.log("PHP sent back: " + lo_req.responseText);
                    let lo_obj = JSON.parse(lo_req.responseText);

                    // display the received lo options
                    let str = '<option id="mainLoOption" selected="selected" disabled>' + "<?= $learningoutcome_selected; ?>" + '</option>';
                    i = 2;
                    for(const [key, value] of Object.entries(lo_obj)){
                        let lo_num = key.slice(key.indexOf('.', key.indexOf('.') + 1) + 1, key.length);
                        let temp = value.slice(1, value.length);
                        if(value[0] === 'A') {
                            str += `<option id="lo_option_${i}" value="${lo_num}">${key}. ${temp}</option>`;
                        } 
                        else {
                            str += `<option id="lo_option_${i}" value="${lo_num}" disabled>${key}. ${temp}</option>`;
                        }
                        i++;
                    }
                    document.getElementById("learningoutcome_options").innerHTML = str;

                    if (secBool) {
                        secBool = false;
                        sectionHelper2();
                    }
                }
            }   


            let ch;
            let sec;
            let lo;
            let testFxn = (ele) => {
                console.log(ele);
                let text = ele.innerHTML;
                console.log(text);
                let pos1 = text.indexOf(".");
                let pos2 = text.indexOf(".", pos1 + 1);
                let pos3 = text.indexOf("-", pos2 + 1);
                // holding single digit of ch, sec, lo
                ch = text.slice(0, pos1);
                sec = text.slice(pos1 + 1, pos2);
                lo = text.slice(pos2 + 1, pos3);
                console.log(ch);
                console.log(sec);
                console.log(lo);

                // now create XMLHttpRequest to get the names of ch, sec, lo that 
                // was selected
                grabNames();

            }

            let req0;
            let names;
            let grabNames = () => {
                req0 = new XMLHttpRequest();
                req0.open('POST', 'js-php/grab_names.php', true);
                req0.onreadystatechange = grabNamesResponse;
                req0.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req0.send("ch=" + ch + "&sec=" + sec + "&lo=" + lo);
            }
            let grabNamesResponse = () => {
                if (req0.readyState == 4 && req0.status == 200) {
                    console.log("PHP sent back: " + req0.responseText);
                    names = JSON.parse(req0.responseText);

                    // now create XMLHttpRequest to inspect student's openStax file to see
                    // if clicked on lo is open or closed
                    checkAccess();
                }
            }

            let req1;        
            let checkAccess = () => {
                req1 = new XMLHttpRequest();
                req1.open('POST', 'js-php/check_access.php', true);
                req1.onreadystatechange = checkAccessResponse;
                req1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req1.send("ch=" + ch + "&sec=" + sec + "&lo=" + lo);
            }
            let checkAccessResponse = () =>{
                if (req1.readyState == 4 && req1.status == 200) {
                    console.log("PHP sent back: " + req1.responseText);
                    let result = JSON.parse(req1.responseText);
 
                    // if selected lo has open access
                    if (result) {
                        // fill out form again
                        document.getElementById("search_tags").value = ch + "." + sec + "." + lo;
                        document.getElementById("chapter_selected").value = names[0];
                        document.getElementById("section_selected").value = names[1];
                        document.getElementById("learningoutcome_selected").value = names[2];
                        document.getElementById("chapter_info_form").value = ch;
                        document.getElementById("section_info_form").value = ch + "." + sec;
                        document.getElementById("learningoutcome_info_form").value = ch + "." + sec + "." + lo;
                        document.getElementById("main_form").submit();
                    }

                }
            }   





            /* OR 2 STEM STARTS HERE */
            var ChaptersList;         // it contains the openStax book with the definition of all the chapters, sections, and learning outcomes
            var StaticQuestionsID;    // it contains the list of index of questions, for each learning outcome
            var StaticQuestionsInfo;  // it contains the list of questions with all the information
            var kMap;                 // the knowledge map, it contains the relationships between the different learning outcome

            // Remarks: not sure if the kMap will change for each student so each student/user will have an instance of each element

            var getdatarequest;       // to load data from the server side
            var data;

    

            ////////////////////////////////////////////////////////////////////////////////////////////
            // Load the necessary files
            ///////////////////////////////////////////////////////////////////////////////////////////

            function GetData() 
            {
            // hiding elements
            HideChapterInformation();
            HideLearningOutcomeInformationInformation();
            HideChSecLoInformation();

            // get data
            console.log('Get data from the server side for the initialization');
            getdatarequest = new XMLHttpRequest();
            if (!getdatarequest) {
                alert('Cannot create an XMLHTTP instance');
                return false;
            }
            getdatarequest.onreadystatechange = GetDataCB;
            getdatarequest.open('POST','js-php/GetData2.php');
            getdatarequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            getdatarequest.send(); 
            
            }
            function GetDataCB() 
            {
            if (getdatarequest.readyState === XMLHttpRequest.DONE) {
                if (getdatarequest.status === 200) {	
                    // alert('Load Data');
                    console.log('Get answer');
                    data=getdatarequest.responseText;
                    data=JSON.parse(data);
                    ChaptersList=JSON.parse(data.ChapterList);
                    StaticQuestionsID=JSON.parse(data.StaticQuestionsID);
                    StaticQuestionsInfo=JSON.parse(data.StaticQuestionsInfo);
                    kMap=JSON.parse(data.kMap);

                    console.log('Init book');
                    // Update the info in the book based on the list of questions
                    InitBookQuestion();
                    // Put the information in the local storage
                    SaveProgress();
                } 
                else 
                    alert('There was a problem with the request.');
            }
            }
            
            
            ///////////////////////////////////////////////////////////////////////////////////////////////////////

            function ToggleChapterInformation() {
            var x = document.getElementById("myChapter");
            if (x.style.display === "none") 
            {
                x.style.display = "block";
                document.getElementById("chapterInfoHeaderArrow").innerHTML = "&#708;";

                // grab chapter number currently user is on and then display the following data
                let chapter = getChapterInfo();
                str_chapter='<p><strong>' + ChaptersList[chapter-1].Introduction.Name + '</strong></p>';
                str_chapter+='<p>' + ChaptersList[chapter-1].Introduction.Description + '</p>';
                str_chapter+='<ul><li style="display:inline; margin-right: 25px;"><a href="../assets/documents/' + ChaptersList[chapter-1].Introduction.Document + '" target="_blank">Introduction</a></li>';
                str_chapter+='<li style="display:inline; margin-left: 25px;"><a href="../assets/documents/' + ChaptersList[chapter-1].Review.Document + '" target="_blank">Review</a></li></ul>';
                document.getElementById('myChapter').innerHTML=str_chapter;
            } 
            else 
            {
                x.style.display = "none";
                document.getElementById("chapterInfoHeaderArrow").innerHTML = "&#709;";
            }
            }
            function ShowChapterInformation() {
            var x = document.getElementById("myChapter");
            x.style.display = "block";
            document.getElementById("chapterInfoHeaderArrow").innerHTML = "&#708;";
            }
            function HideChapterInformation() {
            var x = document.getElementById("myChapter");
            x.style.display = "none";
            document.getElementById("chapterInfoHeaderArrow").innerHTML = "&#709;";
            }
            

            function ToggleLearningOutcomeInformation() {
            var x = document.getElementById("myLO");
            if (x.style.display === "none") 
            {
                x.style.display = "block";
                document.getElementById("loInfoHeaderArrow").innerHTML = "&#708;";

                // grab numbers
                let chapter = getChapterInfo();

                let tempSection = getSectionInfo();
                let pos1 = tempSection.indexOf(".");
                let section = tempSection.slice(pos1 + 1, tempSection.length);

                let tempLO = getLearningOutcomeInfo();
                pos1 = tempLO.indexOf(".");
                let pos2 = tempLO.indexOf(".", pos1 + 1);
                let learningObjective = tempLO.slice(pos2 + 1, tempLO.length);

                document.getElementById("lo_tag").innerHTML = `<strong>Learning Outcome Tag:</strong> ${tempLO} <br> <strong>Number of questions for this learning outcome:</strong> ${obj.length}`;
                
                document.getElementById('lo_link').setAttribute('href',ChaptersList[chapter-1]['Sections'][section-1]['LearningOutcomes'][learningObjective-1]['url']);
                videolink=ChaptersList[chapter-1]['Sections'][section-1]['LearningOutcomes'][learningObjective-1]['Video'];
                if (videolink)
                document.getElementById('lo_video').innerHTML='<a href="' + videolink + '" target="_blank">Video</a>' ;

                doc=ChaptersList[chapter-1]['Sections'][section-1]['LearningOutcomes'][learningObjective-1]['Document'];
                if (doc)
                document.getElementById('lo_document').innerHTML='<a href="../assets/documents/' + doc + '" target="_blank">Document</a>' ;	
                
                document.getElementById('lo_info_next').innerHTML=GetNextRecommendedLO(tempLO);
                document.getElementById('lo_info_previous').innerHTML=GetPreviousRecommendedLO(tempLO);
                
            } 
            else 
            {
                x.style.display = "none";
                document.getElementById("loInfoHeaderArrow").innerHTML = "&#709;";
            }
            }
            function ShowLearningOutcomeInformationInformation() {
            var x = document.getElementById("myLO");
            x.style.display = "block";
            document.getElementById("loInfoHeaderArrow").innerHTML = "&#708;";
            }
            function HideLearningOutcomeInformationInformation() {
            var x = document.getElementById("myLO");
            x.style.display = "none";
            document.getElementById("loInfoHeaderArrow").innerHTML = "&#709;";
            }
            function HideChSecLoInformation(){
                let x = document.getElementById("totalProgressDiv");
                x.style.display = "none";
                x = document.getElementById("chapterProgressDiv");
                x.style.display = "none";
                x = document.getElementById("sectionProgressDiv");
                x.style.display = "none";
                x = document.getElementById("loProgressDiv");
                x.style.display = "none";
            }

            ///////////////////////////////////////////////////////////////////////////////////////////////////////

            // Local Storage functions

            function IsInsideLocalStorage()
            {
            let book = localStorage.getItem('book');
            if(book)
                return true;
            else
                return false;
            }

            function SetStateLocalStorage(chapter,section,lo)
            {
            localStorage.setItem('chapter', chapter);
            localStorage.setItem('section', section);
            localStorage.setItem('learningoutcome', lo);
            }

            function GetStateLocalStorage()
            {
            chapter = localStorage.getItem('chapter');
            section = localStorage.getItem('section');
            learningoutcome=localStorage.getItem('learningoutcome');
            return [ chapter , section , learningoutcome];
            }

            // Save the main data structures in the local storage
            function SaveProgress()
            {
            console.log('Save progress...');
            localStorage.setItem('book', JSON.stringify(ChaptersList));
            localStorage.setItem('questions_info',JSON.stringify(StaticQuestionsInfo));
            localStorage.setItem('questions_id',JSON.stringify(StaticQuestionsID));
            localStorage.setItem('kmap',JSON.stringify(kMap));
            }

            // Load the main data structures from the local storage
            function LoadProgress()
            {
            console.log('Load progress...');
            ChaptersList=JSON.parse(localStorage.getItem('book'));
            StaticQuestionsInfo=JSON.parse(localStorage.getItem('questions_info'));
            StaticQuestionsID=JSON.parse(localStorage.getItem('questions_id'));
            kMap=JSON.parse(localStorage.getItem('kmap'));
            }

            ////////////////////////////////////////////////////////////////////////////////////////////////////////


            ///////////////////////////////////////////////////////////////////////////////////////////////////////

            // Init the book based on the parts related to the questions

            // Set the number of questions for each LO
            function InitBookQuestion()
            {
            console.log('InitBookQuestion');
            nquestions=StaticQuestionsInfo.length;
            for (i=0;i<nquestions;i++)
            {
                tags=StaticQuestionsInfo[i].tags;
                // console.log('tags: ' + tags + ' ' + typeof(tags));
                if (typeof tags === 'string')
                {
                vtags=tags.split(',');
                // console.log('tags: ' + tags);
                for (j=0;j<vtags.length;j++)
                {
                    vtaginfo=vtags[j].split('.');
                    chapterid=parseInt(vtaginfo[0]);
                    sectionid=parseInt(vtaginfo[1]);
                    loid=parseInt(vtaginfo[2]);
                    // console.log(tags + '... Update question ' + i + ' with ' + vtaginfo);
                    
                    nchapters=ChaptersList.length;
                    if (chapterid<=nchapters)
                    {
                    nsections=ChaptersList[chapterid-1]['Sections'].length;
                    if (sectionid<=nsections)
                    {
                        nlearningoutcomes=ChaptersList[chapterid-1]['Sections'][sectionid-1]['LearningOutcomes'].length;
                        if (loid<=nlearningoutcomes)
                        {
                        // Chapter only
                        ChaptersList[chapterid-1].score[0]=ChaptersList[chapterid-1].score[0]+1;
                        // Section only
                        ChaptersList[chapterid-1]['Sections'][sectionid-1].score[0]=
                        ChaptersList[chapterid-1]['Sections'][sectionid-1].score[0]+1;
                        // Learning outcome only
                        ChaptersList[chapterid-1]['Sections'][sectionid-1]['LearningOutcomes'][loid-1].score[0]=
                        ChaptersList[chapterid-1]['Sections'][sectionid-1]['LearningOutcomes'][loid-1].score[0]+1;
                        }
                    }
                    }
                }
                }
            }
            }


            function GetTagInformation(tag)
            {
                vtags=tag.split('.');
                chapterid=vtags[0];
                sectionid=vtags[1];
                loid=vtags[2];
                console.log('tag:' + chapterid + '.' + sectionid + '.' + loid);
                chaptername='';
                sectionname='';
                loname='';
                for (var x in ChaptersList) 
                {
                    if (chapterid==ChaptersList[x]['Index'])
                    {
                        chaptername=ChaptersList[x]['Name'];
                        for (var y in ChaptersList[x]['Sections']) 
                        {	
                            if (sectionid==ChaptersList[x]['Sections'][y]['Index'])
                            {
                                sectionname=ChaptersList[x]['Sections'][y]['Name'];
                                for (var z in ChaptersList[x]['Sections'][y]['LearningOutcomes']) 
                                {
                                    if (loid==ChaptersList[x]['Sections'][y]['LearningOutcomes'][z]['Index'])
                                    {
                                        loname=ChaptersList[x]['Sections'][y]['LearningOutcomes'][z]['Name'];
                                        console.log(chaptername + ' ' + sectionname + ' ' + loname);
                                        return [ chaptername, sectionname, loname ];
                                    }
                                }
                            }
                        }			
                    }
                }
                console.log(tag + ' not found');
                return [ chaptername, sectionname, loname ];
            }



            function GetTag(){
                // assigning values to hidden form variables to send to php
                // 1
                let select = document.getElementById("learningoutcome_options");
                select = select.options[select.selectedIndex].text;
                let pos1 = select.indexOf(".");
                let pos2 = select.indexOf(".", pos1 + 1);
                let pos3 = select.indexOf(".", pos2 + 1);
                var learningoutcomeNumber = select.slice(0, pos3);
                document.getElementById("search_tags").value = learningoutcomeNumber;

                // 2
                select = document.getElementById("chapter_options");
                document.getElementById("chapter_selected").value = select.options[select.selectedIndex].text;

                // 3
                select = document.getElementById("section_options");
                document.getElementById("section_selected").value = select.options[select.selectedIndex].text;

                // 4
                select = document.getElementById("learningoutcome_options");
                document.getElementById("learningoutcome_selected").value = select.options[select.selectedIndex].text;

                // 5
                select = document.getElementById("chapter_options");
                select = select.options[select.selectedIndex].text;
                let firstPeriodIndex = select.indexOf(".");
                var chapterNumber = select.slice(0, firstPeriodIndex);
                document.getElementById("chapter_info_form").value = chapterNumber;

                // 6
                select = document.getElementById("section_options");
                select = select.options[select.selectedIndex].text;
                pos1 = select.indexOf(".");
                pos2 = select.indexOf(".", pos1 + 1);
                var sectionNumber = select.slice(0, pos2);
                document.getElementById("section_info_form").value = sectionNumber;

                // 7
                document.getElementById("learningoutcome_info_form").value = learningoutcomeNumber;
            }



            // controlling the user profile dropdown
            /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
            let showDropdown = () => {
                document.getElementById("myDropdown").classList.toggle("show");
            }
            // Close the dropdown if the user clicks outside of it
            window.onclick = function(event) {
                if (!event.target.matches('.dropbtn')) {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    var i;
                    for (i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }


            // DRIVER ON PAGE LOAD
            hideDisplay();
            getChapterOptions();

            // checking if form was submitted that way we can parse the response
            if("<?= $complete; ?>" === "false"){
                // notify user
                alert("Learning Outcome not found in the filebase.");
                // refresh page
                window.location.href = "student_browse.php";
            }
            else if ("<?= $complete; ?>" === "true") {

                // get the chapter digit
                let chapterDigit = readChapterDigit();
                //console.log(`Chapter digit: ${chapterDigit}`);
                getSectionOptions(chapterDigit);

                // get the section digit
                let sectionDigit = readSectionDigit();
                //console.log(`Section digit: ${sectionDigit}`);
                getLoOptions(chapterDigit, sectionDigit);

                showDisplay();
                parseData();
            }
        </script>
    </body>
</html>
