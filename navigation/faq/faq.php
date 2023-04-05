<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Frequently Asked Questions</title>
        <link rel="stylesheet" type="text/css" href="../../assets/css/global/global.css" />
        <link id="css-header" rel="stylesheet" type="text/css" href="" />
        <link id="css-mode" rel="stylesheet" type="text/css" href="" />
        <script type="text/javascript">
            const toggleBanner = () => {
                const cssHeader = document.getElementById("css-header");
                cssHeader.setAttribute("href", `../../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
            }

            const toggleCSS = () => {
                const cssLink = document.getElementById("css-mode");
                cssLink.setAttribute("href", `./faq-${window.localStorage.getItem("mode")}-mode.css`);
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
    <body onload="initialize();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../settings/settings.php">Settings</a>
                            <a href="../../register_login/logout.php">Logout</a>
                        </div>
                        <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a id="OR2STEM-HEADER-A">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <main>
                <h1>Frequently Asked Questions</h1>
                <br>
                <p>Soon to come...</p>
            </main>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a id="footer-link"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a id="footer-link-home">Home</a></li>
                                <li><a href="../about-us/about-us.php">About Us</a></li>
                                <li><a href="./faq.php">FAQ</a></li>
                                <li><a href="../contact-us/contact-us.php">Contact Us</a></li>
                            </ul>
                        </div>
                        <div class="navigation">
                            <h4>External Links</h4>
                            <ul>
                                <li><a id="footer-link-scale"> CSU SCALE </a></li>
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
                        <p>© 2021-2023 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>
        <script type="text/javascript">
            const initialize = () => {
                // links
                if ("<?= $_SESSION['type'] ?>" === "Instructor" || "<?= $_SESSION['type'] ?>" === "Mentor") {
                document.getElementById("OR2STEM-HEADER-A").setAttribute("href", "../../instructor/instr_index1.php");
                document.getElementById("footer-link").setAttribute("href", "../../instructor/instr_index1.php");
                document.getElementById("footer-link-home").setAttribute("href", "../../instructor/instr_index1.php");
                document.getElementById("footer-link-scale").setAttribute("href", "../../instructor/instr_index1.php");
                }
                else {
                    document.getElementById("OR2STEM-HEADER-A").setAttribute("href", "../../student/student_index.php");
                    document.getElementById("footer-link").setAttribute("href", "../../student/student_index.php");
                    document.getElementById("footer-link-home").setAttribute("href", "../../student/student_index.php");
                    document.getElementById("footer-link-scale").setAttribute("href", "../../student/student_index.php");
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