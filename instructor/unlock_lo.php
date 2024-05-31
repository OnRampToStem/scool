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
// (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id) //
session_start();

// redirect users if not logged in //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// force logout for non-instructors //
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../register_login/logout.php");
    exit;
}

// globals //
$chapter = "Select a Chapter";
$section = "Select a Section";
$learningoutcome = "Select a Learning Outcome";
$students = [];

// connect to the db //
require_once "../register_login/config.php";

// get all students belonging to the instructor for the selected course //
$query =
    "SELECT name, email FROM users
    WHERE type = 'Learner'
        AND instructor = '" . $_SESSION["email"] . "'
        AND course_name='" . $_SESSION["selected_course_name"] . "'
        AND course_id='" . $_SESSION["selected_course_id"] . "'
    ORDER BY name ASC;";
$res = pg_query($con, $query) or die(pg_last_error($con));
if (pg_num_rows($res) > 0) {
    while ($row = pg_fetch_assoc($res)) {
        array_push($students, ["name" => $row["name"], "email" => $row["email"]]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $_SESSION['selected_course_name']; ?></title>
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
            cssLink.setAttribute("href", `../assets/css/instructor/unlock_lo-${window.localStorage.getItem("mode")}-mode.css`);
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
</head>

<body onload="getChapterOptions();">
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

        <main>
            <h1>Learning Outcome(s) Unlock Tool</h1>

            <br>

            <div>
                <h3>Select A Learning Outcome to Unlock</h3>

                <div id="main-select-container">
                    <div id="chapter-container">
                        <h3>Chapter</h3>
                        <select id="chapter_options" onchange="getSectionOptions();">
                            <option selected="selected" disabled><?= $chapter; ?></option>
                        </select>
                    </div>
                    <div id="section-container">
                        <h3>Section</h3>
                        <select id="section_options" onchange="getLoOptions();">
                            <option selected="selected" disabled><?= $section; ?></option>
                        </select>
                    </div>
                    <div id="lo-container">
                        <h3>Learning Outcome</h3>
                        <select id="learningoutcome_options">
                            <option selected="selected" disabled><?= $learningoutcome; ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <br>

            <div>
                <h3>Select All Students or Specific Students</h3>
                <button id="studentsButton" onclick="selectAllStudents();">Select All Students</button>
                <br><br>
                <table id="students-table">
                    <thead>
                        <tr>
                            <th id="student-name">Student Name</th>
                            <th id="student-email">Student Email</th>
                            <th id="select">Select</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) : ?>
                            <tr>
                                <td headers="student-name"><?= $student['name'] ?></td>
                                <td headers="student-email"><?= $student['email'] ?></td>
                                <td headers="select">
                                    <input type="checkbox" class="studentCheckbox" value="<?= $student['email'] ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <br><br>

            <button id="submit-btn" onclick="handleSubmit();">Unlock Learning Outcome</button>
        </main>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="instr_index1.php" class="router-link-active">
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
                <div class="footer-bottom">
                    <p>© 2021-2023 SCOOL Team</p>
                </div>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        const students = <?= json_encode($students) ?>;
        let allStudentsSelected = false;

        const readChapterDigit = () => {
            // ex: ch => 1
            let ch = document.getElementById("chapter_options").value;
            //console.log(ch);
            return ch;
        }

        const readSectionDigit = () => {
            // ex: sec => 1.2
            // we want to extract 2
            let sec = document.getElementById("section_options").value;
            let digitsArray = sec.split(".");
            let lastDigit = digitsArray[digitsArray.length - 1];
            //console.log(lastDigit);
            return lastDigit;
        }

        const readLoDigit = () => {
            // ex: lo => 1.2.3
            // we want to extract 3
            let lo = document.getElementById("learningoutcome_options").value;
            let digitsArray = lo.split(".");
            let lastDigit = digitsArray[digitsArray.length - 1];
            //console.log(lastDigit);
            return lastDigit;
        }

        // getting all chapters from openStax.json
        let getChapterOptions = () => {
            //console.log("Getting all chapter options...");
            let req = new XMLHttpRequest();
            req.open("GET", "./get/ch_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let ch_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $chapter; ?>" + '</option>';
                    for (const [key, value] of Object.entries(ch_obj)) {
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${key}"
                    }
                    document.getElementById("chapter_options").innerHTML = str;
                }
            }
            req.send();
        };

        // getting all sections from selected chapter from openStax.json
        let getSectionOptions = () => {
            //console.log("Getting all section options...");
            let req = new XMLHttpRequest();
            req.open("POST", "./get/sec_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let sec_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $section; ?>" + '</option>';
                    for (const [key, value] of Object.entries(sec_obj)) {
                        //let sec_num = key.slice(key.indexOf('.') + 1, key.length);
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${sec_num}"
                    }
                    document.getElementById("section_options").innerHTML = str;
                }
            }
            req.send("chapter=" + readChapterDigit());
        };

        // getting all los from selected section from openStax.json
        let getLoOptions = () => {
            //console.log("Getting all learning outcome options...");
            let req = new XMLHttpRequest();
            req.open("POST", "./get/lo_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let lo_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $learningoutcome; ?>" + '</option>';
                    for (const [key, value] of Object.entries(lo_obj)) {
                        //let lo_num = key.slice(key.indexOf('.', key.indexOf('.') + 1) + 1, key.length);
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${lo_num}"
                    }
                    document.getElementById("learningoutcome_options").innerHTML = str;
                }
            }
            req.send("chapter=" + readChapterDigit() + "&section=" + readSectionDigit());
        };

        const selectAllStudents = () => {
            allStudentsSelected = !allStudentsSelected;
            const studentCheckboxes = document.querySelectorAll(".studentCheckbox");
            studentCheckboxes.forEach(function(checkbox) {
                checkbox.checked = allStudentsSelected;
            });
        }

        const validateInputs = () => {
            let select = document.getElementById("chapter_options");
            let chapterTxt = select.options[select.selectedIndex].text;
            //console.log(chapterTxt);

            select = document.getElementById("section_options");
            let sectionTxt = select.options[select.selectedIndex].text;
            //console.log(sectionTxt);

            select = document.getElementById("learningoutcome_options");
            let loTxt = select.options[select.selectedIndex].text;
            //console.log(loTxt);

            if (chapterTxt === "Select a Chapter" || sectionTxt === "Select a Section" || loTxt === "Select a Learning Outcome") {
                return false;
            } else {
                return true;
            }
        }

        const handleSubmit = () => {
            if (!validateInputs()) {
                alert("Make sure you select a Chapter, Section, and Learning Outcome.");
                return;
            }

            const checkboxes = document.querySelectorAll(".studentCheckbox");
            let students = [];

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    students.push(checkbox.value);
                }
            });

            if (students.length === 0) {
                alert("You must select at least 1 student.");
                return;
            }

            let ch_digit = readChapterDigit();
            let sec_digit = readSectionDigit();
            let lo_digit = readLoDigit();

            console.log(`Unlocking learning outcome ${ch_digit}.${sec_digit}.${lo_digit}`);
            let req = new XMLHttpRequest();
            req.open("POST", "./pgsql/unlock_lo.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    console.log(req.response);
                    alert(`Learning Outcome ${ch_digit}.${sec_digit}.${lo_digit}. has been unlocked for your selected students!`);
                }
            }
            req.send("ch_digit=" + ch_digit + "&sec_digit=" + sec_digit + "&lo_digit=" + lo_digit + "&students=" + JSON.stringify(students));
        }

        // controlling the user profile dropdown
        /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
        let showDropdown = () => {
            document.getElementById("myDropdown").classList.toggle("show");
        }
        // Close the dropdown if the user clicks outside of it
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