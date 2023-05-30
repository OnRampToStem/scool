<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../register_login/logout.php");
    exit;
}

// connect to the db
require_once "../register_login/config.php";

// php globals
$open_assessments = [];
$future_assessments = [];
$past_assessments = [];

// query to set the local time zone for the following queries
$query = "SET TIME ZONE 'America/Los_Angeles'";
$res = pg_query($con, $query) or die(pg_last_error($con));

// query all open assessments
$query = "SELECT * FROM assessments WHERE instructor = '{$_SESSION["email"]}' AND course_name = '{$_SESSION['selected_course_name']}' 
          AND course_id = '{$_SESSION['selected_course_id']}' AND (open_date < CURRENT_DATE OR (open_date = CURRENT_DATE AND open_time <= CURRENT_TIME))
          AND (close_date > CURRENT_DATE OR (close_date = CURRENT_DATE AND close_time >= CURRENT_TIME))";
$res = pg_query($con, $query) or die(pg_last_error($con));
while ($row = pg_fetch_row($res)) {
    $obj = new stdClass();
    $obj->pkey = $row[0];
    $obj->instructor = $row[1];
    $obj->name = $row[2];
    $obj->public = $row[3];
    $obj->duration = $row[4];
    $obj->open_date = $row[5];
    $obj->open_time = $row[6];
    $obj->close_date = $row[7];
    $obj->close_time = $row[8];
    $obj->content = $row[9];
    $obj->course_name = $row[10];
    $obj->course_id = $row[11];
    array_push($open_assessments, $obj);
}

// query all future assessments
$query = "SELECT * FROM assessments WHERE instructor = '{$_SESSION["email"]}' AND course_name = '{$_SESSION['selected_course_name']}' 
          AND course_id = '{$_SESSION['selected_course_id']}' AND (open_date > CURRENT_DATE OR (open_date = CURRENT_DATE AND open_time > CURRENT_TIME))
          AND (close_date > CURRENT_DATE OR (close_date = CURRENT_DATE AND close_time > CURRENT_TIME))";
$res = pg_query($con, $query) or die(pg_last_error($con));
while ($row = pg_fetch_row($res)) {
    $obj = new stdClass();
    $obj->pkey = $row[0];
    $obj->instructor = $row[1];
    $obj->name = $row[2];
    $obj->public = $row[3];
    $obj->duration = $row[4];
    $obj->open_date = $row[5];
    $obj->open_time = $row[6];
    $obj->close_date = $row[7];
    $obj->close_time = $row[8];
    $obj->content = $row[9];
    $obj->course_name = $row[10];
    $obj->course_id = $row[11];
    array_push($future_assessments, $obj);
}

// query all past assessments
$query = "SELECT * FROM assessments WHERE instructor = '{$_SESSION["email"]}' AND course_name = '{$_SESSION['selected_course_name']}' 
          AND course_id = '{$_SESSION['selected_course_id']}' AND (open_date < CURRENT_DATE OR (open_date = CURRENT_DATE AND open_time < CURRENT_TIME))
  AND (close_date < CURRENT_DATE OR (close_date = CURRENT_DATE AND close_time < CURRENT_TIME))";
$res = pg_query($con, $query) or die(pg_last_error($con));
while ($row = pg_fetch_row($res)) {
    $obj = new stdClass();
    $obj->pkey = $row[0];
    $obj->instructor = $row[1];
    $obj->name = $row[2];
    $obj->public = $row[3];
    $obj->duration = $row[4];
    $obj->open_date = $row[5];
    $obj->open_time = $row[6];
    $obj->close_date = $row[7];
    $obj->close_time = $row[8];
    $obj->content = $row[9];
    $obj->course_name = $row[10];
    $obj->course_id = $row[11];
    array_push($past_assessments, $obj);
}

pg_close($con);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Assessments View</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
            cssLink.setAttribute("href", `../assets/css/instructor/student_assessments_view-${window.localStorage.getItem("mode")}-mode.css`);
        }

        // mode
        let item = localStorage.getItem("mode");
        const cssLink = document.getElementById("css-mode");
        if (item === null) {
            window.localStorage.setItem('mode', 'OR2STEM');
            toggleCSS();
        }
        else {
            toggleCSS();
        }

        // banner
        item = localStorage.getItem("banner");
        const cssHeader = document.getElementById("css-header");
        if (item === null) {
            window.localStorage.setItem('banner', 'OR2STEM');
            toggleBanner();
        }
        else {
            toggleBanner();
        }
    </script>
</head>

<body onload="displayAssessments()">
    <div id="app">
        <header>
            <nav class="container">
                <div id="userProfile" class="dropdown">
                    <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello
                        <?= $_SESSION["name"]; ?>!
                    </button>
                    <div id="myDropdown" class="dropdown-content">
                        <a href="../navigation/settings/settings.php">Settings</a>
                        <a href="../register_login/logout.php">Logout</a>
                    </div>
                    <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                </div>

                <div class="site-logo">
                    <h1 id="OR2STEM-HEADER">
                        <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                    </h1>
                </div>

                <div class="inner-banner">
                    <div class="banner-img"></div>
                </div>
            </nav>
        </header>

        <main>
            <div id="header-div">
                <h1>Assessments</h1>
                <hr style="border: 1px solid black;">
            </div>

            <div class="container-div">
                <div class="blue_long_btn" onclick="toggleOpenAssessments()">
                    <p class="blue_long_content">Open Assessments</p>
                    <p id="openHeaderArrow" class="blue_long_arrow">&#708;</p>
                </div>
                <div id="open_assessments"></div>
            </div>

            <div class="container-div">
                <div class="blue_long_btn" onclick="toggleFutureAssessments()">
                    <p class="blue_long_content">Future Assessments</p>
                    <p id="futureHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="future_assessments" style="display:none"></div>
            </div>

            <div class="container-div">
                <div class="blue_long_btn" onclick="togglePastAssessments()">
                    <p class="blue_long_content">Past Assessments</p>
                    <p id="pastHeaderArrow" class="blue_long_arrow">&#709;</p>
                </div>
                <div id="past_assessments" style="display:none"></div>
            </div>
        </main>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="instr_index1.php">
                            <p>On-Ramp to STEM</p>
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
                            <li><a href="instr_index1.php"> CSU SCALE </a></li>
                            <li><a href="http://fresnostate.edu/" target="_blank"> CSU Fresno Homepage </a></li>
                            <li><a href="http://www.fresnostate.edu/csm/csci/" target="_blank"> Department of Computer
                                    Science </a></li>
                            <li><a href="http://www.fresnostate.edu/csm/math/" target="_blank"> Department of
                                    Mathematics </a></li>
                        </ul>
                    </div>
                    <div class="contact">
                        <h4>Contact Us</h4>
                        <p> 5241 N. Maple Ave. <br /> Fresno, CA 93740 <br /> Phone: 559-278-4240 <br /></p>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>© 2021-2023 OR2STEM Team</p>
                </div>
            </div>
        </footer>
    </div>

    <script type="text/javascript">
        // js globals
        let open_clicked = false;
        let future_clicked = false;
        let past_clicked = false;

        const displayAssessments = () => {
            // open assessments 
            const open_assessments = <?= json_encode($open_assessments); ?>;
            if (open_assessments.length > 0) {
                let str = '<table class="assessments-tbl">';
                str += '<thead><tr>';
                str += '<th class="assessments-tbl-col-1" scope="col">Name</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Mins Allowed</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                for (let i = 0; i < open_assessments.length; i++) {
                    str += '<tr>';
                    str += `<td class="assessments-tbl-col-1">${open_assessments[i]["name"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${open_assessments[i]["open_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${open_assessments[i]["open_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${open_assessments[i]["close_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${open_assessments[i]["close_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${open_assessments[i]["duration"]}</td>`;
                    str += '</tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("open_assessments").innerHTML = str;
            }

            // future assessments
            const future_assessments = <?= json_encode($future_assessments); ?>;
            if (future_assessments.length > 0) {
                let str = '<table class="assessments-tbl">';
                str += '<thead><tr>';
                str += '<th class="assessments-tbl-col-1" scope="col">Name</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Mins Allowed</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                for (let i = 0; i < future_assessments.length; i++) {
                    str += '<tr>';
                    str += `<td class="assessments-tbl-col-1">${future_assessments[i]["name"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${future_assessments[i]["open_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${future_assessments[i]["open_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${future_assessments[i]["close_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${future_assessments[i]["close_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${future_assessments[i]["duration"]}</td>`;
                    str += '</tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("future_assessments").innerHTML = str;
            }

            // past assessments
            const past_assessments = <?= json_encode($past_assessments); ?>;
            if (past_assessments.length > 0) {
                let str = '<table class="assessments-tbl">';
                str += '<thead><tr>';
                str += '<th class="assessments-tbl-col-1" scope="col">Name</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Start Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Date</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Close Time</th>';
                str += '<th class="assessments-tbl-col-rest" scope="col">Mins Allowed</th>';
                str += '</tr></thead>';
                str += '<tbody>';
                for (let i = 0; i < past_assessments.length; i++) {
                    str += '<tr>';
                    str += `<td class="assessments-tbl-col-1">${past_assessments[i]["name"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${past_assessments[i]["open_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${past_assessments[i]["open_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${past_assessments[i]["close_date"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${past_assessments[i]["close_time"]}</td>`;
                    str += `<td class="assessments-tbl-col-rest">${past_assessments[i]["duration"]}</td>`;
                    str += '</tr>';
                }
                str += '</tbody>';
                str += '</table>';
                document.getElementById("past_assessments").innerHTML = str;
            }
        }

        const toggleOpenAssessments = () => {
            if (open_clicked) {
                document.getElementById("openHeaderArrow").innerHTML = "&#708;";
                document.getElementById("open_assessments").style.display = "";
                open_clicked = false;
            }
            else {
                document.getElementById("openHeaderArrow").innerHTML = "&#709;";
                document.getElementById("open_assessments").style.display = "none";
                open_clicked = true;
            }
        }

        const toggleFutureAssessments = () => {
            if (future_clicked) {
                document.getElementById("futureHeaderArrow").innerHTML = "&#709;";
                document.getElementById("future_assessments").style.display = "none";
                future_clicked = false;
            }
            else {
                document.getElementById("futureHeaderArrow").innerHTML = "&#708;";
                document.getElementById("future_assessments").style.display = "";
                future_clicked = true;
            }
        }

        const togglePastAssessments = () => {
            if (past_clicked) {
                document.getElementById("pastHeaderArrow").innerHTML = "&#709;";
                document.getElementById("past_assessments").style.display = "none";
                past_clicked = false;
            }
            else {
                document.getElementById("pastHeaderArrow").innerHTML = "&#708;";
                document.getElementById("past_assessments").style.display = "";
                past_clicked = true;
            }
        }

        // controlling the user profile dropdown
        /* When the user clicks on the button, toggle between hiding and showing the dropdown content */
        let showDropdown = () => {
            document.getElementById("myDropdown").classList.toggle("show");
        }
        // Close the dropdown if the user clicks outside of it
        window.onclick = function (event) {
            if (!event.target.matches('.dropbtn')) {
                let dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    let openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>

</html>