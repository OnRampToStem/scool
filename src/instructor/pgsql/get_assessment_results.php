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

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = [];

    // receive POST inputs
    $assessment_name = $_POST['assessment_name'];

    // connect to the db
    require_once "../../bootstrap.php";

    // get all assessments
    $query = "SELECT * FROM assessments_results WHERE assessment_name = '{$assessment_name}' AND instructor_email = '{$_SESSION['email']}'
              AND course_name = '{$_SESSION['selected_course_name']}' AND course_id = '{$_SESSION['selected_course_id']}'";
    $db_con = getDBConnection();
    $res = pg_query($db_con, $query) or die("Cannot execute query: {$query} <br>" . pg_last_error($db_con) . "<br>");

    while($row = pg_fetch_row($res)){
        $assoc_arr = array(
            "student_name" => $row[4],
            "student_email" => $row[3],
            "score" => $row[7],
            "max_score" => $row[8],
            "content" => $row[9],
            "date_time_submitted" => $row[10]
        );
        array_push($data, $assoc_arr);
    }

    pg_close($db_con);

    echo json_encode($data);

}
