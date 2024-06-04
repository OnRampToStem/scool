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

define("SECRET_KEY", getenv("SCOOL_SECRET_KEY"));
define("VENDOR_DIR", getenv("SCOOL_VENDOR_DIR"));
define("USER_DATA_DIR", getenv("SCOOL_USER_DATA_DIR"));
define("DEMO_PASSWORD", getenv("SCOOL_DEMO_PASSWORD"));

require VENDOR_DIR . "/autoload.php";

// Attempt to connect to the PostgreSQL database
$con = pg_connect(getenv("SCOOL_DB_CONN_STRING"))
or die ("Could not connect to the database.\n");

define("DB_CONN", $con);
