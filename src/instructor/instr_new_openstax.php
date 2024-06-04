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
$inserted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // connect to the db //
    require_once "../bootstrap.php";

    // input data //
    $q_tags         = trim($_POST['qTags']);
    $q_title        = trim($_POST['qTitle']);
    $q_text         = trim($_POST['qText']);
    $q_pic          = (strlen(trim($_POST['qPic'])) === 0 ? '' : trim($_POST['qPic']));
    $q_num_tries    = trim($_POST['qNumTries']);
    $q_options      = trim($_POST['qOptions']);
    $q_right_answer = trim($_POST['qRightAnswer']);
    $q_is_image     = trim($_POST['qIsImage']);
    $q_difficulty   = (strlen(trim($_POST['qDifficulty'])) === 0 ? '' : trim($_POST['qDifficulty']));

    // insert question //
    $query = "INSERT into questions (title, text, pic, numtries, options, rightanswer, isimage, tags, difficulty, selected, numcurrenttries, correct, datetime_started, datetime_answered, createdon)
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, 'false', 0, null, null, null, CURRENT_TIMESTAMP)";
    $result = pg_query_params($con, $query, [$q_title, $q_text, $q_pic, $q_num_tries, $q_options, $q_right_answer, $q_is_image, $q_tags, $q_difficulty]);
    if ($result) {
        $inserted = true;
    } else {
        die(pg_last_error($con));
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New OpenStax Question</title>
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
            cssLink.setAttribute("href", `../assets/css/instructor/instr_new_openstax-${window.localStorage.getItem("mode")}-mode.css`);
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

<body>
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

        <div id='main-div'>
            <form id='newQForm' method='POST' autocomplete='off'>
                <h1>New Open Stax Question</h1>
                <div>
                    <label for='qTags'>Tags:</label>
                    <input id='qTags' name='qTags' type='text' placeholder='1.1.1' required />
                </div>
                <div>
                    <label for='qTitle'>Title:</label>
                    <input id='qTitle' name='qTitle' type='text' placeholder='1.1.1' required />
                </div>
                <div>
                    <label for='qText'>Text:</label>
                    <textarea id='qText' name='qText' cols='60' rows='12' placeholder='Which graph represents the `f(x)=x^2` function?' required></textarea>
                </div>
                <div>
                    <label for='qPic'>Picture:</label>
                    <input id='qPic' name='qPic' type='text' placeholder='math-img.jpg' />
                </div>
                <div>
                    <label for='qNumTries'>Number of Tries:</label>
                    <input id='qNumTries' name='qNumTries' type='number' placeholder='1' required />
                </div>
                <div>
                    <label for='qOptions'>Options:</label>
                    <textarea id='qOptions' name='qOptions' cols='60' rows='12' placeholder='{`(0*%oo)`,`(-oo*%0)uu(0*%oo)`,`(-oo*%oo)`,"Not decreasing"}' required></textarea>
                </div>
                <div>
                    <label for='qRightAnswer'>Right Answer:</label>
                    <textarea id='qRightAnswer' name='qRightAnswer' cols='40' rows='10' placeholder="{true,false,false,false}" required></textarea>
                </div>
                <div>
                    <label for='qIsImage'>Is Image:</label>
                    <textarea id='qIsImage' name='qIsImage' cols='40' rows='10' placeholder="{false,false,false,false}" required></textarea>
                </div>
                <div>
                    <label for='qDifficulty'>Difficulty:</label>
                    <input id='qDifficulty' name='qDifficulty' type='text' placeholder="Easy" />
                </div>
                <input type='submit' value='Insert' />
            </form>

            <div id='inserted-div' style='display:none;'>
                <h2>Successfully inserted your new OpenStax question!</h2>
                <button onclick="insertAgain();">Insert Another Question</button>
            </div>
        </div>

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
                <?= include "../snippets/footer.html" ?>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        // driver //
        let inserted = <?php echo json_encode($inserted); ?>;
        if (inserted) {
            document.getElementById('newQForm').style.display = 'none';
            document.getElementById('inserted-div').style.display = '';
        }

        const insertAgain = () => {
            document.getElementById('newQForm').style.display = '';
            document.getElementById('inserted-div').style.display = 'none';
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