<?php
// start the session (access to user: loggedIn, first_name, email, type, course_name, course_id, section_id,
// selected_course_name, selected_course_id, selected_section_id)

session_start();

// check if user is logged in, if they are not then force redirect them to main page
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: ../index.html");
    exit;
}

// if user account type is not 'instructor' then force logout
if($_SESSION["type"] !== "instructor"){
    header("location: ../register_login/logout.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Instructor Course Page</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_index2.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body>
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
                            <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main>
                <h1><?= $_SESSION['selected_course_name']; ?> - <?= $_SESSION['selected_course_id'] ?> - <?= $_SESSION['selected_section_id'] ?></h1>

                <button class="regular_button" onclick="redirectToInstrAssess()">
                    Students Overview
                </button>

                <br><br>

                <button class="regular_button" onclick="redirectToInstrCreate()">
                    Create an Assessment
                </button>

                <br><br>

                <button class="regular_button" onclick="redirectToInstrView()">
                    View / Edit / Delete an Assessment
                </button>

            </main>

            <br><br>

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

            let redirectToInstrAssess = () => {
                window.location.href = "instr_assess1.php";
            }

            let redirectToInstrCreate = () => {
                window.location.href = "instr_create1.php";
            }

            let redirectToInstrView = () => {
                window.location.href = "instr_multi.php";
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