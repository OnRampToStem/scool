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

require_once "../bootstrap.php";

/*
    This PHP script will do the following:
    1. Create "dynamic_questions" PGSQL table if it does not already exist.
    2. Take an input JSON file of dynamic math questions and will insert the data directly
       into the PostgreSQL database table "dynamic_questions".
*/

session_start();

if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

if ($_SESSION["type"] !== "Admin") {
    header("location: /register_login/logout.php");
    exit;
}

header('Content-type: text/plain');

/* GLOBALS */
$query = "";

// create the "dynamic_questions" table if it does not exist in the PostgreSQL database
// Note: "lo_tag" and "difficulty" are do not have constraint "not null" because some entries in
// the json file are empty
$query = "CREATE TABLE IF NOT EXISTS dynamic_questions (
    id TEXT NOT NULL,
    author TEXT NOT NULL,
    title_topic TEXT NOT NULL,
    title TEXT NOT NULL,
    title_number TEXT NOT NULL,
    problem_number TEXT NOT NULL,
    lo_tag TEXT,
    difficulty TEXT
)";
$db_con = getDBConnection();
pg_query($db_con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($db_con) . ".\n");
echo "The dynamic_questions table has been successfully created or was already there!\n";


// read and decode the JSON file (text => PHP assoc array)
$json_filename = "dynamic_questions_info.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// now insert the data into dynamic_questions by looping through each dynamic question
foreach ($json_data as $question){

    // inserting values into dynamic_questions table
    // (manually adding ' ' needed for PostgreSQL query strings / text)
    $query = "INSERT INTO dynamic_questions(id, author, title_topic, title, title_number, problem_number, lo_tag, difficulty)
              VALUES ('" . $question['id'] . "', '" . $question['author'] . "', '" . $question['title_topic'] . "', '" . $question['title'] . "', '" . $question['title_number'] . "', '" . $question['problem_number']  . "', '" . $question['LOTag']  . "', '" . $question['DifficultyLevel'] . "')";
    pg_query($db_con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($db_con) . ".\n");

}
echo "Inserted values into dynamic_questions table successfully!\n";

echo "Closing connection to PostgreSQL database.\n";
pg_close($db_con);
