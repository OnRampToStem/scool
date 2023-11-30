<?php
// start the session //
// (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id) //
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
            cssLink.setAttribute("href", `../assets/css/instructor/instr_index2-${window.localStorage.getItem("mode")}-mode.css`);
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

        <main>
            <div id="header-div">
                <h1 title="<?= $_SESSION['selected_course_id']; ?>"><?= $_SESSION['selected_course_name']; ?></h1>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToInstrAssess()">Students Overview</button>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToInstrCreate()">Create an Assessment</button>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToInstrView()">Inspect an Assessment</button>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToInstrUnlock()">Unlock Learning Outcome(s)</button>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="handleStudentView()">Student View</button>
            </div>
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
        const redirectToInstrAssess = () => {
            window.location.href = "instr_assess1.php";
        }

        const redirectToInstrCreate = () => {
            if ("<?= $_SESSION["type"]; ?>" === "Instructor") window.location.href = "instr_create1.php";
            else alert("Only Instructors have access to this functionality.");
        }

        const redirectToInstrView = () => {
            if ("<?= $_SESSION["type"]; ?>" === "Instructor") window.location.href = "instr_multi.php";
            else alert("Only Instructors have access to this functionality.");
        }

        const redirectToInstrUnlock = () => {
            if ("<?= $_SESSION["type"]; ?>" === "Instructor") window.location.href = "unlock_lo.php";
            else alert("Only Instructors have access to this functionality.");
        }

        const handleStudentView = () => {
            if ("<?= $_SESSION["type"]; ?>" === "Instructor") {
                // run student view handler //
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "./pgsql/studentViewHandler.php", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        //console.log(xhr.responseText);
                        const response = xhr.responseText.trim();
                        //console.log(response);
                        if (response === "Login Test Student") {
                            // redirect to the student home page //
                            window.location.href = "../student/student_index.php";
                        } else {
                            alert("Unknown Error.");
                        }
                    }
                }
                xhr.send();
            } else {
                alert("Only Instructors have access to this functionality.");
            }
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