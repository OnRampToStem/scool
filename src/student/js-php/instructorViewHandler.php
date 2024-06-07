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
// (loggedIn, name, email, type, pic, course_name, course_id) //
session_start();

// redirect users if not logged in //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// force logout for non test students //
if ($_SESSION["name"] !== "Test Student" || $_SESSION["type"] !== "Learner" || (strpos($_SESSION["email"], "test_student") === false) || (strpos($_SESSION["email"], "@canvas.instructure.com") === false)) {
    header("location: /register_login/logout.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // create timestamp to be updated for instructor login //
    $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    $timestamp = $date->format('Y-m-d H:i:s');

    // connect to the db //
    require_once "../../bootstrap.php";

    $db_con = getDBConnection();

    // get the instructor's email (based on the test student) //
    $query =
        "SELECT instructor FROM users
         WHERE name = 'Test Student'
            AND email = '" . $_SESSION["email"] . "'
            AND type = 'Learner'
            AND course_name = '" . $_SESSION["course_name"] . "'
            AND course_id = '" . $_SESSION["course_id"] . "';";
    $res = pg_query($db_con, $query) or die(pg_last_error($db_con));

    if (pg_num_rows($res) === 1) {
        $row = pg_fetch_assoc($res);
        $instructor_email = $row["instructor"];
    } else {
        echo "Instructor's email not found.";
        exit;
    }

    // get the instructor's data //
    $query =
        "SELECT * FROM users
         WHERE email = '" . $instructor_email . "'
            AND type = 'Instructor'
            AND course_name LIKE '%" . $_SESSION["course_name"] . "%'
            AND course_id LIKE '%" . $_SESSION["course_id"] . "%';";
    $res = pg_query($db_con, $query) or die(pg_last_error($db_con));

    if (pg_num_rows($res) === 1) {
        // get the data //
        $row = pg_fetch_assoc($res);

        // unset all of the session variables & destroy the session for the test student //
        $_SESSION = array();
        session_destroy();

        // login the instructor //
        $query =
            "UPDATE users
                SET last_signed_in = '" . $timestamp . "'
                WHERE email = '" . $row["email"] . "';";
        pg_query($db_con, $query) or die(pg_last_error($db_con));

        // start the session for the instructor //
        session_start();

        // set the session variables & values //
        $_SESSION["loggedIn"]    = true;
        $_SESSION["name"]        = $row["name"];
        $_SESSION["email"]       = $row["email"];
        $_SESSION["type"]        = $row["type"];
        $_SESSION["pic"]         = $row["pic"];
        $_SESSION["course_name"] = $row["course_name"];
        $_SESSION["course_id"]   = $row["course_id"];

        // redirect to the instructor home page //
        echo "Login Instructor";
    } else {
        echo "Instructor's data not found.";
        exit;
    }

    // close connection to the db //
    pg_close($db_con);
}
