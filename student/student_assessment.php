<?php
//for display purposes
//header("Content-type: text/plain");

// start the session (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id)
session_start();

// if user is not logged in then redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../index.html");
    exit;
}

// if user account type is not 'student' then force logout
if($_SESSION["type"] !== "student"){
    header("location: ../register_login/logout.php");
    exit;
}

/* GLOBALS */
$query;
$res;
$instr_email;
$curr_date;
$past_assessments = array();
$open_assessments = array();
$future_assessments = array();

// connect to the db
require_once "../register_login/config.php";

// first query - grab instructor email
$query = "SELECT instructor FROM users WHERE email = '{$_SESSION["email"]}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    $instr_email = pg_fetch_result($res, 0);
}

// setting to CA timezone
date_default_timezone_set('America/Los_Angeles');

$curr_date = date_create();
$curr_date = date_format($curr_date, "Y-m-d");
//echo $curr_date, "\n";

// second query - grab all past assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND close_date < '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($past_assessments[$row[0]])) {
            $past_assessments[$row[0]] = [];
            array_push($past_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// third query - grab all current assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date <= '{$curr_date}' AND close_date >= '{$curr_date}'
          AND course_name = '{$_SESSION['course_name']}' AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($open_assessments[$row[0]])) {
            $open_assessments[$row[0]] = [];
            array_push($open_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}


// fourth query - grab all future assessments that belong to user's course_name, course_id, section_id
$query = "SELECT * FROM assessments WHERE instructor = '{$instr_email}' AND open_date > '{$curr_date}' AND course_name = '{$_SESSION['course_name']}'
          AND course_id = '{$_SESSION['course_id']}' AND section_id = '{$_SESSION['section_id']}'";
$res = pg_query($con, $query);
if(!$res) {
    // error
} else {
    // no error
    while($row = pg_fetch_row($res)){
        if(!isset($future_assessments[$row[0]])) {
            $future_assessments[$row[0]] = [];
            array_push($future_assessments[$row[0]], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
        }
    }
}

/*
print_r($past_assessments);
echo "\n";
print_r($open_assessments);
echo "\n";
print_r($future_assessments);
echo "\n";
*/

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>OR2STEM - Assessments</title>
        <link rel="stylesheet" href="../assets/css/student/student_assessment.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body onload="displayAssessments();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["first_name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../register_login/logout.php">Logout</a>
                        </div>
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A" href="student_index.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main>
                <div id="assessments_display">
                    <div id="past_assessments"></div>
                    <div id="open_assessments"></div>
                    <div id="future_assessments"></div>
                </div>
            </main>

            <br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="" class="router-link-active"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="../index.html" class="router-link-active">Home</a></li>
                                <li><a href="" class="">About Us</a></li>
                                <li><a href="" class="">FAQ</a></li>
                                <li><a href="" class="">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a href="https://scale.fresnostate.edu/scale/" target="_blank"> CSU SCALE </a></li>
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
                        <p>© 2021-2022 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>
        
        <script type="text/javascript">
            /* GLOBALS */
            const past_assessments = <?= json_encode($past_assessments); ?>;
            const open_assessments = <?= json_encode($open_assessments); ?>;
            const future_assessments = <?= json_encode($future_assessments); ?>;


            let displayAssessments = () => {
                let str;

                // past assessments
                str = '<h1>Past Assessments</h1>';
                str += '<table class="assessments">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in past_assessments) {
                    str += '<tr class="tr_ele" onclick=""><td>' + past_assessments[key][0] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("past_assessments").innerHTML = str;

                // open assessments
                str = '<h1>Open Assessments</h1>';
                str += '<table class="assessments">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in open_assessments) {
                    str += '<tr class="tr_ele" onclick=""><td>' + open_assessments[key][0] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("open_assessments").innerHTML = str;

                // future assessments
                str = '<h1>Future Assessments</h1>';
                str += '<table class="assessments">';
                str += '<thead><tr><th scope="col">Assessment Name</th></tr></thead>';
                str += '<tbody>';
                for (const key in future_assessments) {
                    str += '<tr class="tr_ele" onclick=""><td>' + future_assessments[key][0] + '</td></tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("future_assessments").innerHTML = str;
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