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

// for display purposes
header('Content-type: text/plain');

// connect to the DB using the config file
require_once "../bootstrap.php";

// create the 'users' table if it does not already exist in the PostgreSQL database
$query = "CREATE TABLE IF NOT EXISTS users (
    pkey SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    unique_name TEXT NOT NULL UNIQUE,
    sub TEXT NOT NULL UNIQUE,
    type TEXT NOT NULL,
    pic TEXT NOT NULL,
    instructor TEXT,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL,
    iat TEXT NOT NULL,
    exp TEXT NOT NULL,
    iss TEXT NOT NULL,
    aud TEXT NOT NULL,
    created_on TIMESTAMP NOT NULL,
    last_signed_in TIMESTAMP NOT NULL
)";
$db_con = getDBConnection();
pg_query($db_con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($db_con) . ".\n");
echo "The 'users' table has been successfully created or was already there!\n";

echo "Closing connection to PostgreSQL database.";
pg_close($db_con);

/* DESCRIPTION OF 'users' TABLE */

// pkey -> adding a primary key for each row done automatically upon each insert

// name -> represents the full name of a user and is required

// email -> represents the email of a user and is required and must be unique
    // meaning no 2 users can have the same email in the db
    // also implies a student can only be enrolled in one course - section pertaining to OR2STEM

// type -> represents the account type of a user - can be 'Instructor' or 'Student'

// pic -> represents the profile picture of a user and is required.
    // example link: https://canvas.instructure.com/images/messages/avatar-50.png

// instructor -> represents the email of an instructor
    // users of type 'Instructor' will have this column empty bc an instructor can not have an instructor
    // users of type 'Student' must have corresponding instructor email present in this column

// course_name -> represents the title of the course a user is in
    // users of type 'Instructor' will contain an array like: ["Math6", "Math Random", "Math6"] as text
    // this is bc an instructor can be the instructor of multiple courses - sections
    // users of type 'Student' will only contain a single course name value

// course_id -> represents the id of the course a user is in
    // users of type 'Instructor' will contain an array like : ["12345","55912","12346"] as text, note that
    // each index should match with the index of the 'course_name' column for the instructor
    // users of type 'Student' will only contain a single course id value

// created_on -> represents the timestamp of when a user was first inserted into the PostgreSQL db

// last_signed_in -> represents the timestamp of when a user was last logged into OR2STEM webpage
