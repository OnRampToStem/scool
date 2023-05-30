<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Settings</title>
        <link rel="stylesheet" href="../../assets/css/global/global.css" />
        <link id="css-header" rel="stylesheet" type="text/css" href="" />
        <link id="css-mode" rel="stylesheet" type="text/css" href="" />
        <script type="text/javascript">
            // mode
            let item = localStorage.getItem("mode");
            const cssLink = document.getElementById("css-mode");
            if (item === null) {
                window.localStorage.setItem('mode', 'OR2STEM');
                // toggle css
                const cssLink = document.getElementById("css-mode");
                cssLink.setAttribute("href", `./settings-${window.localStorage.getItem("mode")}-mode.css`);
            }
            else {
                // toggle css
                const cssLink = document.getElementById("css-mode");
                cssLink.setAttribute("href", `./settings-${window.localStorage.getItem("mode")}-mode.css`);
            }

            // banner
            item = localStorage.getItem("banner");
            const cssHeader = document.getElementById("css-header");
            if (item === null) {
                window.localStorage.setItem('banner', 'OR2STEM');
                // toggle banner
                const cssHeader = document.getElementById("css-header");
                cssHeader.setAttribute("href", `../../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
            }
            else {
                // toggle banner
                const cssHeader = document.getElementById("css-header");
                cssHeader.setAttribute("href", `../../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
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
                            <a href="">Settings</a>
                            <a href="../../register_login/logout.php">Logout</a>
                        </div>
                        <img id="user-picture" src="<?= $_SESSION['pic']; ?>" alt="user-picture">
                    </div>

                    <div class="site-logo">
                        <h1 id="OR2STEM-HEADER">
                            <a href="../../instructor/instr_index1.php" id="OR2STEM-HEADER-A">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <main>
                <h1>Settings</h1>
                <div class="selection-div">
                    <h2>Modes</h2>
                    <button onclick="toggleCSSMode('OR2STEM');">OR2STEM</button>
                    <button onclick="toggleCSSMode('dark');">Dark</button>
                </div>
                <div class="selection-div">
                    <h2>Banners</h2>
                    <button onclick="toggleBannerMode('OR2STEM');">OR2STEM</button>
                    <button onclick="toggleBannerMode('math');">Mathematics</button>
                    <button onclick="toggleBannerMode('biology');">Biology</button>
                    <button onclick="toggleBannerMode('chemistry-biochemistry');">Chemistry / Biochemistry</button>
                    <button onclick="toggleBannerMode('cs');">Computer Science</button>
                    <button onclick="toggleBannerMode('earth-environmental');">Earth & Environmental Science</button>
                    <button onclick="toggleBannerMode('physics');">Physics</button>
                    <button onclick="toggleBannerMode('psychology');">Psychology</button>
                </div>
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
                                <li><a href="../../instructor/instr_index1.php" id="footer-link-home">Home</a></li>
                                <li><a href="../about-us/about-us.php">About Us</a></li>
                                <li><a href="../faq/faq.php">FAQ</a></li>
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

            const toggleCSSMode = (mode) => {
                const item = localStorage.getItem("mode");
                if (item !== null) {
                    if (item !== mode) {
                        window.localStorage.setItem('mode', mode);
                        toggleCSS();
                    }
                    else {
                        console.log(`You already have ${mode} mode enabled.`);
                    }
                }
                else {
                    window.localStorage.setItem('mode', mode);
                    toggleCSS();
                }
            }
            const toggleCSS = () => {
                const cssLink = document.getElementById("css-mode");
                cssLink.setAttribute("href", `./settings-${window.localStorage.getItem("mode")}-mode.css`);
            }


            const toggleBannerMode = (banner) => {
                const item = localStorage.getItem("banner");
                if (item !== null) {
                    if (item !== banner) {
                        window.localStorage.setItem('banner', banner);
                        toggleBanner();
                    }
                    else {
                        console.log(`You already have ${banner} mode enabled.`);
                    }
                }
                else {
                    window.localStorage.setItem('banner', banner);
                    toggleBanner();
                }
            }

            const toggleBanner = () => {
                const cssHeader = document.getElementById("css-header");
                cssHeader.setAttribute("href", `../../assets/css/global/${window.localStorage.getItem("banner")}-header.css`);
            }


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