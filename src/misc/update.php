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

session_start();

if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

if ($_SESSION["type"] !== "Admin" && $_SESSION["type"] !== "Instructor") {
    header("location: /register_login/logout.php");
    exit;
}

// for display purposes
header('Content-type: text/plain');

// filepath to json file
$filepath = "../assets/json_data/new_questions.json";
// read the json file to text
$json_text = file_get_contents($filepath);
// decode the text into a PHP assoc array
$static_qs = json_decode($json_text, true);

$timestamp = date("Y-m-d H:i:s");

// now write the updated data to another json file
$c = 1;
$filepath = "../assets/json_data/new_qs.json";
$q_file = fopen($filepath, "w") or die("Unable to open file!");
fwrite($q_file, "[\n");

// loop through all of the static questions
for ($i = 0; $i < count($static_qs); $i++) {
    $str = "\t{\n";
    $str .= "\t\t\"pkey\": {$c},\n";
    $str .= "\t\t\"title\": \"{$static_qs[$i]["title"]}\",\n";
    $str .= "\t\t\"text\": \"{$static_qs[$i]["text"]}\",\n";
    $str .= "\t\t\"pic\": \"{$static_qs[$i]["pic"]}\",\n";
    $str .= "\t\t\"numTries\": \"{$static_qs[$i]["numTries"]}\",\n";
    $str .= "\t\t\"options\": [";
    // options
    for($j = 0; $j < count($static_qs[$i]["options"]); $j++) {
        if ($j === count($static_qs[$i]["options"]) - 1) {
            $str .= "\"" . $static_qs[$i]["options"][$j] . "\"],\n";
        }
        else {
            $str .= "\"" . $static_qs[$i]["options"][$j] . "\", ";
        }
    }
    // rightAnswer
    $str .= "\t\t\"rightAnswer\": [";
    for ($j = 0; $j < count($static_qs[$i]["rightAnswer"]); $j++) {
        if ($j === count($static_qs[$i]["rightAnswer"]) - 1) {
            if ($static_qs[$i]["rightAnswer"][$j] == 1) {
                $str .= "true],\n";
            }
            else {
                $str .= "false],\n";
            }
        }
        else{
            if ($static_qs[$i]["rightAnswer"][$j] == 1) {
                $str .= "true, ";
            }
            else {
                $str .= "false, ";
            }
        }
    }
    // isImage
    $str .= "\t\t\"isImage\": [";
    for ($j = 0; $j < count($static_qs[$i]["isImage"]); $j++) {
        if ($j === count($static_qs[$i]["isImage"]) - 1) {
            if ($static_qs[$i]["isImage"][$j] == 1) {
                $str .= "true],\n";
            }
            else {
                $str .= "false],\n";
            }
        }
        else{
            if ($static_qs[$i]["isImage"][$j] == 1) {
                $str .= "true, ";
            }
            else {
                $str .= "false, ";
            }
        }
    }
    $str .= "\t\t\"tags\": \"{$static_qs[$i]["tags"]}\",\n";
    $str .= "\t\t\"difficulty\": \"{$static_qs[$i]["difficulty"]}\",\n";
    $str .= "\t\t\"selected\": \"false\",\n";
    $str .= "\t\t\"numCurrentTries\": \"0\",\n";
    $str .= "\t\t\"correct\": \"\",\n";
    $str .= "\t\t\"datetime_started\": \"\",\n";
    $str .= "\t\t\"datetime_answered\": \"\",\n";
    $str .= "\t\t\"createdOn\": \"{$timestamp}\"\n";
    if ($i !== count($static_qs) - 1) $str .= "\t},\n";
    else $str .= "\t}\n";
    fwrite($q_file, $str);
    $c++;
}

fwrite($q_file, "]");
fclose($q_file);
chmod("../assets/json_data/new_qs.json", 0777) or die("Could not modify perms.\n");


?>
