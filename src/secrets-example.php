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

// do not load the page directly -- only include it
if (count(get_included_files()) === 1) {
    header('HTTP/1.0 404 Not Found', true, 404);
    die();
}

const USER_DATA_DIR = "/var/www/user_data";

// Fresno State PostgreSQL Database credentials
const DB_HOST = 'localhost';
const DB_PORT = 5432;
const DB_NAME = "swa";
const DB_USER = "scool";
const DB_PASS = "supersekret";

// Attempt to connect to the PostgreSQL database
$con = pg_connect("host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS)
or die ("Could not connect to the database.\n");
