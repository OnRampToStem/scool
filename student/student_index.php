<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if ($_SESSION["type"] !== "Learner") {
    header("location: ../register_login/logout.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $_SESSION["course_name"]; ?></title>
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
            cssLink.setAttribute("href", `../assets/css/student/student_index-${window.localStorage.getItem("mode")}-mode.css`);
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
                    <h1 id="OR2STEM-HEADER">On-Ramp to STEM</h1>
                </div>

                <div class="inner-banner">
                    <div class="banner-img"></div>
                </div>
            </nav>
        </header>

        <main>
            <div id="header-div">
                <h1>
                    <?= $_SESSION["course_name"]; ?>
                    <?php
                    // conditionally showing course id if test student only //
                    if ($_SESSION["name"] === "Test Student" && (strpos($_SESSION["email"], "test_student") !== false) && (strpos($_SESSION["email"], "@canvas.instructure.com") !== false)) {
                        echo ('<br>' . $_SESSION["course_id"]);
                    }
                    ?>
                </h1>
                <hr style="border: 1px solid black;">
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToStudentBrowse()">Browse Available Practice Questions</button>
            </div>

            <div class="btn-div">
                <button class="regular_button" onclick="redirectToStudentAssessment()">Browse Available Assessments</button>
            </div>

            <?php
            // conditionally generating button if test student only //
            if ($_SESSION["name"] === "Test Student" && (strpos($_SESSION["email"], "test_student") !== false) && (strpos($_SESSION["email"], "@canvas.instructure.com") !== false)) {
                echo ('<div class="btn-div">
                        <button class="regular_button" onclick="redirectToInstructorView()">Instructor View</button>
                     </div>');
            }
            ?>
        </main>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="">
                            <p>On-Ramp to STEM</p>
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
                            <li><a href=""> CSU SCALE </a></li>
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
        const redirectToStudentBrowse = () => {
            window.location.href = "student_browse.php";
        }

        const redirectToStudentAssessment = () => {
            window.location.href = "student_assessment1.php";
        }

        const redirectToInstructorView = () => {
            // get student name and email //
            const name = "<?php echo $_SESSION["name"]; ?>";
            const email = "<?php echo $_SESSION["email"]; ?>";

            // verify they are test students //
            if (name === "Test Student" && (email.indexOf("test_student") !== -1) && (email.indexOf("@canvas.instructure.com") !== -1)) {
                let xhr = new XMLHttpRequest();
                xhr.open("GET", "./js-php/instructorViewHandler.php", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        //console.log(xhr.responseText);
                        const response = xhr.responseText.trim();
                        if (response === "Login Instructor") {
                            // redirect to the instructor home page //
                            window.location.href = "../instructor/instr_index1.php";
                        } else {
                            alert("Unknown Error.");
                        }
                    }
                }
                xhr.send();
            } else {
                alert("Only Test Students have access to this functionality.");
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