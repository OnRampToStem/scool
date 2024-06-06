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
    header("location: ../register_login/logout.php");
    exit;
}

// for display purposes
//header("Content-type: text/plain");

// receiving inputs
$ch_num = (int)$_POST["chapter"];

// filepath
$json_filename = USER_DATA_DIR . "/" . $_SESSION['course_name'] . "-" . $_SESSION['course_id'] . "/openStax/" . $_SESSION['email'] . ".json";
// read the openStax.json file to text
$json = file_get_contents($json_filename);
// decode the text into a PHP assoc array
$json_openStax = json_decode($json, true);

$sections_data = [];

foreach($json_openStax as $chapter){

    if($chapter["Index"] === $ch_num && $chapter["Access"] === "True"){

        for($i = 0; $i < count($chapter["Sections"]); $i++){

            if($chapter["Sections"][$i]["Access"] === "True"){
                $sections_data[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]] = "A" . $chapter["Sections"][$i]["Name"];
            }
            else {
                $sections_data[$chapter["Index"] . "." . $chapter["Sections"][$i]["Index"]] = "N" . $chapter["Sections"][$i]["Name"];
            }

        }
    }
}

//print_r($sections_data);

// send back sections_data
$json_encoded_sections_data = json_encode($sections_data);
echo $json_encoded_sections_data;

?>
