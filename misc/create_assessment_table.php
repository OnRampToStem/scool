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

// connect to the db
require_once "../../bootstrap.php";

// creating the 'assessments' table, if it does not exist in the PostgreSQL database
$query = "CREATE TABLE IF NOT EXISTS assessments (
    pkey SERIAL PRIMARY KEY,
    instructor TEXT NOT NULL,
    name TEXT NOT NULL,
    public TEXT NOT NULL,
    duration INT NOT NULL,
    open_date DATE NOT NULL,
    open_time TIME NOT NULL,
    close_date DATE NOT NULL,
    close_time TIME NOT NULL,
    content JSON NOT NULL,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL
)";
pg_query($con, $query) or die("Cannot execute query: {$query}.\n" . "Error: " . pg_last_error($con) . "\n");
echo "The 'assessments' table has been successfully created or was already there!\n";

echo "Closing connection to PostgreSQL database.";
pg_close($con);

?>