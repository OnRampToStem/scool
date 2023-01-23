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
    header("location: ../../register_login/logout.php");
    exit;
}

// for display purposes
//header("Content-type: text/plain");

// receiving $_POST inputs
$ch_index = (int)$_POST["ch_num"]; // holds single chapter digit
$sec_index = (int)$_POST["sec_num"]; // holds single section digit
$lo_index = (int)$_POST["lo_num"]; // holds single lo digit
$arr1 = []; // $arr1 will be an assoc array holding: "lo" => number of questions in that specific lo
$arr2 = []; // $arr2 will be an assoc array holding: "sec" => number of questions in that specific sec
$arr3 = []; // $arr3 will be an assoc array holding: "ch" => number of questions in that specific ch


// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$openStax = json_decode($json, true);

// loop through openStax to insert all ch.sec.lo (learning outcomes) as key of $arr1, init with value 0
foreach($openStax as $chapter){

    $arr3[strval($chapter["Index"])] = 0;

    foreach($chapter["Sections"] as $section){

        $arr2[strval($chapter["Index"]) . "." . strval($section["Index"])] = 0;

        foreach($section["LearningOutcomes"] as $lo){

            $arr1[strval($chapter["Index"]) . "." . strval($section["Index"]) . "." . strval($lo["Index"])] = 0;

        }
    }
}


// filepath
$json_filename = "../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/questions/" . $_SESSION['email'] . ".json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$questions = json_decode($json, true);

// loop through the all static questions to sum number of each lo in $arr1
foreach($questions as $question){
    // summing number of los
    if(isset($arr1[$question["tags"]])){
        $arr1[$question["tags"]]++;
    }
    else{
        echo "Unknown lo in the static questions json file\n\n";
    }

    // summing number of sections
    $pos = strpos($question["tags"], ".", strpos($question["tags"], ".") + strlen("."));
    if(isset($arr2[substr($question["tags"], 0, $pos)])){
        $arr2[substr($question["tags"], 0, $pos)]++;
    }
    else{
        echo "Unknown sec in the static questions json file\n\n";
    }

    // summing number of chapters
    if(isset($arr3[strtok($question["tags"], ".")])){
        $arr3[strtok($question["tags"], ".")]++;
    }   
    else{
        echo "Unknown ch in the static questions json file\n\n";
    }
}


// 1 
// LOOK TO OPEN NEXT POSSIBLE LEARNING OUTCOME IN THE CHAPTER.SECTION
// THAT THE STUDENT CURRENTLY RESIDES IN
// if $b remains as false then step 2 will run, if it is switches to true then no 
// more steps will run
$b = false;
// loop through each chapter
foreach($openStax as $key1 => $val1){

    // only looking for specific chapter
    if($val1["Index"] === $ch_index){

        // loop through each section in that chapter
        foreach($val1["Sections"] as $key2 => $val2){

            // only looking for specific section
            if($val2["Index"] === $sec_index){

                // loop through each lo in that section
                foreach($val2["LearningOutcomes"] as $key3 => $val3){

                    // if $bool becomes true then we know modification occurred already so we stop looping
                    // through the learning outcomes
                    $bool = false;

                    // loop through each learning outcome in the section
                    for($i = 1; $i < count($val2["LearningOutcomes"]); $i++){

                        // only perform modification if the next lo exists and has questions in the questions json file
                        // for ex: current lo => 1.1.1, we are looking for 1.1.2, we see it exists in the openStax json
                        // file and we also see that there are questions in the questions json file for it
                        if($val3["Index"] === ($lo_index + $i) &&
                           $arr1[strval($val1["Index"]) . "." . strval($val2["Index"] . "." . strval($val3["Index"]))] !== 0)
                        {
                            // modification done here
                            $openStax[$key1]["Sections"][$key2]["LearningOutcomes"][$key3]["Access"] = 'True';
                            echo "Current lo: " . $ch_index . "." . $sec_index . "." . $lo_index . "\n";
                            echo "Modified lo: " . $val1["Index"] . "." . $val2["Index"] . "." . $val3["Index"] . "\n";
                            echo "Set 'Access' to 'True'.\n\n";
                            $b = true;
                            $bool = true;
                            // break because we only want to modify 1 learning outcome
                            break;
                        }
                    }

                    // break only if modification occured already
                    if($bool) break;

                }
            }
        }
    }
}


// 2
// LOOK TO OPEN NEXT POSSIBLE SECTION AND LEARNING OUTCOME IN THE CHAPTER THAT THE 
// STUDENT CURRENTLY RESIDES IN
// if $bb remains as false then step 3 will run, if it is switches to true then no 
// more steps will run
$bb = false;
if(!$b){

    // loop through each chapter
    foreach($openStax as $key1 => $val1){

        // only looking for specific chapter
        if($val1["Index"] === $ch_index){

            // loop through each section in that chapter
            foreach($val1["Sections"] as $key2 => $val2){

                // if $bool1 becomes true then we know modification occurred already so we stop looping through the sections
                $bool1 = false;

                for($i = 1; $i < count($val1["Sections"]); $i++){

                    if($val2["Index"] === ($sec_index + $i) && 
                       $arr2[strval($val1["Index"]) . "." . strval($val2["Index"])] !== 0)
                    {
                        // modification done here
                        $openStax[$key1]["Sections"][$key2]["Access"] = 'True';
                        echo "Current sec: " . $ch_index . "." . $sec_index . "\n";
                        echo "Modified sec: " . $val1["Index"] . "." . $val2["Index"] . "\n";
                        echo "Set 'Access' to 'True'.\n\n";
                        $bb = true;
                        $bool1 = true;
                        // break because we only want to modify 1 section
                        break;
                    }
                }

                // section was modified then we must open up the first lo with questions in that section
                if($bool1){

                    // loop through each lo in that section
                    foreach($val2["LearningOutcomes"] as $key3 => $val3){

                        // hold the lo the student is currently on
                        $old_lo_index = $lo_index;
                        // reset $lo_index to 1
                        $lo_index = 1;
                        // if $bool2 becomes true then we know modification occurred already so we stop looping through the los
                        $bool2 = false;

                        // loop through each learning outcome in the section
                        for($j = 0; $j < count($val2["LearningOutcomes"]); $j++){

                            // only perform modification if the next lo exists and has questions in the questions json file
                            // for ex: current lo => 1.1.1, we are looking for 1.1.2, we see it exists in the openStax json
                            // file and we also see that there are questions in the questions json file for it
                            if($val3["Index"] === ($lo_index + $j) &&
                               $arr1[strval($val1["Index"]) . "." . strval($val2["Index"] . "." . strval($val3["Index"]))] !== 0)
                            {
                                // modification done here
                                $openStax[$key1]["Sections"][$key2]["LearningOutcomes"][$key3]["Access"] = 'True';
                                echo "Current lo: " . $ch_index . "." . $sec_index . "." . $old_lo_index . "\n";
                                echo "Modified lo: " . $val1["Index"] . "." . $val2["Index"] . "." . $val3["Index"] . "\n";
                                echo "Set 'Access' to 'True'.\n\n";
                                $bool2 = true;
                                // break because we only want to modify 1 learning outcome
                                break;
                            }
                        }

                        // break out of lo loop once done with lo modification
                        if($bool2) break;

                    }

                    // break out of sections loop once done with lo modification
                    break;

                }
            }
        }
    }
}


// 3
// LOOK TO OPEN NEXT POSSIBLE CHAPTER, SECTION, AND LEARNING OUTCOME THAT COMES RIGHT
// AFTER THE CHAPTER THE STUDENT IS CURRENTLY IN
// if $bbb remains as false then step 4 will run, if it is switches to true then no 
// more steps will run
$bbb = false;
if(!$bb && !$b){

    // loop through each chapter
    foreach($openStax as $key1 => $val1){

        // if $bool1 becomes true then we know modification occurred already so we stop looping through the chapters
        $bool1 = false;

        // loop through each chapter
        for($i = 1; $i < count($openStax); $i++){

            if($val1["Index"] === ($ch_index + $i) && $arr3[strval($val1["Index"])] !== 0){
                // modification done here
                $openStax[$key1]["Access"] = 'True';
                echo "Current ch: " . $ch_index . "\n";
                echo "Modified ch: " . $val1["Index"] . "\n";
                echo "Set 'Access' to 'True'.\n\n";
                $bbb = true;
                $bool1 = true;
                // break because we only want to modify 1 learning outcome
                break;
            }

        }

        // proceed to modify first section in that chapter
        if($bool1){

            // loop through each section in that chapter
            foreach($val1["Sections"] as $key2 => $val2){

                // hold the sec the student is currently on
                $old_sec_index = $sec_index;
                // reset $sec_index to 1
                $sec_index = 1;
                // if $bool2 becomes true then we know modification occurred already so we stop looping through the sections
                $bool2 = false;

                for($j = 0; $j < count($val1["Sections"]); $j++){

                    if($val2["Index"] === ($sec_index + $j) && 
                       $arr2[strval($val1["Index"]) . "." . strval($val2["Index"])] !== 0)
                    {
                        // modification done here
                        $openStax[$key1]["Sections"][$key2]["Access"] = 'True';
                        echo "Current sec: " . $ch_index . "." . $old_sec_index . "\n";
                        echo "Modified sec: " . $val1["Index"] . "." . $val2["Index"] . "\n";
                        echo "Set 'Access' to 'True'.\n\n";
                        $bool2 = true;
                        // break because we only want to modify 1 learning outcome
                        break;
                    }
                }

                // procced to modify first lo in that section
                if($bool2){

                    // hold the lo the student is currently on
                    $old_lo_index = $lo_index;
                    // reset $lo_index to 1
                    $lo_index = 1;
                    // if $bool3 becomes true then we know modification occurred already so we stop looping through the los
                    $bool3 = false;

                    // start lo loop
                    foreach($val2["LearningOutcomes"] as $key3 => $val3){

                        // loop through each learning outcome in the section
                        for($k = 0; $k < count($val2["LearningOutcomes"]); $k++){

                            // only perform modification if the next lo exists and has questions in the questions json file
                            // for ex: current lo => 1.1.1, we are looking for 1.1.2, we see it exists in the openStax json
                            // file and we also see that there are questions in the questions json file for it
                            if($val3["Index"] === ($lo_index + $k) &&
                               $arr1[strval($val1["Index"]) . "." . strval($val2["Index"] . "." . strval($val3["Index"]))] !== 0)
                            {
                                // modification done here
                                $openStax[$key1]["Sections"][$key2]["LearningOutcomes"][$key3]["Access"] = 'True';
                                echo "Current lo: " . $ch_index . "." . $old_sec_index . "." . $old_lo_index . "\n";
                                echo "Modified lo: " . $val1["Index"] . "." . $val2["Index"] . "." . $val3["Index"] . "\n";
                                echo "Set 'Access' to 'True'.\n\n";
                                $bool3 = true;
                                // break because we only want to modify 1 learning outcome
                                break;
                            }
                        }

                        // break from lo loop
                        if($bool3) break;

                    }

                    // break from section loop
                    break;

                }

            }

            // break from chapter loop
            break;
        }
    }
}


// 4
// if nothing else was modified then that means user is complete with all chapters, sections, and los
if(!$bbb && !$bb && !$b){
    echo "Nothing was modified.\n\n";
}


// PROCEED TO REWRITE OPENSTAX JSON FILE
echo "Now rewriting respective openStax json file\n";
// rewrite user openStax json file (original data + modified data)
$myfile = fopen("../../user_data/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json", "w") or die("Unable to open file!");

// begin writing
fwrite($myfile, "[");

// loop through each chapter
$c1 = 0;
foreach($openStax as $chapter){

    // comma at the end
    if($c1 !== count($openStax) - 1){
        $string = "\n\t" . "{" . "\n\t\t\"Index\": " . $chapter["Index"] . "," . "\n\t\t\"Name\": \"" . $chapter["Name"] . "\"," . "\n\t\t\"Access\": \"" . $chapter["Access"] . "\",";

        $string .= "\n\t\t\"Introduction\": {";
        $string .= "\n\t\t\t\"Name\": \"" . $chapter["Introduction"]["Name"] . "\",";
        $string .= "\n\t\t\t\"Description\": \"" . $chapter["Introduction"]["Description"] . "\",";
        $string .= "\n\t\t\t\"Document\": \"" . $chapter["Introduction"]["Document"] . "\",";
        $string .= "\n\t\t\t\"PageStart\": " . $chapter["Introduction"]["PageStart"];
        $string .= "\n\t\t},";

        $string .= "\n\t\t\"Review\": {";
        $string .= "\n\t\t\t\"Name\": \"" . $chapter["Review"]["Name"] . "\",";
        $string .= "\n\t\t\t\"Document\": \"" . $chapter["Review"]["Document"] . "\",";
        $string .= "\n\t\t\t\"PageStart\": " . $chapter["Review"]["PageStart"];
        $string .= "\n\t\t},";

        $string .= "\n\t\t\"Sections\": [";
        // loop through inner Sections array
        for($i = 0; $i < count($chapter["Sections"]); $i++){
            // comma at the end
            if($i !== count($chapter["Sections"]) - 1){
                $string .= "\n\t\t\t{";
                $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] ."\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t},";//learning outcome comma here
                    }
                    // no comma
                    else{
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t}";//no learning outcome comma here
                    }
                }

                $string .= "\n\t\t\t\t],";
                $string .= "\n\t\t\t\t\"score\": [";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0";
                $string .= "\n\t\t\t\t]";
                $string .= "\n\t\t\t},";//section comma here

            }
            // no comma
            else{
                $string .= "\n\t\t\t{";
                $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] ."\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t},";//learning outcome comma here
                    }
                    // no comma
                    else{
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t}";//no learning outcome comma here
                    }
                }

                $string .= "\n\t\t\t\t],";
                $string .= "\n\t\t\t\t\"score\": [";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0";
                $string .= "\n\t\t\t\t]";
                $string .= "\n\t\t\t}";//no section comma here
            }
        }

        $string .= "\n\t\t],";
        $string .= "\n\t\t\"score\": [";
        $string .= "\n\t\t\t0,";
        $string .= "\n\t\t\t0,";
        $string .= "\n\t\t\t0";
        $string .= "\n\t\t]";
        $string .= "\n\t},";//chapter comma here

        // writing 
        fwrite($myfile, $string);
    }
    // no comma
    else{
        $string = "\n\t" . "{" . "\n\t\t\"Index\": " . $chapter["Index"] . "," . "\n\t\t\"Name\": \"" . $chapter["Name"] . "\"," . "\n\t\t\"Access\": \"" . $chapter["Access"] ."\",";

        $string .= "\n\t\t\"Introduction\": {";
        $string .= "\n\t\t\t\"Name\": \"" . $chapter["Introduction"]["Name"] . "\",";
        $string .= "\n\t\t\t\"Description\": \"" . $chapter["Introduction"]["Description"] . "\",";
        $string .= "\n\t\t\t\"Document\": \"" . $chapter["Introduction"]["Document"] . "\",";
        $string .= "\n\t\t\t\"PageStart\": " . $chapter["Introduction"]["PageStart"];
        $string .= "\n\t\t},";

        $string .= "\n\t\t\"Review\": {";
        $string .= "\n\t\t\t\"Name\": \"" . $chapter["Review"]["Name"] . "\",";
        $string .= "\n\t\t\t\"Document\": \"" . $chapter["Review"]["Document"] . "\",";
        $string .= "\n\t\t\t\"PageStart\": " . $chapter["Review"]["PageStart"];
        $string .= "\n\t\t},";

        $string .= "\n\t\t\"Sections\": [";
        // loop through inner Sections array
        for($i = 0; $i < count($chapter["Sections"]); $i++){
            // comma at the end
            if($i !== count($chapter["Sections"]) - 1){
                $string .= "\n\t\t\t{";
                $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] ."\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t},";//learning outcome comma here
                    }
                    // no comma
                    else{
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t}";//no learning outcome comma here
                    }
                }

                $string .= "\n\t\t\t\t],";
                $string .= "\n\t\t\t\t\"score\": [";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0";
                $string .= "\n\t\t\t\t]";
                $string .= "\n\t\t\t},";//section comma here

            }
            // no comma
            else{
                $string .= "\n\t\t\t{";
                $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] ."\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t},";//learning outcome comma here
                    }
                    // no comma
                    else{
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] ."\",";
                        $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                        if(gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string"){
                            $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                        }
                        else{
                            $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                        }
                        $string .= "\n\t\t\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t\t\t]";
                        $string .= "\n\t\t\t\t\t}";//no learning outcome comma here
                    }
                }

                $string .= "\n\t\t\t\t],";
                $string .= "\n\t\t\t\t\"score\": [";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0,";
                $string .= "\n\t\t\t\t\t0";
                $string .= "\n\t\t\t\t]";
                $string .= "\n\t\t\t}";//no section comma here
            }
        }

        $string .= "\n\t\t],";
        $string .= "\n\t\t\"score\": [";
        $string .= "\n\t\t\t0,";
        $string .= "\n\t\t\t0,";
        $string .= "\n\t\t\t0";
        $string .= "\n\t\t]";
        $string .= "\n\t}";//no chapter comma here

        // writing 
        fwrite($myfile, $string);
    }

    // updating counter
    $c1++;
}
echo "\n";

// finalizing writing
fwrite($myfile, "\n]");
fclose($myfile);
echo "Successfully Rewrote OpenStax\n\n";


// output the arrays for display purposes
print_r($arr1);
echo "\n";
print_r($arr2);
echo "\n";
print_r($arr3);
echo "\n";

?>