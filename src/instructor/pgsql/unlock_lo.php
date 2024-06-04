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

require_once "../../bootstrap.php";

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // globals //
    $ch_digit = (int)$_POST["ch_digit"];
    $sec_digit = (int)$_POST["sec_digit"];
    $lo_digit = (int)$_POST["lo_digit"];
    $student_emails = json_decode($_POST["students"]);

    // 1
    // unlock the requested learning outcome for each student
    foreach ($student_emails as $student_email) {

        echo "Starting unlock process for $student_email \n";

        // filepath
        $json_filename = USER_DATA_DIR . "/" . $_SESSION['selected_course_name'] . "-" . $_SESSION['selected_course_id'] . "/openStax/" . $student_email . ".json";
        // read the openStax.json file to text
        $json = file_get_contents($json_filename);
        // decode the text into a PHP assoc array
        $openStax = json_decode($json, true);

        // UNLOCK THE CHAPTER, SECTION, AND LO
        // loop through each chapter
        foreach ($openStax as $key1 => $val1) {

            // only looking for specific chapter
            if ($val1["Index"] === $ch_digit) {

                // perform modification here for chapter
                $openStax[$key1]["Access"] = "True";
                echo "Modified chapter {$val1["Index"]} Access to True \n";

                // loop through each section in that chapter
                foreach ($val1["Sections"] as $key2 => $val2) {

                    // only looking for specific section
                    if ($val2["Index"] === $sec_digit) {

                        // peform modification here for section
                        $openStax[$key1]["Sections"][$key2]["Access"] = "True";
                        echo "Modified section {$val2["Index"]} Access to True \n";

                        // loop through each lo in that section
                        foreach ($val2["LearningOutcomes"] as $key3 => $val3) {

                            // only looking for specific lo
                            if ($val3["Index"] === $lo_digit) {

                                // perform modification here for lo
                                $openStax[$key1]["Sections"][$key2]["LearningOutcomes"][$key3]["Access"] = "True";
                                echo "Modified lo {$val3["Index"]} Access to True \n";
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        // 2
        // PROCEED TO REWRITE OPENSTAX JSON FILE
        echo "Now rewriting respective openStax json file\n";
        // rewrite user openStax json file (original data + modified data)
        $myfile = fopen(USER_DATA_DIR . "/" . $_SESSION['selected_course_name'] . "-" . $_SESSION['selected_course_id'] . "/openStax/" . $student_email . ".json", "w") or die("Unable to open file!");

        // begin writing
        fwrite($myfile, "[");

        // loop through each chapter
        $c1 = 0;
        foreach ($openStax as $chapter) {

            // comma at the end
            if ($c1 !== count($openStax) - 1) {
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
                fwrite($myfile, $string);
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
    }
}
