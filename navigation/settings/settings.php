<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Settings</title>
        <link rel="stylesheet" href="../../assets/css/global/header.css" />
        <link rel="stylesheet" href="../../assets/css/global/global.css" />
        <link id="mode-css" rel="stylesheet" href="./settings-light-mode.css" />
    </head>
    <body onload="initializeCSS();">
        <div id="app">
            <header>
                <nav class="container">
                    <div id="userProfile" class="dropdown">
                        <button id="userButton" class="dropbtn" onclick="showDropdown()">Hello <?= $_SESSION["name"]; ?>!</button>
                        <div id="myDropdown" class="dropdown-content">
                            <a href="../register_login/logout.php">Logout</a>
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
                <h1>Settings</h1>
                <button onclick="toggleCSSMode('light');">Light Mode</button>
                <button onclick="toggleCSSMode('dark');">Dark Mode</button>
                <button onclick="toggleCSSMode('colorful');">Colorful Mode</button>
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
                                <li><a href="about-us.php">About Us</a></li>
                                <li><a href="faq.php">FAQ</a></li>
                                <li><a href="">Contact Us</a></li>
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
                const cssLink = document.getElementById("mode-css");
                cssLink.setAttribute("href", `./settings-${window.localStorage.getItem("mode")}-mode.css`);
            }

            const initializeCSS = () => {
                const item = localStorage.getItem("mode");
                const cssLink = document.getElementById("mode-css");
                if (item === null) {
                    cssLink.setAttribute("href", "./settings-light-mode.css");
                }
                else {
                    cssLink.setAttribute("href", `./settings-${window.localStorage.getItem("mode")}-mode.css`);
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