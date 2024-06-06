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
// (loggedIn, name, email, type, pic, course_name, course_id) //
session_start();

// redirect users if not logged in //
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// force logout for non-instructors or non-mentors //
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$course_names = json_decode($_SESSION['course_name']);
$course_ids = json_decode($_SESSION['course_id']);

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // extract POST data
    $idx = $_POST['number'];

    // set new session variables for instructor
    $_SESSION['selected_course_name'] = $course_names[$idx];
    $_SESSION['selected_course_id'] = $course_ids[$idx];

    // redirect to instr_index2.php
    header("Location: instr_index2.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Home Page</title>
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
            cssLink.setAttribute("href", `../assets/css/instructor/instr_index1-${window.localStorage.getItem("mode")}-mode.css`);
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
                    <h1 id="OR2STEM-HEADER">SCOOL - Student-Centered Open Online Learning</h1>
                </div>

                <div class="inner-banner">
                    <div class="banner-img"></div>
                </div>
            </nav>
        </header>

        <main>
            <div id="header-div">
                <h1>Instructor Home Page</h1>
                <hr style="border: 1px solid black;">
            </div>

            <div id="loading-div">
                LOADING...
            </div>

            <div id="class-list-div" style="display:none;">
                <h2>Inspect one of your courses.</h2>
            </div>

            <div id="static-dynamic-div" style="display:none;">
                <h2>Browse through OpenStax or IMathAS questions.</h2>
                <button class="q-btn" onclick="redirect(0)">OpenStax Questions</button>
                <button class="q-btn" onclick="redirect(1)">IMathAS Questions</button>
            </div>

            <div id='new-openstax-div' style="display:none;">
                <h2>Insert New OpenStax Question.</h2>
                <button class="q-btn" onclick="redirect(2)">New OpenStax Question</button>
            </div>

            <div id='update-openstax-div' style="display:none;">
                <h2>Update OpenStax Question By ID.</h2>
                <button class="q-btn" onclick="redirect(3)">Update OpenStax Question</button>
            </div>
        </main>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="">
                            <p>SCOOL</p>
                        </a>
                    </div>
                    <div class="navigation">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="">Home</a></li>
                            <li><a href="../navigation/about-us/about-us.php">About Us</a></li>
                            <li><a href="../navigation/faq/faq.php">FAQ</a></li>
                            <li><a href="../navigation/contact-us/contact-us.php">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="navigation">
                        <h4>External Links</h4>
                        <ul>
                            <li><a href=""> SCOOL </a></li>
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
        /* JS GLOBALS */
        // converting php array to js array
        const course_names = <?= json_encode($course_names); ?>;
        const course_ids = <?= json_encode($course_ids); ?>;

        let displayClasses = () => {
            let str = '<form id="myForm" action="" method="POST">';
            str += '<input type="number" id="number" name="number" style="display: none;" />';
            for (let i = 0; i < course_names.length; i++) {
                str += `<button type="button" class="q-btn" onclick="submitForm(${i})">${course_names[i]}<br>${course_ids[i]}</button>`;
            }
            str += '</form>';
            document.getElementById("class-list-div").insertAdjacentHTML('beforeend', str);
        }

        let submitForm = (int) => {
            // set chosen index value
            document.getElementById("number").value = int;
            // submit form
            document.getElementById("myForm").submit();
        }

        let redirect = (idx) => {
            if (idx === 0) {
                window.location.href = "./static.php";
            } else if (idx === 1) {
                window.location.href = "./dynamic.php";
            } else if (idx === 2) {
                window.location.href = "./instr_new_openstax.php";
            } else if (idx === 3) {
                window.location.href = "./instr_update_openstax.php";
            }
        }


        const initialize = () => {
            // content
            displayClasses();
            document.getElementById("class-list-div").style.display = "";
            document.getElementById("static-dynamic-div").style.display = "";
            document.getElementById("new-openstax-div").style.display = "";
            document.getElementById("update-openstax-div").style.display = "";
            document.getElementById("loading-div").style.display = "none";
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
