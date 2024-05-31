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

// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if ($_SESSION["type"] !== "Learner") {
    header("location: ../register_login/logout.php");
    exit;
}

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST"){

    // receive post inputs
    $assessment_name = $_POST['assessment_name'];
    $instructor_email = $_POST['instructor_email'];
    $date_time_submitted = $_POST['date_time_submitted'];
    $score = $_POST['score'];
    $max_score = $_POST['max_score'];
    $content = $_POST['content'];

    // connect to the db
    require_once "../../register_login/config.php";

    // query to insert results into 'assessments_results' table
    $query = "INSERT INTO assessments_results (assessment_name, instructor_email, student_email, student_name, course_name, course_id, score, max_score, content, date_time_submitted)
              VALUES ('{$assessment_name}', '{$instructor_email}', '{$_SESSION['email']}', '{$_SESSION['name']}', '{$_SESSION['course_name']}', '{$_SESSION['course_id']}', {$score}, {$max_score}, '{$content}', '{$date_time_submitted}')";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");

    echo "Successfully inserted assessment result.";

    pg_close($con);
}

?>