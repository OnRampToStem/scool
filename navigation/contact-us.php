<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Contact Us</title>
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
        <style>
            /* HEADER STYLING */
            #OR2STEM-HEADER {
                color: navy;
                font-weight: bold;
                padding-left: 20px;
            }
            #OR2STEM-HEADER-A {
                color: navy;
                text-decoration: none;
                transition-duration: 0.5s;
            }
            #OR2STEM-HEADER-A:hover {
                color: red;
            }
            #userProfile {
                float: right;
                margin-right: 15px;
                position: relative;
            }
            #userButton {
                width: auto;
                height: 30px;
                font-size: 14px;
                font-weight: 600;
                color: white;
                background-color: navy;
                cursor: pointer;
                margin-right: 30px;
            }
            #user-picture {
                width: 30px; 
                height: 30px; 
                position: absolute; 
                right: 0; 
                bottom: 0;
            }
            .dropdown {
                position: relative;
                display: inline-block;
            }
            .dropdown-content {
                display: none;
                position: absolute;
                margin-left: 3px;
                color: white;
                background-color: navy;
                width: 85px;
                box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                z-index: 2;
            }
            .dropdown-content a {
                color: white;
                display: block;
                text-decoration: none;
                padding: 5px;
                text-align: center;
            }
            .dropdown a:hover {
                background-color: red;
            }
            .show {display: block;}


            .outer-section {
                width: 100%;
                display: flex;
                justify-content: space-evenly;
                padding: 10px;
            }
        </style>
    </head>
    <body>
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
				<div>
					<h1>Contact Us</h1>
				</div>

				<div class="outer-section">
                    <div class="inner-section">
                        <h4>Our Address</h4>
                        <p>5241 N. Maple Ave.<br>Fresno, CA 93740<br></p>

                        <h4>Our Phone Number</h4>
                        <p>+1 (559) 278 - 4240</p>
                    </div>
                    <div class="inner-section">
                        <form action="/">
                            <div class="full-width">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>

                            <div class="full-width">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>

                            <div class="full-width">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject">
                            </div>

                            <div class="full-width">
                                <label for="message">Message</label>
                                <textarea id="message" name="message" style="height:200px" required></textarea>
                            </div>

                            <input type="submit" class="btn btn-fsblue" value="Submit">
                        </form>
                    </div>
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
            // DRIVER
            if ("<?= $_SESSION['type'] ?>" === "Instructor" || "<?= $_SESSION['type'] ?>" === "Mentor") {
                document.getElementById("OR2STEM-HEADER-A").setAttribute("href", "../instructor/instr_index1.php");
                document.getElementById("footer-link").setAttribute("href", "../instructor/instr_index1.php");
                document.getElementById("footer-link-home").setAttribute("href", "../instructor/instr_index1.php");
                document.getElementById("footer-link-scale").setAttribute("href", "../instructor/instr_index1.php");
            }
            else {
                document.getElementById("OR2STEM-HEADER-A").setAttribute("href", "../student/student_index.php");
                document.getElementById("footer-link").setAttribute("href", "../student/student_index.php");
                document.getElementById("footer-link-home").setAttribute("href", "../student/student_index.php");
                document.getElementById("footer-link-scale").setAttribute("href", "../student/student_index.php");
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