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

// globals
$sections_data = [];
// PHP hashmap: key represent chapter.section number, value is an array[sec name, sec question count, sec correct, sec complete, sec time spent]
$chapter = (int)$_POST["chapter"];


// read and decode the user JSON file (text => PHP assoc array)
$json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_questions = json_decode($json, true);

// loop through the PHP assoc array & add every chapter.section number into $sections_data (no duplicate keys)
foreach($json_questions as $question){
    // grabbing section number out of tag
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    $section = substr($question["tags"], 0, $pos);

    if(!isset($sections_data[$section]) && ((int)strtok($question["tags"], ".") === $chapter)){
        $sections_data[$section] = [];
    }

    // making sure ch.sec.lo number index does not already exist in the array
    if((isset($sections_data[$section])) && (!isset($sections_data[$section][$question["tags"]]))){
        $sections_data[$section][$question["tags"]] = [];
        $sections_data[$section][$question["tags"]]["NumberQuestions"] = 1;
    }
    else if(isset($sections_data[$section]) && isset($sections_data[$section][$question["tags"]])){
        $sections_data[$section][$question["tags"]]["NumberQuestions"]++;
    }
}


/* SORTING $sections_data */
ksort($sections_data);
$sections_sorted = [];
foreach($json_questions as $question){
    // extracting only the ch.sec number out of the tag (1.2.3 => 1.2)
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    $section = substr($question["tags"], 0, $pos);
    // in order to sort the elements of the sec must already exist in $sections_data and
    // the ch.sec must not have been sorted already
    if(isset($sections_data[$section]) && !in_array($section, $sections_sorted)){
        array_push($sections_sorted, $section);
        ksort($sections_data[$section]);
    }
}
/* SORTING $sections_data */


// read and decode the openStax JSON file (text => PHP assoc array)
$json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_openStax = json_decode($json, true);

foreach($json_openStax as $openStaxCh){

    if((int)$openStaxCh["Index"] === $chapter){

        for($i = 0; $i < count($openStaxCh["Sections"]); $i++){

            $chapter_section = (string)$openStaxCh["Index"] . "." . (string)$openStaxCh["Sections"][$i]["Index"];

            if(isset($sections_data[$chapter_section])){

                // pushing in section name
                $sections_data [$chapter_section] ["Name"] = $openStaxCh["Sections"][$i]["Name"];
                // pushing in starting section question count
                $sections_data [$chapter_section] ["TotalQuestions"] = 0;
                // pushing in starting section num correct count
                $sections_data [$chapter_section] ["NumberCorrect"] = 0;
                // pushing in starting section num complete count
                $sections_data [$chapter_section] ["NumberComplete"] = 0;
                // pushing in starting section time spent count
                $sections_data [$chapter_section] ["TimeSpent"] = 0;

                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($openStaxCh["Sections"][$i]["LearningOutcomes"]); $j++){

                    $chapter_section_lo = (string)$openStaxCh["Index"] . "." . (string)$openStaxCh["Sections"][$i]["Index"] . "." . (string)$openStaxCh["Sections"][$i]["LearningOutcomes"][$j]["Index"];

                    if(isset($sections_data [$chapter_section] [$chapter_section_lo])){
                        $sections_data [$chapter_section] [$chapter_section_lo] ["MaxNumberAccessment"] = $openStaxCh["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"];
                    }
                }

            }
        }
        // only need to loop through section
        break;
    }
}



// loop through php hashmap
foreach($sections_data as $key => $value){

    // loop through the questions
    foreach($json_questions as $question){

        $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
        $section = substr($question["tags"], 0, $pos);


        // if chapter.section match
        if($section == $key){
            // if question is correct, increase ch correct count
            if($question["correct"] === "Yes"){
                $sections_data[$key]["NumberCorrect"]++;
            }
            // if question has been answered
            if($question["datetime_started"] !== ""){
                // increase ch question answered count
                $sections_data[$key]["NumberComplete"]++;

                // convert timestamp strings to time
                $start_time = strtotime($question["datetime_started"]);
                $end_time = strtotime($question["datetime_answered"]);
                $diff_seconds = abs($end_time - $start_time);
                // increase ch time spent count
                $sections_data[$key]["TimeSpent"] += $diff_seconds;
            }
        }
    }
}


// loop through $chapters_data
$sec_keys = array_keys($sections_data);
for($i = 0; $i < count($sections_data); $i++){

    //echo $ch_keys[$i], "\n";

    $lo_keys = array_keys($sections_data[$sec_keys[$i]]);
    for($j = 0; $j < count($sections_data[$sec_keys[$i]]); $j++){

        //echo $sec_keys[$j], "\n";

        // because we have section keys like 'Name', 'TotalQuestions', ... 'TimeSpent'
        if(gettype($sections_data[$sec_keys[$i]][$lo_keys[$j]]) === "array"){

            if($sections_data [$sec_keys[$i]] [$lo_keys[$j]] ["MaxNumberAccessment"] > $sections_data [$sec_keys[$i]] [$lo_keys[$j]] ["NumberQuestions"]){
                $sections_data [$sec_keys[$i]] ["TotalQuestions"] += $sections_data [$sec_keys[$i]] [$lo_keys[$j]] ["NumberQuestions"];
            }
            else{
                $sections_data [$sec_keys[$i]] ["TotalQuestions"] += $sections_data [$sec_keys[$i]] [$lo_keys[$j]] ["MaxNumberAccessment"];
            }

        }
    }
}


// display
//print_r($sections_data);

// send section data back now
$json_encoded_section_data = json_encode($sections_data);
echo $json_encoded_section_data;

?>
