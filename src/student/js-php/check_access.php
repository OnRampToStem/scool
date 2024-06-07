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

// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if($_SESSION["type"] !== "Learner"){
    header("location: /register_login/logout.php");
    exit;
}

// globals
$result = false;

// receiving $_POST inputs
$ch = (int)$_POST["ch"]; // holds single chapter digit
$sec = (int)$_POST["sec"]; // holds single section digit
$lo = (int)$_POST["lo"]; // holds single lo digit

// read and decode the student's respective openStax JSON file (text => PHP assoc array)
$json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// loop through openStax to check access
foreach ($json_data as $chapter) {

    if ($chapter["Index"] === $ch && $chapter["Access"] === "True") {

        foreach ($chapter["Sections"] as $section) {

            if ($section["Index"] === $sec && $section["Access"] === "True") {

                foreach ($section["LearningOutcomes"] as $learningoutcome) {

                    if ($learningoutcome["Index"] === $lo && $learningoutcome["Access"] === "True") {
                        $result = true;
                    }
                }
            }
        }
    }
}

// send back result
echo json_encode($result);

?>
