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

// start the session //
// loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id //
session_start();

// if user is not logged in -> redirect them back to Fresno State Canvas //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' -> force logout //
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

require_once "../bootstrap.php";

$students = [];

if ($_SESSION["type"] === "Instructor") {
    // get all students that belong to the instructor's currently selected course //
    $query =
        "SELECT pkey, name, email FROM users
         WHERE type = 'Learner' AND instructor = '{$_SESSION["email"]}' AND
         course_name = '{$_SESSION["selected_course_name"]}' AND course_id = '{$_SESSION["selected_course_id"]}' 
         ORDER BY name ASC;";
    $res = pg_query($con, $query);
    if (!$res) {
        echo "Could not execute: " . $query . "\n Error: " . pg_last_error($con) . "\n";
        exit;
    } else {
        while ($row = pg_fetch_assoc($res)) {
            array_push(
                $students,
                [
                    "pkey" => $row["pkey"],
                    "name" => $row["name"],
                    "email" => $row["email"]
                ]
            );
        }
    }
} else if ($_SESSION["type"] === "Mentor") {
    // get the email of the instructor of the course //
    $query =
        "SELECT email FROM users
         WHERE type = 'Instructor' AND course_name LIKE '%{$_SESSION["selected_course_name"]}%' AND
         course_id LIKE '%{$_SESSION["selected_course_id"]}%'";
    $res = pg_query($con, $query);
    if (!$res) {
        echo "Could not execute: " . $query . "\n Error: " . pg_last_error($con) . "\n";
        exit;
    } else {
        // get the instructor's email
        $instructor_email = pg_fetch_result($res, 0, 0);

        // get all students that belong to the instructor's currently selected course //
        $query =
            "SELECT pkey, name, email FROM users
             WHERE type = 'Learner' AND instructor = '{$instructor_email}' AND
             course_name = '{$_SESSION["selected_course_name"]}' AND course_id = '{$_SESSION["selected_course_id"]}' 
             ORDER BY name ASC;";
        $res = pg_query($con, $query);
        if (!$res) {
            echo "Could not execute: " . $query . "\n Error: " . pg_last_error($con) . "\n";
            exit;
        } else {
            while ($row = pg_fetch_assoc($res)) {
                array_push(
                    $students,
                    [
                        "pkey" => $row["pkey"],
                        "name" => $row["name"],
                        "email" => $row["email"]
                    ]
                );
            }
        }
    }
}
pg_close($con);

// loop through the students //
foreach ($students as &$student) {
    // read & decode the student's static questions JSON file (text => PHP assoc array) //
    $json_filename = USER_DATA_DIR . "/{$_SESSION['selected_course_name']}-{$_SESSION['selected_course_id']}/questions/" . $student["email"] . ".json";
    $json = file_get_contents($json_filename);
    $json_data = json_decode($json, true);

    $data1 = []; // php assoc arr holding "lo num" => "total number of complete static questions in rel to that lo num"
    $data2 = []; // php assoc arr holding "lo num" => "total number of static questions in rel to that lo num"
    $maxNumberAssessment = 5; // assuming data from openStax.json file remains the same, each lo should have max of 5
    $complete = 0; // counter for complete static questions
    $total = 0; // counter for total static questions

    // loop through each student's respective static questions
    foreach ($json_data as $question) {
        /* summing total number of complete static questions per learning outcome */
        // setting key for php assoc arr if not set already
        if (!isset($data1[$question["tags"]])) {
            // if question not complete
            if ($question["datetime_answered"] === "") {
                $data1[$question["tags"]] = 0;
            }
            // if question complete
            else {
                $data1[$question["tags"]] = 1;
            }
        } else {
            // if question complete
            if ($question["datetime_answered"] !== "") {
                $data1[$question["tags"]]++;
            }
        }

        /* summing total number of static questions per learning outcome */
        // setting key for php assoc arr if not set already and initializing
        if (!isset($data2[$question["tags"]])) {
            $data2[$question["tags"]] = 1;
        }
        // key already set, so just increment value
        else {
            $data2[$question["tags"]]++;
        }
    }

    // loop through data2 array
    foreach ($data2 as $k => $v) {
        // using total num of static questions ($v), for given lo ($k), to determine whether or not that lo is over or equal
        // to $maxNumberAssessment
        if ($v >= $maxNumberAssessment) {
            // if the count of complete questions for that lo is greater or equal to $maxNumberAssessment then increment
            // count of $complete
            if ($data1[$k] >= $maxNumberAssessment) {
                $complete++;
            }

            // update total
            $total++;
        }
        // if the total num of static questions ($v) is less than $maxNumberAssessment
        else {
            // if count of complete questions for that lo is greater than or equal to total num of static questions
            // for that given lo, then increment $complete
            if ($data1[$k] >= $v) {
                $complete++;
            }

            // update total
            $total++;
        }
    }

    // once loop is done, save the data in $students_data, to be used in JS //
    $student["progress"] = [$complete, $total];
}
unset($student);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Students Overview</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/global/global.css" />
    <link id="css-header" rel="stylesheet" type="text/css" href="" />
    <link id="css-mode" rel="stylesheet" type="text/css" href="" />
    <script type="text/javascript">
        const toggleBanner = () => {
            const cssHeader = document.getElementById("css-header");
            cssHeader.setAttribute("href", `../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
        }

        const toggleCSS = () => {
            const cssLink = document.getElementById("css-mode");
            cssLink.setAttribute("href", `../assets/css/instructor/instr_assess1-${window.localStorage.getItem("mode")}-mode.css`);
        }

        // mode
        let item = localStorage.getItem("mode");
        const cssLink = document.getElementById("css-mode");
        if (item === null) {
            window.localStorage.setItem('mode', 'OR2STEM');
            toggleCSS();
        } else {
            toggleCSS();
        }

        // banner
        item = localStorage.getItem("banner");
        const cssHeader = document.getElementById("css-header");
        if (item === null) {
            window.localStorage.setItem('banner', 'OR2STEM');
            toggleBanner();
        } else {
            toggleBanner();
        }
    </script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>

<body onload="initialize();">
    <div id="app">
        <header>
            <nav class="container">
                <div id="userProfile" class="dropdown">
                    <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                    <div id="myDropdown" class="dropdown-content">
                        <a href="../navigation/settings/settings.php">Settings</a>
                        <a href="../register_login/logout.php">Logout</a>
                    </div>
                    <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                </div>

                <div class="site-logo">
                    <h1 id="OR2STEM-HEADER">
                        <a id="OR2STEM-HEADER-A" href="instr_index1.php">SCOOL - Student-Centered Open Online Learning</a>
                    </h1>
                </div>

                <div class="inner-banner">
                    <div class="banner-img"></div>
                </div>
            </nav>
        </header>

        <main id="main">
            <div id="header-div">
                <h1 title="<?= $_SESSION['selected_course_id']; ?>"><?= $_SESSION["selected_course_name"]; ?> <br> Students Overview</h1>
            </div>

            <div id="loading-div">
                LOADING...
            </div>

            <div id="sorting-options-div"></div>

            <div id="students-div"></div>
        </main>


        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="instr_index1.php">
                            <p>SCOOL</p>
                        </a>
                    </div>
                    <div class="navigation">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="instr_index1.php">Home</a></li>
                            <li><a href="../navigation/about-us/about-us.php">About Us</a></li>
                            <li><a href="../navigation/faq/faq.php">FAQ</a></li>
                            <li><a href="../navigation/contact-us/contact-us.php">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="navigation">
                        <h4>External Links</h4>
                        <ul>
                            <li><a href="instr_index1.php"> SCOOL </a></li>
                            <li><a href="http://fresnostate.edu/" target="_blank"> CSU Fresno Homepage </a></li>
                            <li><a href="http://www.fresnostate.edu/csm/csci/" target="_blank"> Department of Computer Science </a></li>
                            <li><a href="http://www.fresnostate.edu/csm/math/" target="_blank"> Department of Mathematics </a></li>
                        </ul>
                    </div>
                    <div class="contact">
                        <h4>Contact Us</h4>
                        <p> 5241 N. Maple Ave. <br /> Fresno, CA 93740 <br /> Phone: 559-278-4240 <br /></p>
                    </div>
                </div>
                <?= include "../snippets/footer.html" ?>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        // globals //
        let students = <?= json_encode($students); ?>;
        let studentsSortedByName = true;
        let studentsSortedByProgress = false;

        const initialize = () => {
            displaySortingOptions();
            displayStudents();
            drawAllCharts();
            document.getElementById("loading-div").style.display = "none";
        }

        const displaySortingOptions = () => {
            let str = '<button class="sorting-btn" onclick="sortStudentsByName()">Sort Students By Name</button>';
            str += '<div id="sorting-status"><b>Sorted By Name - Ascending</b></div>';
            str += '<button class="sorting-btn" onclick="sortStudentsByProgress()">Sort Students By Progress</button>';
            document.getElementById("sorting-options-div").innerHTML = str;
        }

        const displayStudents = () => {
            let str = '<table id="ss_tab">';
            str += '<thead><tr>';
            str += '<th class="th1" scope="col">Name</th>';
            str += '<th class="th2" scope="col">Email</th>';
            str += '<th class="th3" scope="col">Progress</th>';
            str += '<th class="th4" scope="col">Details</th>';
            str += '</tr></thead>';
            str += '<tbody>';
            for (let i = 0; i < students.length; i++) {
                let student = students[i];
                str += `<tr id="${student["pkey"]}">`;
                str += `<td class="td1">${student["name"]}</td>`;
                str += `<td class="td2">${student["email"]}</td>`;
                str += `<td class="td3"><div id="myChart${student["pkey"]}" class="myCharts"></div></td>`;
                str += '<td class="td4">';
                str += '<form action="instr_assess2.php" method="POST">';
                str += `<input id="student_pkey" name="student_pkey" type="number" value="${student['pkey']}" style="display:none" required>`;
                str += `<input id="student_email" name="student_email" type="text" value="${student['email']}" style="display:none" required>`;
                str += `<input id="student_name" name="student_name" type="text" value="${student['name']}" style="display:none" required>`;
                str += `<input id="student_complete" name="student_complete" type="number" value="${student['progress'][0]}" style="display:none" required>`;
                str += `<input id="student_incomplete" name="student_incomplete" type="number" value="${student['progress'][1]}" style="display:none" required>`;
                str += `<input class="open_btn_1" type="submit" value="Open">`;
                str += '</form>';
                str += '</td>';
                str += '</tr>';
            }
            str += '</tbody></table>';
            str += '<br><button id="download-btn" onclick="downloadData()">Download Data</button>';
            document.getElementById("students-div").innerHTML = str;
        }

        // using Google Pie Charts to display each student's learning outcome progress //
        const drawAllCharts = () => {
            for (let i = 0; i < students.length; i++) {
                let student = students[i];
                drawChart(student);
            }
        }
        const drawChart = (student) => {
            google.charts.load('current', {
                'packages': ['corechart']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Status', 'Learning Outcomes'],
                    ['Complete', student["progress"][0]],
                    ['Remaining', student["progress"][1] - student["progress"][0]]
                ]);

                var options = {
                    colors: ['green', 'white'],
                    pieSliceBorderColor: 'black',
                    legend: 'none'
                };

                var chart = new google.visualization.PieChart(document.getElementById(`myChart${student["pkey"]}`));

                chart.draw(data, options);
            }
        }

        const sortStudentsByName = () => {
            const compareNamesDescending = (a, b) => {
                if (a.name < b.name) {
                    return 1;
                } else if (a.name > b.name) {
                    return -1;
                }
                return 0;
            }
            const compareNamesAscending = (a, b) => {
                if (a.name > b.name) {
                    return 1;
                } else if (a.name < b.name) {
                    return -1;
                }
                return 0;
            }

            // show loading div //
            document.getElementById("loading-div").style.display = "";
            // hide data divs //
            document.getElementById("sorting-options-div").style.display = "none";
            document.getElementById("students-div").style.display = "none";

            if (studentsSortedByName) {
                // sort students from Z-A (descending) //
                students.sort(compareNamesDescending);
                document.getElementById("sorting-status").innerHTML = "<b>Sorted By Name - Descending</b>";
            } else {
                // sort students from A-Z (ascending) //
                students.sort(compareNamesAscending);
                document.getElementById("sorting-status").innerHTML = "<b>Sorted By Name - Ascending</b>";
            }

            // display sorted data //
            displayStudents();
            drawAllCharts();

            // hide loading div //
            document.getElementById("loading-div").style.display = "none";
            // show data divs //
            document.getElementById("sorting-options-div").style.display = "";
            document.getElementById("students-div").style.display = "";

            studentsSortedByName = !studentsSortedByName;
        }

        const sortStudentsByProgress = () => {
            const compareProgressDescending = (a, b) => {
                if (a.progress[0] < b.progress[0]) {
                    return 1;
                } else if (a.progress[0] > b.progress[0]) {
                    return -1;
                }
                return 0;
            }
            const compareProgressAscending = (a, b) => {
                if (a.progress[0] > b.progress[0]) {
                    return 1;
                } else if (a.progress[0] < b.progress[0]) {
                    return -1;
                }
                return 0;
            }

            // show loading div //
            document.getElementById("loading-div").style.display = "";
            // hide data divs //
            document.getElementById("sorting-options-div").style.display = "none";
            document.getElementById("students-div").style.display = "none";

            if (studentsSortedByProgress) {
                // sort students from lowest progress to highest progress //
                students.sort(compareProgressDescending);
                document.getElementById("sorting-status").innerHTML = "<b>Sorted By Progress - Descending</b>";
            } else {
                // sort students from highest progress to lowest progress //
                students.sort(compareProgressAscending);
                document.getElementById("sorting-status").innerHTML = "<b>Sorted By Progress - Ascending</b>";
            }

            // display sorted data //
            displayStudents();
            drawAllCharts();

            // hide loading div //
            document.getElementById("loading-div").style.display = "none";
            // show data divs //
            document.getElementById("sorting-options-div").style.display = "";
            document.getElementById("students-div").style.display = "";

            studentsSortedByProgress = !studentsSortedByProgress;
        }

        // TODO: fix issue with downloading data (Error 500) //
        const downloadData = () => {
            try {
                let req = new XMLHttpRequest();
                req.open("POST", "./pgsql/downloadData.php", true);
                req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req.onreadystatechange = function() {
                    if (req.readyState == 4 && req.status == 200) {
                        if (req.responseText === "[]") {
                            alert("No data to output.");
                        } else {
                            //console.log(req.responseText);
                            const data = JSON.parse(req.responseText);

                            /*
                                example of object inside a subarray of the data
                                {
                                    "name": "Test Student",
                                    "email": "test_student@canvas.instructure.com",
                                    "tags": "1.1.6",
                                    "text": "Which graph represents the `f(x)=x^2` function?",
                                    "numCurrentTries": "1",
                                    "numTries": "1",
                                    "correct": "No",
                                    "datetime_started": "2023-03-17 10:32:31",
                                    "datetime_answered": "2023-03-17 10:33:21"
                                }
                            */

                            // Setting the column headers of the CSV file //
                            let csvContent = 'Student Name, Student Email, Learning Outcome Number, Text, Student Attempts, Maximum Allowed Attempts, Correct, Date Time Started, Date Time Answered \r\n';

                            // Setting the row data of the CSV file //
                            data.forEach((student) => {
                                let row = [];

                                // loop through each question in the array
                                for (let i = 0; i < student.length; i++) {

                                    // removing comma and BR from text if applicable
                                    if (student[i]["text"].includes(',')) {
                                        // regex: match all instances of the comma globally and remove them by replacing them with an empty string
                                        student[i]["text"] = student[i]["text"].replace(/,/g, '');
                                    }
                                    if (student[i]["text"].includes('BR')) {
                                        // regex: match all instances of BR globally and remove them by replacing them with an empty string
                                        student[i]["text"] = student[i]["text"].replace(/BR/g, '');
                                    }

                                    row.push(
                                        student[i]["name"], student[i]["email"], student[i]["tags"], student[i]["text"],
                                        student[i]["numCurrentTries"], student[i]["numTries"], student[i]["correct"],
                                        student[i]["datetime_started"], student[i]["datetime_answered"]
                                    );
                                    row = row.join(',');
                                    csvContent += row + '\r\n';
                                    row = [];
                                }
                            });

                            // create a Blob object from the CSV data
                            const blob = new Blob([csvContent], {
                                type: 'text/csv'
                            });

                            // generate a URL for the Blob object
                            const url = URL.createObjectURL(blob);

                            // create a link element
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `Students-OpenStax-Data.csv`;

                            // simulate a click on the link to initiate the download
                            link.click();

                            // clean up by revoking the generated URL
                            URL.revokeObjectURL(url);
                        }
                    }
                };
                req.send(`students=${JSON.stringify(students)}`);
            } catch (err) {
                console.log(err);
            }
        }

        /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
        let showDropdown = () => {
            document.getElementById("myDropdown").classList.toggle("show");
        }
        // Close the dropdown if the user clicks outside of it //
        window.onclick = function(event) {
            if (!event.target.matches('.dropbtn')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>

</html>