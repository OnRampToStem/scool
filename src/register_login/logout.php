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

session_start();

if ($_SESSION["course_name"] === DEMO_COURSE_TITLE) {
    $logout_url = "/login.php";
} elseif (isset($_SESSION["logout_url"])) {
    $logout_url = $_SESSION["logout_url"];
} else {
    $logout_url = "https://fresnostate.instructure.com";
}

// unset all session variables
$_SESSION = array();

// destroy the session.
session_destroy();

header("location: {$logout_url}");
