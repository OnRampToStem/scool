<?php
/*
    This PHP script will do the following:
    1. Create PGSQL "users" table if it does not already exist.
    2. Will take an input JSON file "users.json", which represents a list of users that will
       be inserted into the "users" table (many students, only 1 instructor).
    3. Each student will also get their own copy of the static questions json file and of the
       static openStax json file.
*/

// for echo display purposes
header('Content-type: text/plain');

// connect to the DB using the config file
require_once "config.php";

// create the users table if it does not already exist in the PostgreSQL database
$query = "CREATE TABLE IF NOT EXISTS users (
    pkey serial primary key,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    type TEXT NOT NULL,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL,
    section_id TEXT NOT NULL,
    instructor TEXT,
    created_on TIMESTAMP NOT NULL,
    last_signed_in TIMESTAMP
)";
pg_query($con, $query) or die("Cannot execute query: $query \n");
echo "The users table has been successfully created or was already there!\n";

/* $json_assoc_arr used to give each student a copy of the PGSQL questions db, but in json file format */
// filepath
$json_filename = "user_json/users1.json"; // change filepath if needed
// read the users.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_assoc_arr = json_decode($json, true);

// create questions & openStax directories if they do not exist
if (!is_dir("/Applications/MAMP/htdocs/hub_v1/user_data/" . $json_assoc_arr[0]['course_name'] . "-" . $json_assoc_arr[0]['course_id'] . "-" . $json_assoc_arr[0]['section_id'] . "/questions")) {
    mkdir("/Applications/MAMP/htdocs/hub_v1/user_data/" . $json_assoc_arr[0]['course_name'] . "-" . $json_assoc_arr[0]['course_id'] . "-" . $json_assoc_arr[0]['section_id'] . "/questions", 0777, true);
}
if (!is_dir("/Applications/MAMP/htdocs/hub_v1/user_data/" . $json_assoc_arr[0]['course_name'] . "-" . $json_assoc_arr[0]['course_id'] . "-" . $json_assoc_arr[0]['section_id'] . "/openStax")) {
    mkdir("/Applications/MAMP/htdocs/hub_v1/user_data/" . $json_assoc_arr[0]['course_name'] . "-" . $json_assoc_arr[0]['course_id'] . "-" . $json_assoc_arr[0]['section_id'] . "/openStax", 0777, true);
}

/* $json_data used to give each student a copy of the new_openStax json file */
// filepath
$json_filename = "../assets/json_data/new_openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_data = json_decode($json, true);

// create timestamp to be inserted for created_on attribute of users
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$timestamp = $date->format('Y-m-d H:i:s');


// loop through each user
foreach($json_assoc_arr as $user){

    // instructors won't receive a copy of the json questions or openStax, only students
    if($user['type'] !== "instructor"){

        // STEP 1 
        // we need to create each user their own copy of the JSON questions in the database
        // selecting all rows in the static questions database table
        $query = "SELECT * FROM questions"; 
        $rs = pg_query($con, $query) or die("Cannot execute query: $query\n");

        $result = pg_query($con, $query);
        $rows = pg_num_rows($result);
        //echo $rows . " row(s) returned.\n";

        // WRITING COPY OF QUESTIONS JSON FILE 

        // begin writing to personalized questions file
        $questions_file = fopen("/Applications/MAMP/htdocs/hub_v1/user_data/" . $user['course_name'] . "-" . $user['course_id'] . "-" . $user['section_id'] . "/questions/" . $user['email'] . ".json", "w") or die("Unable to open file!");
        fwrite($questions_file, "[\n");

        // loop to write to file
        $counter = 1;
        while ($row = pg_fetch_row($rs)) {

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
            // END OPTIONS DATA MODIFICATIONS 

            // rightAnswer array modification
            $row[6] = str_replace('{', '[', $row[6]);
            $row[6] = str_replace('}', ']', $row[6]);

            // isImage array modification
            $row[7] = str_replace('{', '[', $row[7]);
            $row[7] = str_replace('}', ']', $row[7]);

            if($counter == $rows){
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
            else{
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
        echo "Successfully wrote " . $user['email'] . "'s own questions json file.\n";


        // WRITING COPY OF OPENSTAXS JSON FILE 

        // create new_openStax.json user file and begin writing original data + new data
        $openStax_file = fopen("/Applications/MAMP/htdocs/hub_v1/user_data/" . $user['course_name'] . "-" . $user['course_id'] . "-" . $user['section_id'] . "/openStax/" . $user['email'] . ".json", "w") or die("Unable to open file!");

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
        echo "Successfully wrote " . $user['email'] . "'s own openStax json file.\n";

    }

    // STEP 2 
    // hash each user password using the current default algorithm (BCRYPT)
    $hashed_password = password_hash($user["password"], PASSWORD_DEFAULT);

    // inserting user values into table, (manually adding ' ' needed for PostgreSQL query strings / text)
    if ($user['type'] !== "instructor") {
        // students
        $query = "INSERT INTO users(first_name, last_name, email, password, type, course_name, course_id, section_id, instructor, created_on) VALUES ('" . $user['first_name'] . "', '" . $user['last_name'] . "', '" . $user['email'] . "', '" . $hashed_password . "', '" . $user['type'] . "', '" . $user['course_name'] . "', '" . $user['course_id'] . "', '" . $user['section_id'] . "', '" . $user['instructor'] . "', '" . $timestamp . "')";
        pg_query($con, $query) or die("Cannot execute query: $query \n");
        echo "Inserted " . $user['email'] . " into users table successfully!\n";
    }
    else {
        // single instructor
        $query = "INSERT INTO users(first_name, last_name, email, password, type, course_name, course_id, section_id, instructor, created_on) VALUES ('" . $user['first_name'] . "', '" . $user['last_name'] . "', '" . $user['email'] . "', '" . $hashed_password . "', '" . $user['type'] . "', '" . json_encode($user['course_name']) . "', '" . json_encode($user['course_id']) . "', '" . json_encode($user['section_id']) . "', '" . $user['instructor'] . "', '" . $timestamp . "')";
        pg_query($con, $query) or die("Cannot execute query: $query \n");
        echo "Inserted " . $user['email'] . " into users table successfully!\n";
    }

}

echo "Closing connection to PostgreSQL database.\n";
pg_close($con);

?>