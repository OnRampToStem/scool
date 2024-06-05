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
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../../register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // receive data
    $instructor = $_POST["instructor"];
    $course_name = $_POST["course_name"];
    $course_id = $_POST["course_id"];
    $assessment = json_decode($_POST["assessment"]);

    // connect to the db
    require_once "../../bootstrap.php";

    // query
    $query = "INSERT INTO assessments(instructor, name, public, duration, open_date, open_time, close_date, close_time, content, course_name, course_id)
              VALUES('$instructor', '$assessment[2]', '$assessment[3]', '$assessment[4]', '$assessment[5]', '$assessment[6]', '$assessment[7]', '$assessment[8]', '$assessment[9]', '$course_name', '$course_id')";
    $db_con = getDBConnection();
    $res = pg_query($db_con, $query) or die(pg_last_error($db_con));

    echo "Successfully copied assessment.";
    pg_close($db_con);
}
