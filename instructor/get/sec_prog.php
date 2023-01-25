<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

// for display purposes
//header('Content-type: text/plain');

// receiving input
$user = $_POST["user"];

// globals
// PHP hashmap: key represent chapter.section number, value is an array[sec name, sec question count, sec correct, sec complete, sec time spent]
$sections_data = [];
// chapter user is currently on will be used to pull up section data from json file
$chapter = (int)$_POST["chapter"];


// read and decode the user JSON file (text => PHP assoc array)
$json_filename = "../../user_data/{$_SESSION['selected_course_name']}-{$_SESSION['selected_course_id']}/questions/" . $user . ".json";
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
$json_filename = "../../user_data/{$_SESSION['selected_course_name']}-{$_SESSION['selected_course_id']}/openStax/" . $user . ".json";
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