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

/* GLOBALS */
$chapters_data = []; // key represents chapter number, value is an array[ch name, ch question count, ch correct, ch complete, ch time spent]

// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
// read the file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_questions = json_decode($json, true);

// loop through the PHP assoc array & add every chapter number into $chapters_data (no duplicate keys)
foreach($json_questions as $question){
    // extracting only the chapter number out of the tag (1.2.3 => 1)
    $chapter = (int)strtok($question["tags"], ".");
    // making sure chapter number index does not already exist in the array
    if(!isset($chapters_data[$chapter])){
        // if does not exist, create inner array at that chapter index
        $chapters_data[$chapter] = [];
    }

    // extracting only the ch.sec number out of the tag (1.2.3 => 1.2)
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    $section = substr($question["tags"], 0, $pos);
    // making sure ch.sec number index does not already exist in the array
    if(!isset($chapters_data[$chapter][$section])){
        $chapters_data[$chapter][$section] = [];
    }

    // making sure ch.sec.lo number index does not already exist in the array
    if(!isset($chapters_data[$chapter][$section][$question["tags"]])){
        $chapters_data[$chapter][$section][$question["tags"]] = [];
        $chapters_data[$chapter][$section][$question["tags"]]["NumberQuestions"] = 1;
    }
    else{
        $chapters_data[$chapter][$section][$question["tags"]] ["NumberQuestions"]++;
    }

}

// sort chapters in ascending order
ksort($chapters_data); 
// will contain ch index values that have already been sorted
$chapters_sorted = [];
// will contain ch.sec index values that have already been sorted
$sections_sorted = [];
// loop through the PHP assoc array
foreach($json_questions as $question){
    // extracting only the chapter number out of the tag (1.2.3 => 1)
    $chapter = (int)strtok($question["tags"], ".");
    // in order to sort the elements of the chapter, chapter must already exist in $chapters_data and 
    // the chapter must not have been sorted already
    if(isset($chapters_data[$chapter]) && !in_array($chapter, $chapters_sorted)){
        array_push($chapters_sorted, $chapter);
        ksort($chapters_data[$chapter]);
    }

    // extracting only the ch.sec number out of the tag (1.2.3 => 1.2)
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    $section = substr($question["tags"], 0, $pos);
    // in order to sort the elements of the ch.sec, ch.sec must already exist in $chapters_data and
    // the ch.sec must not have been sorted already
    if(isset($chapters_data[$chapter][$section]) && !in_array($section, $sections_sorted)){
        array_push($sections_sorted, $section);
        ksort($chapters_data[$chapter][$section]);
    }
}



// reading through personalized openStax json file
// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
// read the file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

foreach($json_openStax as $chapter){
    // making sure to only insert chapters that exist in the user json file
    // openStax may have 15 chapters, but if the json file only contains chapters 1-12, only those will be added
    if(isset($chapters_data[$chapter["Index"]])){
        // pushing in chapter name
        $chapters_data [$chapter["Index"]] ["Name"] = $chapter["Name"];
        // pushing in starting chapter question count
        $chapters_data [$chapter["Index"]] ["TotalQuestions"] = 0;
        // pushing in starting chapter num correct count
        $chapters_data [$chapter["Index"]] ["NumberCorrect"] = 0;
        // pushing in starting chapter num complete count
        $chapters_data [$chapter["Index"]] ["NumberComplete"] = 0;
        // pushing in starting chapter time spent count
        $chapters_data [$chapter["Index"]] ["TimeSpent"] = 0;

        // loop through inner Sections array
        for($i = 0; $i < count($chapter["Sections"]); $i++){

            if(isset($chapters_data [$chapter["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]])){

                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    
                    if(isset($chapters_data [$chapter["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]])){
                        //array_push($chapters_data [$chapter["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]], $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"]);
                        $chapters_data [$chapter["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]] [$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"] . "." . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"]] ["MaxNumberAccessment"] = $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"];
                    }
                }
            }
        }
    }
}



// loop through $chapters_data
foreach($chapters_data as $key => $value){

    // loop through the questions
    foreach($json_questions as $question){

        // if chapter match
        if((int)strtok($question["tags"], ".") === $key){

            // if question is correct, increase ch correct count
            if($question["correct"] === "Yes"){
                $chapters_data[$key]["NumberCorrect"]++;
            }

            // if question has been answered
            if($question["datetime_started"] !== ""){
                // increase ch question answered count
                $chapters_data[$key]["NumberComplete"]++;
                // convert timestamp strings to time
                $start_time = strtotime($question["datetime_started"]);
                $end_time = strtotime($question["datetime_answered"]);
                $diff_seconds = abs($end_time - $start_time);
                // increase ch time spent count
                $chapters_data[$key]["TimeSpent"] += $diff_seconds;
            }
        }
    }
}



// loop through $chapters_data
$ch_keys = array_keys($chapters_data);
for($i = 0; $i < count($chapters_data); $i++){

    //echo $ch_keys[$i], "\n";

    $sec_keys = array_keys($chapters_data[$ch_keys[$i]]);
    for($j = 0; $j < count($chapters_data[$ch_keys[$i]]); $j++){

        //echo $sec_keys[$j], "\n";

        // because we have section keys like 'Name', 'TotalQuestions', ... 'TimeSpent'
        if(gettype($chapters_data[$ch_keys[$i]][$sec_keys[$j]]) === "array"){
            $lo_keys = array_keys($chapters_data[$ch_keys[$i]][$sec_keys[$j]]);
            for($k = 0; $k < count($chapters_data[$ch_keys[$i]][$sec_keys[$j]]); $k++){

                //echo $lo_keys[$k], "\n";

                if($chapters_data [$ch_keys[$i]] [$sec_keys[$j]] [$lo_keys[$k]] ["MaxNumberAccessment"] > $chapters_data [$ch_keys[$i]] [$sec_keys[$j]] [$lo_keys[$k]] ["NumberQuestions"]){
                    $chapters_data [$ch_keys[$i]] ["TotalQuestions"] += $chapters_data [$ch_keys[$i]] [$sec_keys[$j]] [$lo_keys[$k]] ["NumberQuestions"];
                }
                else{
                    $chapters_data [$ch_keys[$i]] ["TotalQuestions"] += $chapters_data [$ch_keys[$i]] [$sec_keys[$j]] [$lo_keys[$k]] ["MaxNumberAccessment"];
                }

            }
        }
    }
}


// display
//print_r($chapters_data);

// send chapter data back now

$json_encoded_chapter_data = json_encode($chapters_data);
echo $json_encoded_chapter_data;

?>