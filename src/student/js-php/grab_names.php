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

// globals
$names = [];

// receiving $_POST inputs
$ch = (int)$_POST["ch"]; // holds single chapter digit
$sec = (int)$_POST["sec"]; // holds single section digit
$lo = (int)$_POST["lo"]; // holds single lo digit

//
$json_filename = "../../assets/json_data/openStax.json";
$json = file_get_contents($json_filename);
$json_data = json_decode($json, true);

// loop through openStax to check access
foreach ($json_data as $chapter) {

    if ($chapter["Index"] === $ch) {

        array_push($names, $chapter["Index"] . ". " . $chapter["Name"]);

        foreach ($chapter["Sections"] as $section) {

            if ($section["Index"] === $sec) {

                array_push($names, $chapter["Index"] . "." . $section["Index"] . ". " . $section["Name"]);

                foreach ($section["LearningOutcomes"] as $learningoutcome) {

                    if ($learningoutcome["Index"] === $lo) {
                        array_push($names, $chapter["Index"] . "." . $section["Index"] . "." . $learningoutcome["Index"] . ". " . $learningoutcome["Name"]);
                    }
                }
            }
        }
    }
}

// send back result
echo json_encode($names);

?>