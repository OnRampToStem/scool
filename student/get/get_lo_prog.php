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
$los_data = [];
// PHP hashmap: key represent chapter.section.lo number, value is an array[lo name, lo question count, lo correct, lo complete, lo time spent]
$chapter = (int)$_POST["chapter"];
//$chapter = 1;
$section = $_POST["section"];
//$section = "1.2";
$pos = strpos($section, ".");
$section_number = (int)substr($section, $pos + 1, strlen($section));


// read and decode the user JSON file (text => PHP assoc array)
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_questions = json_decode($json, true);

// loop through the PHP assoc array & add every chapter.section.lo number into $los_data (no duplicate keys)
foreach($json_questions as $question){

    // get the current question's section number
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    $section_num = substr($question["tags"], 0, $pos);

    if(!isset($los_data[$question["tags"]]) && ($section_num == $section)){
        $los_data[$question["tags"]] = [];
    }

    // making sure ch.sec.lo number index does not already exist in the array
    if(!isset($los_data [$question["tags"]] ["NumberQuestions"]) && ($section_num == $section)){
        $los_data[$question["tags"]]["NumberQuestions"] = 1;
    }
    else if(isset($los_data [$question["tags"]] ["NumberQuestions"]) && ($section_num == $section)){
        $los_data[$question["tags"]]["NumberQuestions"]++;
    }
}

// sort keys in ascending order
ksort($los_data);


// read and decode the openStax JSON file (text => PHP assoc array)
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_openStax = json_decode($json, true);


foreach($json_openStax as $openStaxCh){

    if((int)$openStaxCh["Index"] === $chapter){

        for($i = 0; $i < count($openStaxCh["Sections"]); $i++){

            if($openStaxCh["Sections"][$i]["Index"] === $section_number){

                for($j = 0; $j < count($openStaxCh["Sections"][$i]["LearningOutcomes"]); $j++){

                    $ch_sec_lo = (string)$chapter . "." . (string)$section_number . "." . (string)$openStaxCh["Sections"][$i]["LearningOutcomes"][$j]["Index"];

                    if(isset($los_data[$ch_sec_lo])){

                        //
                        $los_data [$ch_sec_lo] ["MaxNumberAccessment"] = $openStaxCh["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"];
                        // pushing in section name
                        $los_data [$ch_sec_lo] ["Name"] = $openStaxCh["Sections"][$i]["LearningOutcomes"][$j]["Name"];
                        // pushing in starting section question count
                        $los_data [$ch_sec_lo] ["TotalQuestions"] = 0;
                        // pushing in starting section num correct count
                        $los_data [$ch_sec_lo] ["NumberCorrect"] = 0;
                        // pushing in starting section num complete count
                        $los_data [$ch_sec_lo] ["NumberComplete"] = 0;
                        // pushing in starting section time spent count
                        $los_data [$ch_sec_lo] ["TimeSpent"] = 0;
        
                    }
                }
            }
        }
    }
}


// loop through php hashmap
foreach($los_data as $key => $value){

    // loop through the questions
    foreach($json_questions as $question){

        // if chapter.section.lo match
        if($question["tags"] == $key){

            // if question is correct, increase ch correct count
            if($question["correct"] === "Yes"){
                $los_data[$key]["NumberCorrect"]++;
            }
            // if question has been answered
            if($question["datetime_started"] !== ""){
                // increase ch question answered count
                $los_data[$key]["NumberComplete"]++;

                // convert timestamp strings to time
                $start_time = strtotime($question["datetime_started"]);
                $end_time = strtotime($question["datetime_answered"]);
                $diff_seconds = abs($end_time - $start_time);
                // increase ch time spent count
                $los_data[$key]["TimeSpent"] += $diff_seconds;
            }
        }
    }
}


// loop through $los_data
$lo_keys = array_keys($los_data);
for($i = 0; $i < count($los_data); $i++){

    //echo $lo_keys[$i], "\n";

    if($los_data [$lo_keys[$i]] ["MaxNumberAccessment"] > $los_data [$lo_keys[$i]] ["NumberQuestions"]){
        $los_data [$lo_keys[$i]] ["TotalQuestions"] += $los_data [$lo_keys[$i]] ["NumberQuestions"];
    }
    else{
        $los_data [$lo_keys[$i]] ["TotalQuestions"] += $los_data [$lo_keys[$i]] ["MaxNumberAccessment"];
    }

}

// display
//print_r($los_data);

// send lo data back now
$json_encoded_lo_data = json_encode($los_data);
echo $json_encoded_lo_data;

?>