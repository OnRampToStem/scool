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

// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor"){
    header("location: /register_login/logout.php");
    exit;
}

/*
    This php script will return a php associate array, the key being the [section number]
    and the value being the [section name].
*/

// for display purposes
//header("Content-type: text/plain");

// receiving $_POST input
$ch_index = (int)$_POST["chapter"]; // holds single chapter digit (ex: 1)

// globals
$sections_data = []; // $sections_data will be an assoc array holding: "section number" => "section name"

// filepath
$json_filename = "../../assets/json_data/openStax.json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

// loop through each chapter until finding selected chapter
foreach($json_openStax as $chapter){
    if($chapter["Index"] === $ch_index){
        // loop through each section and collect data
        foreach($chapter["Sections"] as $section){
            $sections_data[$chapter["Index"] . "." . $section["Index"]] = $section["Name"];
        }
        break;
    }
}

//print_r($sections_data);

// send back sections_data
echo json_encode($sections_data);

?>
