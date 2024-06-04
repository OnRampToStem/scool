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

// for deleting directories and files on the Fresno State server
// first delete the files, then delete the directory
echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/questions/test_student@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/openStax/test_student@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/questions/test_student_hoUMIt2EGnmfORP5@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/openStax/test_student_hoUMIt2EGnmfORP5@canvas.instructure.com.json")
    or die("Could not remove file<br>");


echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/questions/test_student_kUjPEsJp@canvas.instructure.com.json")
    or die("Could not remove file<br>");

echo "Deleting file<br>";
unlink(USER_DATA_DIR . "/Development MATH6 Pilot-cfd70b5da3ce9018402b66c1d4ecfdc6b9d6eeef/openStax/test_student_kUjPEsJp@canvas.instructure.com.json")
    or die("Could not remove file<br>");
