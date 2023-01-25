<?php
/* GLOBALS */
$token;
$obj;
$query;
$res;

// checking for POST
if (isset($_POST['token'])) {
	$token = $_POST['token'];
	echo "Token received from POST. <br><br>";
}
else {
    echo "Error, no POST received. <br><br>";
    exit;
}

function DecodeToken($token) {
	return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $token)[1]))));
}

function display($obj) {
    echo "Name: {$obj->name} <br>";
    echo "Email: {$obj->email} <br>";
    echo "Unique Name: {$obj->unique_name} <br>";
    echo "Sub: {$obj->sub} <br>";
	echo "Role: {$obj->roles[0]} <br>";
    echo "Course Name: {$obj->context->title} <br>";
	echo "Course ID: {$obj->context->id} <br>";
    echo "Picture: {$obj->picture} <br>";
    echo "iat: {$obj->iat} <br>";
    echo "exp: {$obj->exp} <br>";
    echo "iss: {$obj->iss} <br>";
    echo "aud: {$obj->aud} <br>";
    echo "<br>";
}

// decode the sent token
$obj = DecodeToken($token);
// display received data on client side
display($obj);

// create timestamp to be inserted / updated for users when creating account & logging into account
$date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$timestamp = $date->format('Y-m-d H:i:s');

// connect to the db
require_once "config.php";

/* Now begin process of analyzing sent data and determine what should be done with the data */

/** 
 * 
 * INSTRUCTOR HANDLER 
 * 
 */
if ($obj->roles[0] === "Instructor") {
    echo "User is of type 'Instructor'. <br>";

	// check to see if Instructor's email already exists in the 'users' table
	$query = "SELECT * FROM users WHERE email = '{$obj->email}'";
	$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

	if (pg_num_rows($res) === 0) {
        echo "'Instructor' is not in the 'users' table. <br>";

        // Now we must check that there is not an Instructor with the same course_name and course_id as the incoming Instructor
        $query = "SELECT * FROM users WHERE type = 'Instructor' AND course_name LIKE '%{$obj->context->title}%'
                  AND course_id LIKE '%{$obj->context->id}%'";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

        if (pg_num_rows($res) > 0) {
            echo "We already have a user of type 'Instructor' for the course name: '{$obj->context->title}' and 
                  course id: '{$obj->context->id}'. (Only 1 Instructor allowed per course name, course id) <br>";
            exit;
        }
        else {
            // insert the Instructor

            // putting course name and course id into their own arrays, bc an Instructor can be an Instructor for multiple
            // courses, so we can add to this array if the Instructor enters OR2STEM from a different course
            $course_name = [$obj->context->title];
            $course_id = [$obj->context->id];

            // prepare and execute query for inserting Instructor in the 'users' table
            $query = "INSERT INTO users(name, email, unique_name, sub, type, pic, instructor, course_name, course_id, iat, exp, iss, aud, created_on, last_signed_in)
                      VALUES('{$obj->name}', '{$obj->email}', '{$obj->unique_name}', '{$obj->sub}', '{$obj->roles[0]}', '{$obj->picture}', '', '" . json_encode($course_name)
                      . "', '" . json_encode($course_id) . "', '{$obj->iat}', '{$obj->exp}', '{$obj->iss}', '{$obj->aud}', '{$timestamp}', '{$timestamp}')";
            pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
            echo "Inserted 'Instructor' into 'users' table successfully! <br>";

            // create the directories that will keep track of the student progress in 'user_data'
            echo "Creating directory in: ../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions <br>";
            $directory_path = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions";
            mkdir($directory_path, 0777, true) or die("Failed to create directory.");
        
            echo "Creating directory in: ../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax <br>";
            $directory_path = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax";
            mkdir($directory_path, 0777, true) or die("Failed to create directory.");

            echo "Starting Session. <br>";
            session_start();

            echo "Setting required session variables. <br>";
            $_SESSION["loggedIn"] = true;
            $_SESSION["name"] = $obj->name;
            $_SESSION["email"] = $obj->email;
            $_SESSION["type"] = $obj->roles[0];
            $_SESSION["pic"] = $obj->picture;
            $_SESSION["course_name"] = json_encode($course_name);
            $_SESSION["course_id"] = json_encode($course_id);

            echo "Redirecting to Instructor Home Page. <br>";
            header("location: ../scale/instructor/instr_index1.php");
        }
	}		
	else {
        echo "'Instructor' is already in the 'users' table. <br>";

		// However, Instructor may be coming in with 4 different possibilities:
        // 1. Instructor already has their email in the db associated with a different user type
        // 2. Instructor is coming in with the same course_name and course_id that is already stored
        // 3. Instructor is coming in with the same course_name, but different course_id from what is stored
        // 4. Instructor is coming in with different course_name and different course_id from what is stored

        // handle case 1 here
        $query = "SELECT type FROM users WHERE email='{$obj->email}'";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
        $stored_type = pg_fetch_result($res, 0, 0);

        if ($stored_type !== $obj->roles[0]) {
            echo "An account already exists with the current email, that is not of type 'Instructor'. <br>";
            exit;
        }
        else {
            // handle cases 2 - 4 here
            // compare current course name & course id to the one that is stored in the db
            $query = "SELECT course_name, course_id FROM users WHERE email='{$obj->email}'";
            $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
            $course_name = json_decode(pg_fetch_result($res, 0, 0));
            $course_id = json_decode(pg_fetch_result($res, 0, 1));

            if (!in_array($obj->context->title, $course_name) || !in_array($obj->context->id, $course_id)) {
                // cases 3 and 4 are handled here
                echo "'Instructor' either has a different course name or course id from what was already stored. <br>";

                // insert new values into existing arrays
                array_push($course_name, $obj->context->title);
                array_push($course_id, $obj->context->id);

                // update data into the db
                $query = "UPDATE users SET course_name = '" . json_encode($course_name) . "', course_id = '" . json_encode($course_id)
                        . "', last_signed_in = '{$timestamp}' WHERE email = '{$obj->email}'";
                pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
                echo "Updated 'course_name', 'course_id', and 'last_signed_in' of 'Instructor'. <br>";

                // create the new directories that will keep track of the student progress in 'user_data'
                echo "Creating directory in: ../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions <br>";
                $directory_path = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions";
                mkdir($directory_path, 0777, true) or die("Failed to create directory.");
            
                echo "Creating directory in: ../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax <br>";
                $directory_path = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax";
                mkdir($directory_path, 0777, true) or die("Failed to create directory.");

                echo "Starting Session. <br>";
                session_start();

                echo "Setting required session variables. <br>";
                $_SESSION["loggedIn"] = true;
                $_SESSION["name"] = $obj->name;
                $_SESSION["email"] = $obj->email;
                $_SESSION["type"] = $obj->roles[0];
                $_SESSION["pic"] = $obj->picture;
                $_SESSION["course_name"] = json_encode($course_name);
                $_SESSION["course_id"] = json_encode($course_id);

                echo "Redirecting to Instructor Home Page. <br>";
                header("location: ../scale/instructor/instr_index1.php");
            }
            else {
                // case 2 is handled here
                echo "'Instructor' has the same course name and course id from what was already stored. <br>";

                // query to update Instructor's 'last_signed_in' field
                $query = "UPDATE users SET last_signed_in = '{$timestamp}' WHERE email = '{$obj->email}'";
                pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
                echo "Updated 'last_signed_in' of 'Instructor'. <br>";

                echo "Starting Session. <br>";
                session_start();

                echo "Setting required session variables. <br>";
                $_SESSION["loggedIn"] = true;
                $_SESSION["name"] = $obj->name;
                $_SESSION["email"] = $obj->email;
                $_SESSION["type"] = $obj->roles[0];
                $_SESSION["pic"] = $obj->picture;
                $_SESSION["course_name"] = json_encode($course_name);
                $_SESSION["course_id"] = json_encode($course_id);

                echo "Redirecting to Instructor Home Page. <br>";
                header("location: ../scale/instructor/instr_index1.php");
            }
        }
	}
}
/** 
 * 
 * LEARNER HANDLER 
 * 
 */
elseif ($obj->roles[0] === "Learner") {
    echo "User is of type 'Learner'. <br>";

	// check to see if Learner's email already exists in the 'users' table
	$query = "SELECT * FROM users WHERE email = '{$obj->email}'";
	$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

	if (pg_num_rows($res) === 0) {
        echo "'Learner' is not in the 'users' table. <br>";

		// query to get the email of the instructor that corresponds to the student (based on course_name & course_id)
		$query = "SELECT email FROM users WHERE type = 'Instructor' AND course_name LIKE '%{$obj->context->title}%'
				  AND course_id LIKE '%{$obj->context->id}%'";
		$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

        // check to make sure Instructor has created their account
        if (pg_num_rows($res) === 0) {
            echo "Can not create the 'Learner' account if corresponding Instructor has not created their account. <br>";
            exit;
        }
        else {
            // get the instructor's email
            $instr_email = pg_fetch_result($res, 0, 0);

            // prepare and execute query for inserting Learner in the 'users' table
            $query = "INSERT INTO users(name, email, unique_name, sub, type, pic, instructor, course_name, course_id, iat, exp, iss, aud, created_on, last_signed_in)
                      VALUES('{$obj->name}', '{$obj->email}', '{$obj->unique_name}', '{$obj->sub}', '{$obj->roles[0]}', '{$obj->picture}', '{$instr_email}', '{$obj->context->title}',
                      '{$obj->context->id}', '{$obj->iat}', '{$obj->exp}', '{$obj->iss}', '{$obj->aud}', '{$timestamp}', '{$timestamp}')";
            pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");
            echo "Inserted 'Learner', into the 'users' table successfully! <br>";

            // prepare and execute query for getting all static questions from 'questions' table
            $query = "SELECT * FROM questions"; 
            $res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");
            $rows = pg_num_rows($res);

            // begin writing Learner's questions file
            echo "Now writing 'Learner's' own static questions json file. <br>";

            $filepath = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions/{$obj->email}.json";

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

            chmod("../scale/user_data/{$obj->context->title}-{$obj->context->id}/questions/{$obj->email}.json", 0777) or die("Could not modify questions json perms.");

            echo "Successfully wrote 'Learner's' own static questions json file. <br>";


            // begin writing Learner's openStax file
            echo "Now writing 'Learner's' own openStax json file. <br>";

            // filepath
            $json_filename = "new_openStax.json";
            // read the openStax.json file to text
            $json = file_get_contents($json_filename);
            // decode the text into a PHP assoc array
            $json_data = json_decode($json, true);

            $filepath = "../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax/{$obj->email}.json";

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

            chmod("../scale/user_data/{$obj->context->title}-{$obj->context->id}/openStax/{$obj->email}.json", 0777) or die("Could not modify openStax json perms.");

            echo "Successfully wrote 'Learner's' own openStax json file. <br>";

            echo "Starting Session. <br>";
            session_start();

            echo "Setting required session variables. <br>";
            $_SESSION["loggedIn"] = true;
            $_SESSION["name"] = $obj->name;
            $_SESSION["email"] = $obj->email;
            $_SESSION["type"] = $obj->roles[0];
            $_SESSION["pic"] = $obj->picture;
            $_SESSION["course_name"] = $obj->context->title;
            $_SESSION["course_id"] = $obj->context->id;

            echo "Redirecting to Student Home Page. <br>";
			header("location: ../scale/student/student_index.php");
        }
	}
	else {
        echo "'Learner' is already in the 'users' table. <br>";

        // check that the Learner has the same course_name, course_id from what is stored
        $query = "SELECT course_name, course_id FROM users WHERE email = '{$obj->email}'";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
        $temp_course_name = pg_fetch_result($res, 0, 0);
        $temp_course_id = pg_fetch_result($res, 0, 1);

        if ($temp_course_name === $obj->context->title && $temp_course_id === $obj->context->id) {
            // Learner is just trying to login
            // prepare and execute query to update Learner's 'last_signed_in' field
            $query = "UPDATE users SET last_signed_in = '{$timestamp}' WHERE email = '{$obj->email}'";
            pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
            echo "Updated 'last_signed_in' of 'Learner'. <br>";

            echo "Starting Session. <br>";
            session_start();

            echo "Setting required session variables. <br>";
            $_SESSION["loggedIn"] = true;
            $_SESSION["name"] = $obj->name;
            $_SESSION["email"] = $obj->email;
            $_SESSION["type"] = $obj->roles[0];
            $_SESSION["pic"] = $obj->picture;
            $_SESSION["course_name"] = $obj->context->title;
            $_SESSION["course_id"] = $obj->context->id;

            echo "Redirecting to Student Home Page. <br>";
            header("location: ../scale/student/student_index.php");
        }
        else {
            // Learner is using the same email in another Canvas course to try to work on OR2STEM
            // (not currently allowed)
            echo "You can only join OR2STEM in a single course if you are a 'Learner'. <br>";
            exit;
        }
	}
}
/** 
 * 
 * MENTOR HANDLER 
 * 
 */
elseif ($obj->roles[0] === "Mentor") {
    echo "User is of type 'Mentor'. <br>";
    // A Mentor can be in many different classes (based on class name, class id)
    
    // check to see if Mentor's email already exists in the 'users' table
	$query = "SELECT * FROM users WHERE email = '{$obj->email}'";
	$res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");

	if (pg_num_rows($res) === 0) {
        echo "'Mentor' is not in the 'users' table. <br>";

        // query to get data of the instructor that corresponds to the course_name & course_id
		$query = "SELECT * FROM users WHERE type = 'Instructor' AND course_name LIKE '%{$obj->context->title}%'
                  AND course_id LIKE '%{$obj->context->id}%'";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($con) . "<br>");

        // check to make sure Instructor has created their account
        if (pg_num_rows($res) === 0) {
            echo "Can not create 'Mentor' account if corresponding Instructor has not created their account. <br>";
            exit;
        }
        else {
            // insert the Mentor

            // putting course name and course id into their own arrays, bc a Mentor can be a Mentor for multiple
            // courses, so we can add to this array if the Mentor enters OR2STEM from a different course
            $course_name = [$obj->context->title];
            $course_id = [$obj->context->id];

            // prepare and execute query for inserting Mentor in the 'users' table
            $query = "INSERT INTO users(name, email, unique_name, sub, type, pic, instructor, course_name, course_id, iat, exp, iss, aud, created_on, last_signed_in)
                      VALUES('{$obj->name}', '{$obj->email}', '{$obj->unique_name}', '{$obj->sub}', '{$obj->roles[0]}', '{$obj->picture}', '', '" . json_encode($course_name)
                      . "', '" . json_encode($course_id) . "', '{$obj->iat}', '{$obj->exp}', '{$obj->iss}', '{$obj->aud}', '{$timestamp}', '{$timestamp}')";
            pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
            echo "Inserted 'Mentor' into 'users' table successfully! <br>";

            echo "Starting Session. <br>";
            session_start();

            echo "Setting required session variables. <br>";
            $_SESSION["loggedIn"] = true;
            $_SESSION["name"] = $obj->name;
            $_SESSION["email"] = $obj->email;
            $_SESSION["type"] = $obj->roles[0];
            $_SESSION["pic"] = $obj->picture;
            $_SESSION["course_name"] = json_encode($course_name);
            $_SESSION["course_id"] = json_encode($course_id);

            echo "Redirecting to Instructor Home Page. <br>";
            header("location: ../scale/instructor/instr_index1.php");
        }
    }
    else {
        echo "'Mentor' already exists in the 'users' table. <br>";

		// However, Mentor may be coming in with 4 different possibilities:
        // 1. Mentor already has their email in the db associated with a different user type
        // 2. Mentor is coming in with the same course_name and course_id that is already stored
        // 3. Mentor is coming in with the same course_name, but different course_id from what is stored
        // 4. Mentor is coming in with different course_name and different course_id from what is stored

        // handle case 1 here
        $query = "SELECT type FROM users WHERE email='{$obj->email}'";
        $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($con) . "<br>");
        $stored_type = pg_fetch_result($res, 0, 0);

        if ($stored_type !== $obj->roles[0]) {
            echo "An account already exists with the current email, that is not of type 'Mentor'. <br>";
            exit;
        }
        else {
            // handle cases 2 - 4 here
            // compare current course_name & course_id to the one that is stored in the db
            $query = "SELECT course_name, course_id FROM users WHERE email='{$obj->email}'";
            $res = pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
            $course_name = json_decode(pg_fetch_result($res, 0, 0));
            $course_id = json_decode(pg_fetch_result($res, 0, 1));

            if (!in_array($obj->context->title, $course_name) || !in_array($obj->context->id, $course_id)) {
                // cases 3 and 4 are handled here
                echo "'Mentor' either has a different course name or course id from what was already stored. <br>";

                // insert new values into existing arrays
                array_push($course_name, $obj->context->title);
                array_push($course_id, $obj->context->id);

                // update data for the Mentor
                $query = "UPDATE users SET course_name = '" . json_encode($course_name) . "', course_id = '" . json_encode($course_id)
                        . "', last_signed_in = '{$timestamp}' WHERE email = '{$obj->email}'";
                pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
                echo "Updated 'course_name', 'course_id', and 'last_signed_in' of 'Mentor'. <br>";

                echo "Starting Session. <br>";
                session_start();

                echo "Setting required session variables. <br>";
                $_SESSION["loggedIn"] = true;
                $_SESSION["name"] = $obj->name;
                $_SESSION["email"] = $obj->email;
                $_SESSION["type"] = $obj->roles[0];
                $_SESSION["pic"] = $obj->picture;
                $_SESSION["course_name"] = json_encode($course_name);
                $_SESSION["course_id"] = json_encode($course_id);

                echo "Redirecting to Instructor Home Page. <br>";
                header("location: ../scale/instructor/instr_index1.php");
            }
            else {
                // case 2 is handled here
                echo "'Mentor' has the same course name and course id from what was already stored. <br>";

                // update Mentor's 'last_signed_in' field
                $query = "UPDATE users SET last_signed_in = '{$timestamp}' WHERE email = '{$obj->email}'";
                pg_query($con, $query) or die("Cannot execute query: {$query} <br>" . "Error: " . pg_last_error($con) . "<br>");
                echo "Updated 'last_signed_in' of 'Mentor'. <br>";

                echo "Starting Session. <br>";
                session_start();

                echo "Setting required session variables. <br>";
                $_SESSION["loggedIn"] = true;
                $_SESSION["name"] = $obj->name;
                $_SESSION["email"] = $obj->email;
                $_SESSION["type"] = $obj->roles[0];
                $_SESSION["pic"] = $obj->picture;
                $_SESSION["course_name"] = json_encode($course_name);
                $_SESSION["course_id"] = json_encode($course_id);

                echo "Redirecting to Instructor Home Page. <br>";
                header("location: ../scale/instructor/instr_index1.php");
            }
        }
    }
}
/** 
 * 
 * ANY OTHER USER ROLE HANDLER 
 * 
 */
else {
	echo "User role of '{$obj->roles[0]}' is not currently accepted in OR2STEM. <br>";
	exit;
}

?>