<?php
// for display purposes
header("Content-type: text/plain");
/*
    This PHP script is being used to rewrite the original "openStax.json" file to add in an "Access" attribute
    to each chapter, section, and learning outcome in the file, with value or either "True" or "False".
*/

// filepath
$json_filename = "data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_data = json_decode($json, true);

// create new_openStax.json user file and begin writing original data + new data
$myfile = fopen("/Applications/MAMP/htdocs/hub_v1/data/new_openStax.json", "w") or die("Unable to open file!");

// begin writing
fwrite($myfile, "[");

// loop through each chapter
$c1 = 0;
foreach($json_data as $chapter){

    // comma at the end
    if($c1 !== count($json_data) - 1){
        $string = "\n\t" . "{" . "\n\t\t\"Index\": " . $chapter["Index"] . "," . "\n\t\t\"Name\": \"" . $chapter["Name"] . "\"," . "\n\t\t\"Access\": \"False\",";

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
                $string .= "\n\t\t\t\t\"Access\": \"False\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                $string .= "\n\t\t\t\t\"Access\": \"False\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
        $string = "\n\t" . "{" . "\n\t\t\"Index\": " . $chapter["Index"] . "," . "\n\t\t\"Name\": \"" . $chapter["Name"] . "\"," . "\n\t\t\"Access\": \"False\",";

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
                $string .= "\n\t\t\t\t\"Access\": \"False\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                $string .= "\n\t\t\t\t\"Access\": \"False\",";

                $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                // loop through inner inner LearningOutcomes array
                for($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++){
                    // comma at the end
                    if($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1){
                        $string .= "\n\t\t\t\t\t{";
                        $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                        $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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
                        $string .= "\n\t\t\t\t\t\t\"Access\": \"False\",";
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

// finalizing writing
fwrite($myfile, "\n]");
fclose($myfile);
echo "Success\n";

?>