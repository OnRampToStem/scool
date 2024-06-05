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
if($_SESSION["type"] !== "Instructor"){
    header("location: ../../register_login/logout.php");
    exit;
}

// receive POST inputs
$pkey = $_POST["pkey"];
$name = $_POST["name"];
$public = $_POST["public"];
$duration = $_POST["duration"];
$open_date = $_POST["open_date"];
$open_time = $_POST["open_time"];
$close_date = $_POST["close_date"];
$close_time = $_POST["close_time"];
$los = json_decode($_POST["los"]);
$questions = json_decode($_POST["questions"]);
$points = json_decode($_POST["points"]);
$num_of_selected_los = $_POST["num_of_selected_los"];

// create the json content string
$json_content = "[";
for($i = 0; $i < $num_of_selected_los; $i++){
    // first entries
    if($i !== $num_of_selected_los - 1){
        $json_content .= "{";
        $json_content .= "\"LearningOutcomeNumber\": \"" . $los[$i] . "\",";
        $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
        $json_content .= "\"NumberPoints\": " . $points[$i];
        $json_content .= "},";
    }
    // last entry
    else{
        $json_content .= "{";
        $json_content .= "\"LearningOutcomeNumber\": \"" . $los[$i] . "\",";
        $json_content .= "\"NumberQuestions\": " . $questions[$i] . ",";
        $json_content .= "\"NumberPoints\": " . $points[$i];
        $json_content .= "}";
    }
}
$json_content .= "]";

/*
echo $pkey, "\n";
echo $name, "\n";
echo $public, "\n";
echo $duration, "\n";
echo $open_date, "\n";
echo $open_time, "\n";
echo $close_date, "\n";
echo $close_time, "\n";
print_r($los);
print_r($questions);
print_r($points);
echo $num_of_selected_los, "\n";
echo $json_content, "\n";
*/

// connect to the db
require_once "../../bootstrap.php";

// query
$query = "UPDATE assessments SET name = '{$name}', public = '{$public}', duration = '{$duration}', open_date = '{$open_date}',
          open_time = '{$open_time}', close_date = '{$close_date}', close_time = '{$close_time}', content = '{$json_content}'
          WHERE pkey = '{$pkey}'";
$db_con = getDBConnection();
$res = pg_query($db_con, $query) or die("Cannot execute query: {$query}<br>" . "Error: " . pg_last_error($db_con) . "<br>");

echo "Successfully updated assessment.";
pg_close($db_con);
