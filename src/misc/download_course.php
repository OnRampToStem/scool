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

ini_set('memory_limit', '128M');

require_once "../bootstrap.php";

$log = getLogger(__FILE__);

session_start();

if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

if ($_SESSION["type"] !== "Instructor") {
    header("location: /register_login/logout.php");
    exit;
}

if (!isset($_GET["key"])) {
    die("key parameter is required");
}

$course_key = $_GET["key"];
$base_dir = USER_DATA_DIR . "/$course_key/questions";
$log->info("course key", ["course-key" => $course_key, "base_dir" => $base_dir]);

$dir_handle = opendir($base_dir);
if (!$dir_handle) {
    die("failed to open directory: {$base_dir}");
}

$csv_filename = "SCOOL-Data-{$course_key}.csv";
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="' . $csv_filename . '";');

$csv_header_fields = ['Course', 'Student Email', 'Learning Outcome Number', 'Text', 'Student Attempts', 'Maximum Allowed Attempts', 'Correct', 'Date Time Started', 'Date Time Answered'];

$f = fopen('php://output', 'w');
fputcsv($f, $csv_header_fields);

while (false !== ($filename = readdir($dir_handle))) {
    if (!str_ends_with($filename, ".json")) {
        continue;
    }
    $filepath = "$base_dir/$filename";
    $json_text = file_get_contents($filepath);
    $json_data = json_decode($json_text, true);
    foreach ($json_data as $item) {
        if ($item["datetime_answered"] === "") {
            continue;
        }
        $row = [
            'course'            => $course_key,
            'email'             => substr($filename, 0, -5),
            'tags'              => $item["tags"],
            'text'              => $item["text"],
            'numCurrentTries'   => $item["numCurrentTries"],
            'numTries'          => $item["numTries"],
            'correct'           => $item["correct"],
            'datetime_started'  => $item["datetime_started"],
            'datetime_answered' => $item["datetime_answered"]
        ];
        fputcsv($f, $row);
    }
}
