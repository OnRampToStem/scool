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

require_once "../bootstrap.php";

// start the session //
// (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id) //
session_start();

// redirect users if not logged in //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// force logout for non-instructors //
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // create timestamp to be inserted / updated for test students for account registration and login //
    $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $timestamp = $date->format('Y-m-d H:i:s');

    // connect to the db //
    require_once "../../bootstrap.php";

    $db_con = getDBConnection();

    // check if the instructor already owns a test student for the selected course //
    $query =
        "SELECT * FROM users
         WHERE name = 'Test Student'
            AND type = 'Learner'
            AND instructor = '" . $_SESSION["email"] . "'
            AND course_name='" . $_SESSION["selected_course_name"] . "'
            AND course_id='" . $_SESSION["selected_course_id"] . "';";
    $res = pg_query($db_con, $query) or die(pg_last_error($db_con));

    // test student already exists //
    if (pg_num_rows($res) === 1) {
        // get the data //
        $row = pg_fetch_assoc($res);

        // unset all of the session variables & destroy the session for the instructor //
        $_SESSION = array();
        session_destroy();

        // login the test student //
        $query =
            "UPDATE users
             SET last_signed_in = '" . $timestamp . "'
             WHERE email = '" . $row["email"] . "'";
        pg_query($db_con, $query) or die(pg_last_error($db_con));

        // start the session for the test student //
        session_start();

        // set the session variables & values //
        $_SESSION["loggedIn"]    = true;
        $_SESSION["name"]        = $row["name"];
        $_SESSION["email"]       = $row["email"];
        $_SESSION["type"]        = $row["type"];
        $_SESSION["pic"]         = $row["pic"];
        $_SESSION["course_name"] = $row["course_name"];
        $_SESSION["course_id"]   = $row["course_id"];

        // redirect to the student home page //
        echo "Login Test Student";
    }
    // test student does not exist //
    else {
        // create a unique email //
        $email_prefix = "test_student_";
        $unique_identifier = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $email_postfix = "@canvas.instructure.com";
        $unique_email = $email_prefix . $unique_identifier . $email_postfix;
        $unique = false;

        // assume the email is not unique until proven unique //
        while (!$unique) {
            // check if the email is unique //
            $query = "SELECT * FROM users WHERE email='" . $unique_email . "';";
            $res = pg_query($db_con, $query) or die(pg_last_error($db_con));

            if (pg_num_rows($res) === 1) {
                // email already exists - regenerate the unique email //
                $unique_identifier = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                $unique_email = $email_prefix . $unique_identifier . $email_postfix;
            } else {
                // email does not exist //
                $unique = true;
            }
        }

        // register the test student //
        $query =
            "INSERT INTO users (name, email, unique_name, sub, type, pic, instructor, course_name, course_id, iat, exp, iss, aud, created_on, last_signed_in)
             VALUES ('Test Student', '" . $unique_email . "', '" . $unique_email . "', 'N/A', 'Learner', 'https://canvas.instructure.com/images/messages/avatar-50.png',
             '" . $_SESSION["email"] . "', '" . $_SESSION["selected_course_name"] . "', '" . $_SESSION["selected_course_id"] . "', 'N/A', 'N/A', 'N/A', 'N/A',
             '" . $timestamp . "', '" . $timestamp . "')
             RETURNING *;";
        $res = pg_query($db_con, $query) or die(pg_last_error($db_con));
        $test_student = pg_fetch_assoc($res);

        // get all static questions from 'questions' table //
        $query = "SELECT * FROM questions";
        $res = pg_query($db_con, $query) or die(pg_last_error($db_con));
        $rows = pg_num_rows($res);

        // begin writing the test student's static questions json file //
        $filepath = USER_DATA_DIR . "/" . $test_student["course_name"] . "-" . $test_student["course_id"] . "/questions/" . $test_student["email"] . ".json";
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
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        $db_string .= "\"$options_arr[$i]\"], ";
                    } else {
                        $db_string .= "\"$options_arr[$i]\",";
                    }
                }

                $db_string .= "\n\"rightAnswer\": $row[6], \n\"isImage\": $row[7], \n\"tags\": \"$row[8]\", \n\"difficulty\": \"$row[9]\", \n\"selected\": \"$row[10]\", \n\"numCurrentTries\": \"$row[11]\", \n\"correct\": \"$row[12]\", \n\"datetime_started\": \"$row[13]\", \n\"datetime_answered\": \"$row[14]\", \n\"createdOn\": \"$row[15]\"\n}\n";

                // replacing the commas back in the options array
                $db_string = str_replace('*%', ',', $db_string);

                fwrite($questions_file, $db_string);
            } else {
                // normal write
                $db_string = "{\n\"pkey\": $row[0], \n\"title\": \"$row[1]\", \n\"text\": \"$row[2]\", \n\"pic\": \"$row[3]\", \n\"numTries\": \"$row[4]\", \n\"options\": [";

                // insert each option into $db_string
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        $db_string .= "\"$options_arr[$i]\"], ";
                    } else {
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
        chmod(USER_DATA_DIR . "/" . $test_student["course_name"] . "-" . $test_student["course_id"] . "/questions/" . $test_student["email"] . ".json", 0777) or die("Could not modify questions json perms.");


        // begin writing the test student's openStax json file //
        $json_filename = "new_openStax.json";
        // read the openStax.json file to text
        $json = file_get_contents($json_filename);
        // decode the text into a PHP assoc array
        $json_data = json_decode($json, true);

        $filepath = USER_DATA_DIR . "/" . $test_student["course_name"] . "-" . $test_student["course_id"] . "/openStax/" . $test_student["email"] . ".json";
        $openStax_file = fopen($filepath, "w") or die("Unable to open file!");

        // begin writing
        fwrite($openStax_file, "[");

        // loop through each chapter
        $c1 = 0;
        foreach ($json_data as $chapter) {
            // comma at the end
            if ($c1 !== count($json_data) - 1) {
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
                for ($i = 0; $i < count($chapter["Sections"]); $i++) {
                    // comma at the end
                    if ($i !== count($chapter["Sections"]) - 1) {
                        $string .= "\n\t\t\t{";
                        $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                        $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                        $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                        // loop through inner inner LearningOutcomes array
                        for ($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++) {
                            // comma at the end
                            if ($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1) {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t},"; //learning outcome comma here
                            }
                            // no comma
                            else {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t}"; //no learning outcome comma here
                            }
                        }

                        $string .= "\n\t\t\t\t],";
                        $string .= "\n\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t]";
                        $string .= "\n\t\t\t},"; //section comma here

                    }
                    // no comma
                    else {
                        $string .= "\n\t\t\t{";
                        $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                        $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                        $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                        // loop through inner inner LearningOutcomes array
                        for ($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++) {
                            // comma at the end
                            if ($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1) {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t},"; //learning outcome comma here
                            }
                            // no comma
                            else {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t}"; //no learning outcome comma here
                            }
                        }

                        $string .= "\n\t\t\t\t],";
                        $string .= "\n\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t]";
                        $string .= "\n\t\t\t}"; //no section comma here
                    }
                }

                $string .= "\n\t\t],";
                $string .= "\n\t\t\"score\": [";
                $string .= "\n\t\t\t0,";
                $string .= "\n\t\t\t0,";
                $string .= "\n\t\t\t0";
                $string .= "\n\t\t]";
                $string .= "\n\t},"; //chapter comma here

                // writing
                fwrite($openStax_file, $string);
            }
            // no comma
            else {
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
                for ($i = 0; $i < count($chapter["Sections"]); $i++) {
                    // comma at the end
                    if ($i !== count($chapter["Sections"]) - 1) {
                        $string .= "\n\t\t\t{";
                        $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                        $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                        $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                        // loop through inner inner LearningOutcomes array
                        for ($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++) {
                            // comma at the end
                            if ($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1) {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t},"; //learning outcome comma here
                            }
                            // no comma
                            else {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t}"; //no learning outcome comma here
                            }
                        }

                        $string .= "\n\t\t\t\t],";
                        $string .= "\n\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t]";
                        $string .= "\n\t\t\t},"; //section comma here

                    }
                    // no comma
                    else {
                        $string .= "\n\t\t\t{";
                        $string .= "\n\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["Index"] . ",";
                        $string .= "\n\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["Name"] . "\",";
                        $string .= "\n\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["Access"] . "\",";

                        $string .= "\n\t\t\t\t\"LearningOutcomes\": [";
                        // loop through inner inner LearningOutcomes array
                        for ($j = 0; $j < count($chapter["Sections"][$i]["LearningOutcomes"]); $j++) {
                            // comma at the end
                            if ($j !== count($chapter["Sections"][$i]["LearningOutcomes"]) - 1) {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t},"; //learning outcome comma here
                            }
                            // no comma
                            else {
                                $string .= "\n\t\t\t\t\t{";
                                $string .= "\n\t\t\t\t\t\t\"Index\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Index"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Name\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Name"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"Access\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Access"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"MaxNumberAssessment\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["MaxNumberAssessment"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"Document\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Document"] . "\",";
                                $string .= "\n\t\t\t\t\t\t\"PageStart\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageStart"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"PageEnd\": " . $chapter["Sections"][$i]["LearningOutcomes"][$j]["PageEnd"] . ",";
                                $string .= "\n\t\t\t\t\t\t\"url\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["url"] . "\",";
                                if (gettype($chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"]) === "string") {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": \"" . $chapter["Sections"][$i]["LearningOutcomes"][$j]["Video"] . "\",";
                                } else {
                                    $string .= "\n\t\t\t\t\t\t\"Video\": [],";
                                }
                                $string .= "\n\t\t\t\t\t\t\"score\": [";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0,";
                                $string .= "\n\t\t\t\t\t\t\t0";
                                $string .= "\n\t\t\t\t\t\t]";
                                $string .= "\n\t\t\t\t\t}"; //no learning outcome comma here
                            }
                        }

                        $string .= "\n\t\t\t\t],";
                        $string .= "\n\t\t\t\t\"score\": [";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0,";
                        $string .= "\n\t\t\t\t\t0";
                        $string .= "\n\t\t\t\t]";
                        $string .= "\n\t\t\t}"; //no section comma here
                    }
                }

                $string .= "\n\t\t],";
                $string .= "\n\t\t\"score\": [";
                $string .= "\n\t\t\t0,";
                $string .= "\n\t\t\t0,";
                $string .= "\n\t\t\t0";
                $string .= "\n\t\t]";
                $string .= "\n\t}"; //no chapter comma here

                // writing
                fwrite($openStax_file, $string);
            }

            // updating counter
            $c1++;
        }

        // finalizing writing
        fwrite($openStax_file, "\n]");
        fclose($openStax_file);
        chmod(USER_DATA_DIR . "/" . $test_student["course_name"] . "-" . $test_student["course_id"] . "/openStax/" . $test_student["email"] . ".json", 0777) or die("Could not modify openStax json perms.");

        // unset all of the session variables & destroy the session for the instructor //
        $_SESSION = array();
        session_destroy();

        // start the session for the test student //
        session_start();

        // set the session variables & values //
        $_SESSION["loggedIn"]    = true;
        $_SESSION["name"]        = $test_student["name"];
        $_SESSION["email"]       = $test_student["email"];
        $_SESSION["type"]        = $test_student["type"];
        $_SESSION["pic"]         = $test_student["pic"];
        $_SESSION["course_name"] = $test_student["course_name"];
        $_SESSION["course_id"]   = $test_student["course_id"];

        // redirect to the student home page //
        echo "Login Test Student";
    }

    // close connection to the db //
    pg_close($db_con);
}
