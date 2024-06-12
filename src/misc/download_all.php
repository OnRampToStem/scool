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

$log = getLogger(__FILE__);

session_start();

// user not logged in => redirect to FS Canvas //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// user not 'Instructor' => force logout //
if ($_SESSION["type"] !== "Instructor") {
    header("location: /register_login/logout.php");
    exit;
}

$courses = []; // holds course directories
if ($handle = opendir(USER_DATA_DIR)) {
    while (false !== ($course_key = readdir($handle))) {
        // exclude special directories //
        if (!str_starts_with($course_key, ".")) {
            $courses[] = $course_key;
        }
    }
}

$course_count = count($courses);
if ($course_count === 0) {
    $log->warning("no courses found");
    die("No courses found");
}

$log->info("course directory count", ["count" => $course_count]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Download All</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script type="text/javascript">
        const courses = <?= json_encode($courses) ?>;
        for (let i = 0; i < courses.length; i++) {
            let course = courses[i];
            setTimeout(() => {
                const link = document.createElement('a');
                let courseKey = encodeURIComponent(course)
                console.log("retrieving course by key [" + courseKey + "]");
                link.href = `download_course.php?key=${courseKey}`;
                // simulate a click on the link to initiate the download
                link.click();
                document.getElementById('current-file').innerText = course;
                let newCount = document.getElementById('counter').innerText - 1;
                document.getElementById('counter').innerText = String(newCount);
            }, i * 2500);
        }
    </script>
</head>
<body>
    <div>
        <h1>Downloading All Courses</h1>
        <h2>Files Remaining</h2>
        <span id="counter"><?= $course_count ?></span>
        <h2>Current File</h2>
        <span id="current-file"><?= $courses[0] ?></span>
    </div>
</body>
</html>
