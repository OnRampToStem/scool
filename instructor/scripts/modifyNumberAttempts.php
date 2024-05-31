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

// force logout if instructor has not selected a course //
if (!isset($_SESSION["selected_course_name"]) || !isset($_SESSION["selected_course_id"])) {
    header("location: ../../register_login/logout.php");
    exit;
}

// handle GET request //
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // get data //
    $number_input = $_GET["n"];
    $new_number_of_attempts = (int)$number_input;

    // data validation //
    // number of attempts can not be 0 or less
    // (int) will set the value to 0 if the number input was not numeric
    if ($new_number_of_attempts <= 0) {
        echo "New number of attempts is not valid. Try again.";
        exit;
    }

    // get all student emails in the instructor's currently selected course //
    require_once "../../register_login/config.php";
    $student_emails = [];
    $query = "SELECT email FROM users 
              WHERE instructor='{$_SESSION["email"]}' AND course_name='{$_SESSION["selected_course_name"]}'
              AND course_id='{$_SESSION["selected_course_id"]}'";
    $res = pg_query($con, $query) or die(pg_last_error($con));
    while ($row = pg_fetch_assoc($res)) {
        array_push($student_emails, $row["email"]);
    }

    // rewrite each student's static questions JSON file w/ the new number of attempts //
    foreach ($student_emails as $student_email) {

        echo "Starting update & rewrite process for " . $student_email . "\n";

        // create filepath //
        $json_filename = "../../user_data/" . $_SESSION['selected_course_name'] . "-" . $_SESSION['selected_course_id'] . "/questions/" . $student_email . ".json";
        // read the file to text //
        $json = file_get_contents($json_filename);
        // decode the text into a PHP assoc array //
        $json_data = json_decode($json, true);

        /* UPDATING USER QUESTIONS JSON FILE */
        foreach ($json_data as $key => $value) {
            // only update the questions that have not been answered yet //
            if ($value["numCurrentTries"] === "0") {
                // updating numTries //
                $json_data[$key]["numTries"] = strval($new_number_of_attempts);
            }
        }

        /* REWRITING USER QUESTIONS JSON FILE */
        $myfile = fopen("../../user_data/" . $_SESSION['selected_course_name'] . "-" . $_SESSION['selected_course_id'] . "/questions/" . $student_email . ".json", "w") or die("Unable to open file!");

        fwrite($myfile, "[\n");

        // loop to write to file
        $totalQuestions = count($json_data);
        $counter = 1;
        foreach ($json_data as $question) {

            // get the total number of options in the question
            $options_length = count($question["options"]);

            if ($counter == $totalQuestions) {
                // no comma, because it is the last math question
                $db_string = "{\n\"pkey\":" . $question["pkey"] . ", \n\"title\":\"" . $question["title"] . "\", \n\"text\":\"" . $question["text"] . "\", \n\"pic\":\"" . $question["pic"] . "\", \n\"numTries\":\"" . $question["numTries"] . "\", \n\"options\": [";

                // insert each option into $db_string
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        $db_string .= "\"" . $question["options"][$i] . "\"], ";
                    } else {
                        $db_string .= "\"" . $question["options"][$i] . "\",";
                    }
                }
                // insert each rightAnswer into $db_string
                $db_string .= "\n\"rightAnswer\": [";
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        if ($question["rightAnswer"][$i] == 1) {
                            $db_string .= "true], ";
                        } else {
                            $db_string .= "false], ";
                        }
                    } else {
                        if ($question["rightAnswer"][$i] == 1) {
                            $db_string .= "true,";
                        } else {
                            $db_string .= "false,";
                        }
                    }
                }
                // insert each isImage into $db_string
                $db_string .= "\n\"isImage\": [";
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        if ($question["isImage"][$i] == 1) {
                            $db_string .= "true], ";
                        } else {
                            $db_string .= "false], ";
                        }
                    } else {
                        if ($question["isImage"][$i] == 1) {
                            $db_string .= "true,";
                        } else {
                            $db_string .= "false,";
                        }
                    }
                }

                $db_string .=  "\n\"tags\":\"" . $question["tags"] . "\", \n\"difficulty\":\"" . $question["difficulty"] . "\", \n\"selected\":\"" . $question["selected"] . "\", \n\"numCurrentTries\":\"" . $question["numCurrentTries"] . "\", \n\"correct\":\"" . $question["correct"] . "\", \n\"datetime_started\":\"" . $question["datetime_started"] . "\", \n\"datetime_answered\":\"" . $question["datetime_answered"] . "\", \n\"createdOn\":\"" . $question["createdOn"] . "\"\n}\n";

                fwrite($myfile, $db_string);
            } else {
                // normal write
                $db_string = "{\n\"pkey\":" . $question["pkey"] . ", \n\"title\":\"" . $question["title"] . "\", \n\"text\":\"" . $question["text"] . "\", \n\"pic\":\"" . $question["pic"] . "\", \n\"numTries\":\"" . $question["numTries"] . "\", \n\"options\": [";

                // insert each option into $db_string
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        $db_string .= "\"" . $question["options"][$i] . "\"], ";
                    } else {
                        $db_string .= "\"" . $question["options"][$i] . "\",";
                    }
                }
                // insert each rightAnswer into $db_string
                $db_string .= "\n\"rightAnswer\": [";
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        if ($question["rightAnswer"][$i] == 1) {
                            $db_string .= "true], ";
                        } else {
                            $db_string .= "false], ";
                        }
                    } else {
                        if ($question["rightAnswer"][$i] == 1) {
                            $db_string .= "true,";
                        } else {
                            $db_string .= "false,";
                        }
                    }
                }
                // insert each isImage into $db_string
                $db_string .= "\n\"isImage\": [";
                for ($i = 0; $i < $options_length; $i++) {
                    if ($i == $options_length - 1) {
                        if ($question["isImage"][$i] == 1) {
                            $db_string .= "true], ";
                        } else {
                            $db_string .= "false], ";
                        }
                    } else {
                        if ($question["isImage"][$i] == 1) {
                            $db_string .= "true,";
                        } else {
                            $db_string .= "false,";
                        }
                    }
                }

                $db_string .=  "\n\"tags\":\"" . $question["tags"] . "\", \n\"difficulty\":\"" . $question["difficulty"] . "\", \n\"selected\":\"" . $question["selected"] . "\", \n\"numCurrentTries\":\"" . $question["numCurrentTries"] . "\", \n\"correct\":\"" . $question["correct"] . "\", \n\"datetime_started\":\"" . $question["datetime_started"] . "\", \n\"datetime_answered\":\"" . $question["datetime_answered"] . "\", \n\"createdOn\":\"" . $question["createdOn"] . "\"\n},\n";

                fwrite($myfile, $db_string);
            }

            $counter++;
        }
        fwrite($myfile, "]\n");
        fclose($myfile);

        echo "Update & rewrite process for " . $student_email . " has been successfully completed.\n\n";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Modify Number of Attempts</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body>
    <div style="margin: 0 auto; text-align: center;">
        <a href="../instr_index1.php">Click here to go Home</a>
    </div>
</body>

</html>