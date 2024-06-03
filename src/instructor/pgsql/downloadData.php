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
// loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id //
session_start();

// if user is not logged in -> redirect them back to Fresno State Canvas //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' -> force logout //
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../../register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $students = json_decode($_POST["students"]);
    $data = [];

    foreach ($students as $student) {
        $filepath = "../../user_data/" . $_SESSION['selected_course_name'] . "-" . $_SESSION['selected_course_id'] . "/questions/" . $student->email . ".json";
        $json_text = file_get_contents($filepath);
        $json_data = json_decode($json_text, true);

        $arr = [];

        // loop through each question in the file //
        for ($i = 0; $i < count($json_data); $i++) {
            if ($json_data[$i]["datetime_answered"] !== "") {
                // create PHP object containing student name & email + question data //
                $obj = new stdClass();
                $obj->name = $student->name;
                $obj->email = $student->email;
                $obj->tags = $json_data[$i]["tags"];
                $obj->text = $json_data[$i]["text"];
                $obj->numCurrentTries = $json_data[$i]["numCurrentTries"];
                $obj->numTries = $json_data[$i]["numTries"];
                $obj->correct = $json_data[$i]["correct"];
                $obj->datetime_started = $json_data[$i]["datetime_started"];
                $obj->datetime_answered = $json_data[$i]["datetime_answered"];
                /*
                $obj->pkey = $json_data[$i]["pkey"];
                $obj->title = $json_data[$i]["title"];
                $obj->pic = $json_data[$i]["pic"];
                $obj->options = $json_data[$i]["options"];
                $obj->rightAnswer = $json_data[$i]["rightAnswer"];
                $obj->isImage = $json_data[$i]["isImage"];
                $obj->difficulty = $json_data[$i]["difficulty"];
                $obj->selected = $json_data[$i]["selected"];
                $obj->createdOn = $json_data[$i]["createdOn"];
                */
                array_push($arr, $obj);
            }
        }
        array_push($data, $arr);
    }
    echo json_encode($data);
}
