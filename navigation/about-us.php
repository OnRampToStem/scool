<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>About Us</title>
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


            .content-div {
                width: 100%;
                margin-bottom: 30px;
            }

            /* ABOUT US STYLING */
            .intro-div {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            #img1 {
                flex: 1;
                width: 563.2px;
                height: 352px;
                padding: 10px;
            }
            .intro-div p {
                flex: 1;
                font-size: 18px;
                font-weight: 500;
                padding: 10px;
            }
            #img2 {
                flex: 1;
                padding: 10px;
            }
            #img3 {
                flex: 1;
                padding: 10px;
            }

            /* WEB DEVELOPER TEAM STYLING */
            #member-div {
                width: 100%;
                display: flex;
                justify-content: space-evenly;
            }
            .member img{
                width: 150px;
                height: 150px;
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

            <div class="content-div">
                <div>
                    <h1>About Us</h1>
                </div>

                <div>
                    <div class="intro-div">
                        <img id="img1" src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Fwallpapercave.com%2Fwp%2FxZxE5jt.jpg&f=1&nofb=1">
                        <p>
                            The University California State University, Fresno was founded as Fresno State Normal School in 1911, became a teacher's college in 1921, and has offered advanced degrees since 1949. 
                            The university's popular nickname is "Fresno State." Our mascot is the Bulldog.
                        </p>
                    </div>

                    <div class="intro-div">
                        <p>
                            On-Ramp to STEM, a project funded by the California Education Learning Lab program, forms a partnership between Fresno State, Fresno City College, Clovis Community College, and University High School to improve learning outcomes in math courses, in particular college algebra and pre-calculus, by developing and implementing an open-source adaptive learning technology, and utilizing culturally responsive teaching pedagogy. 
                            We focus on algebra and pre-calculus because they represent important, foundational courses at the start of the STEM pathway. 
                            However, for many students, especially under-represented minorities and other first-generation or low-income students, these courses act as a roadblock; making it difficult for students to transition to a STEM major. 
                            Thus, we seek to transform the culture of learning in math classrooms so students achieve greater fluency and self-efficacy in mathematics that is required throughout the STEM disciplines. 
                            Our hope is to build a proverbial "on-ramp" that makes STEM more accessible and closes the achievement gaps among student populations in the San Joaquin Valley.
                        </p>
                        <img id="img2" src="https://external-content.duckduckgo.com/iu/?u=http%3A%2F%2Fwww.fresnostate.edu%2Fadminserv%2Ffacilitiesmanagement%2Fimages%2Fscience2%2F7.jpg&f=1&nofb=1">
                    </div>

                    <div class="intro-div">
                        <img id="img3" src="https://external-content.duckduckgo.com/iu/?u=https%3A%2F%2Ftse1.mm.bing.net%2Fth%3Fid%3DOIP.tfJiqLNQ55hTVu-2mf6fkgHaE8%26pid%3DApi&f=1">
                        <p>
                            The words Discovery, Diversity and Distinction are everywhere you see the Fresno State logo. There’s a reason for that. It reminds us why we are here: to educate and empower student success through the discoveries they make, the diversity that will enrich their life experiences, and the distinctions they will imprint on a changing world.
                        </p>
                    </div>
                </div>
            </div>

            <div class="content-div">
                <div>
                    <h1>The Team</h1>
                </div>

                <div id="member-div">
                    <div class="member">
                        <img src="http://www.fresnostate.edu/csm/csci/images/facstaff/hubert_cecotti.jpg" alt="Member Image" title="Dr. Hubert Cecotti">
                        <div class="about-member">
                            <p>Dr. Hubert Cecotti</p>
                            <p>Project Manager</p>
                            <a href="mailto:hcecotti@csufresno.edu" class="email">hcecotti@csufresno.edu</a>
                        </div>
                    </div>
                    <div class="member">
                        <img src="https://media.licdn.com/dms/image/D5635AQHXO6lMc122oA/profile-framedphoto-shrink_400_400/0/1675819003907?e=1676916000&v=beta&t=tUN_iyyho6_GCqwKARh5MZfETwQYO2-DM4pxD4eE9bE" alt="Member Image" title="Luis Valencia">
                        <div class="about-member">
                            <p>Luis Valencia</p>
                            <p>Lead Developer</p>
                            <a href="mailto:luisss3v@mail.fresnostate.edu" class="email">luisss3v@mail.fresnostate.edu</a>
                        </div>
                    </div>
                </div>
            </div>

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
                                <li><a href="">About Us</a></li>
                                <li><a href="faq.php">FAQ</a></li>
                                <li><a href="contact-us.php">Contact Us</a></li>
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