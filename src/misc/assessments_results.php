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

// for display purposes
header('Content-type: text/plain');

$query = "CREATE TABLE IF NOT EXISTS assessments_results (
    pkey SERIAL PRIMARY KEY,
    assessment_name TEXT NOT NULL,
    instructor_email TEXT NOT NULL,
    student_email TEXT NOT NULL,
    student_name TEXT NOT NULL,
    course_name TEXT NOT NULL,
    course_id TEXT NOT NULL,
    score DECIMAL NOT NULL,
    max_score DECIMAL NOT NULL,
    content JSON NOT NULL,
    date_time_submitted TIMESTAMP NOT NULL
)";

$db_con = getDBConnection();
pg_query($db_con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($db_con) . "\n");
echo "The 'assessments_results' table has been successfully created or was already there!\n";

echo "Disconnecting from PostgreSQL database.\n";
pg_close($db_con);
