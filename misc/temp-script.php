<?php

// connect to the db
require_once "config.php";

// prepare and execute query for getting all static questions from 'questions' table
$query = "SELECT * FROM questions"; 
$res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");
$rows = pg_num_rows($res);

// STATIC QUESTIONS

$filepath = "../scale/user_data/Temporary Course-123/questions/temp-student@gmail.com.json";

$questions_file = fopen($filepath, "w") or die("Unable to open file!");

fwrite($questions_file, "[\n");

// loop to write to file
$counter = 1;
while ($row = pg_fetch_row($res)) {
    // OPTIONS DATA MODIFICATIONS 
    // first remove { from options string $row[5]
    $row[5] = substr($row[5], 1);
    // then remove } from options string $row[5]
    $row[5] = substr($row[5], 0, -1);
    // then remove all double quotes from options string $row[5]
    $row[5] = str_replace('"', '', $row[5]);
    // convert options string $row[5] => to an array (based on commas)
    $options_arr = explode(",", $row[5]);
    // get options_arr length
    $options_length = count($options_arr);

    // rightAnswer array modification
    $row[6] = str_replace('{', '[', $row[6]);
    $row[6] = str_replace('}', ']', $row[6]);

    // isImage array modification
    $row[7] = str_replace('{', '[', $row[7]);
    $row[7] = str_replace('}', ']', $row[7]);

    if ($counter == $rows) {
        // no comma, because it is the last math question
        $db_string = "{\n\"pkey\": $row[0], \n\"title\": \"$row[1]\", \n\"text\": \"$row[2]\", \n\"pic\": \"$row[3]\", \n\"numTries\": \"$row[4]\", \n\"options\": [";
            
        // insert each option into $db_string
        for($i = 0; $i < $options_length; $i++){
            if($i == $options_length - 1){
                $db_string .= "\"$options_arr[$i]\"], ";
            }
            else{
                $db_string .= "\"$options_arr[$i]\",";
            }
        }
            
        $db_string .= "\n\"rightAnswer\": $row[6], \n\"isImage\": $row[7], \n\"tags\": \"$row[8]\", \n\"difficulty\": \"$row[9]\", \n\"selected\": \"$row[10]\", \n\"numCurrentTries\": \"$row[11]\", \n\"correct\": \"$row[12]\", \n\"datetime_started\": \"$row[13]\", \n\"datetime_answered\": \"$row[14]\", \n\"createdOn\": \"$row[15]\"\n}\n";

        // replacing the commas back in the options array
        $db_string = str_replace('*%', ',', $db_string);

        fwrite($questions_file, $db_string);
    }
    else {
        // normal write
        $db_string = "{\n\"pkey\": $row[0], \n\"title\": \"$row[1]\", \n\"text\": \"$row[2]\", \n\"pic\": \"$row[3]\", \n\"numTries\": \"$row[4]\", \n\"options\": [";
            
        // insert each option into $db_string
        for($i = 0; $i < $options_length; $i++){
            if($i == $options_length - 1){
                $db_string .= "\"$options_arr[$i]\"], ";
            }
            else{
                $db_string .= "\"$options_arr[$i]\",";
            }
        }
            
        $db_string .= "\n\"rightAnswer\": $row[6], \n\"isImage\": $row[7], \n\"tags\": \"$row[8]\", \n\"difficulty\": \"$row[9]\", \n\"selected\": \"$row[10]\", \n\"numCurrentTries\": \"$row[11]\", \n\"correct\": \"$row[12]\", \n\"datetime_started\": \"$row[13]\", \n\"datetime_answered\": \"$row[14]\", \n\"createdOn\": \"$row[15]\"\n},\n";

        // replacing the commas back in the options array
        $db_string = str_replace('*%', ',', $db_string);

        fwrite($questions_file, $db_string);
    }

    $counter++;
}

fwrite($questions_file, "]\n");

fclose($questions_file);

chmod("../scale/user_data/Temporary Course-123/questions/temp-student@gmail.com.json", 0777) or die("Could not modify questions json perms.");



// OPEN STAX

// filepath
$json_filename = "new_openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_data = json_decode($json, true);

$filepath = "../scale/user_data/Temporary Course-123/openStax/temp-student@gmail.com.json";

$openStax_file = fopen($filepath, "w") or die("Unable to open file!");

// begin writing
fwrite($openStax_file, "[");

// loop through each chapter
$c1 = 0;
foreach($json_data as $chapter){
    // comma at the end
    if($c1 !== count($json_data) - 1){
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
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
        fwrite($openStax_file, $string);
    }
    // no comma
    else{
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
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
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
        fwrite($openStax_file, $string);
    }

    // updating counter
    $c1++;
}

// finalizing writing
fwrite($openStax_file, "\n]");

fclose($openStax_file);

chmod("../scale/user_data/Temporary Course-123/openStax/temp-student@gmail.com.json", 0777) or die("Could not modify openStax json perms.");


?>