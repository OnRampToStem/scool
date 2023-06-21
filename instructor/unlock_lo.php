<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor") {
    header("location: ../register_login/logout.php");
    exit;
}

$chapter = "Select a Chapter";
$section = "Select a Section";
$learningoutcome = "Select a Learning Outcome";

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
            cssLink.setAttribute("href", `../assets/css/instructor/unlock_lo-${window.localStorage.getItem("mode")}-mode.css`);
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

<body onload="getChapterOptions();">
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
                        <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                    </h1>
                </div>

                <div class="inner-banner">
                    <div class="banner-img"></div>
                </div>
            </nav>
        </header>

        <main>
            <h1>Unlock Learning Outcome(s)</h1>
            <h3>Select A Learning Outcome to Unlock</h3>
            <div id="main-select-container">
                <div id="chapter-container">
                    <h3>Chapter</h3>
                    <select id="chapter_options" onchange="getSectionOptions();">
                        <option selected="selected" disabled><?= $chapter; ?></option>
                    </select>
                </div>
                <div id="section-container">
                    <h3>Section</h3>
                    <select id="section_options" onchange="getLoOptions();">
                        <option selected="selected" disabled><?= $section; ?></option>
                    </select>
                </div>
                <div id="lo-container">
                    <h3>Learning Outcome</h3>
                    <select id="learningoutcome_options">
                        <option selected="selected" disabled><?= $learningoutcome; ?></option>
                    </select>
                </div>
            </div>
            <button id="submit-btn" onclick="handleSubmit();">Unlock Learning Outcome</button>
        </main>

        <footer>
            <div class="container">
                <div class="footer-top flex">
                    <div class="logo">
                        <a href="instr_index1.php" class="router-link-active">
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
        ////////////////////////////////////////////////////
        // CHAPTER, SECTION, & LEARNING OUTCOME SELECTION //
        ////////////////////////////////////////////////////

        const readChapterDigit = () => {
            // ex: ch => 1
            let ch = document.getElementById("chapter_options").value;
            //console.log(ch);
            return ch;
        }

        const readSectionDigit = () => {
            // ex: sec => 1.2
            // we want to extract 2
            let sec = document.getElementById("section_options").value;
            let digitsArray = sec.split(".");
            let lastDigit = digitsArray[digitsArray.length - 1];
            //console.log(lastDigit);
            return lastDigit;
        }

        const readLoDigit = () => {
            // ex: lo => 1.2.3
            // we want to extract 3
            let lo = document.getElementById("learningoutcome_options").value;
            let digitsArray = lo.split(".");
            let lastDigit = digitsArray[digitsArray.length - 1];
            //console.log(lastDigit);
            return lastDigit;
        }

        // getting all chapters from openStax.json
        let getChapterOptions = () => {
            //console.log("Getting all chapter options...");
            let req = new XMLHttpRequest();
            req.open("GET", "./get/ch_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let ch_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $chapter; ?>" + '</option>';
                    for (const [key, value] of Object.entries(ch_obj)) {
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${key}"
                    }
                    document.getElementById("chapter_options").innerHTML = str;
                }
            }
            req.send();
        };

        // getting all sections from selected chapter from openStax.json
        let getSectionOptions = () => {
            //console.log("Getting all section options...");
            let req = new XMLHttpRequest();
            req.open("POST", "./get/sec_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let sec_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $section; ?>" + '</option>';
                    for (const [key, value] of Object.entries(sec_obj)) {
                        //let sec_num = key.slice(key.indexOf('.') + 1, key.length);
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${sec_num}"
                    }
                    document.getElementById("section_options").innerHTML = str;
                }
            }
            req.send("chapter=" + readChapterDigit());
        };

        // getting all los from selected section from openStax.json
        let getLoOptions = () => {
            //console.log("Getting all learning outcome options...");
            let req = new XMLHttpRequest();
            req.open("POST", "./get/lo_names_1.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    //console.log(req.response);
                    let lo_obj = JSON.parse(req.response);
                    let str = '<option selected="selected" disabled>' + "<?= $learningoutcome; ?>" + '</option>';
                    for (const [key, value] of Object.entries(lo_obj)) {
                        //let lo_num = key.slice(key.indexOf('.', key.indexOf('.') + 1) + 1, key.length);
                        str += `<option value="${key}">${key}. ${value}</option>`; //value="${lo_num}"
                    }
                    document.getElementById("learningoutcome_options").innerHTML = str;
                }
            }
            req.send("chapter=" + readChapterDigit() + "&section=" + readSectionDigit());
        };


        const validateInputs = () => {
            let select = document.getElementById("chapter_options");
            let chapterTxt = select.options[select.selectedIndex].text;
            //console.log(chapterTxt);

            select = document.getElementById("section_options");
            let sectionTxt = select.options[select.selectedIndex].text;
            //console.log(sectionTxt);

            select = document.getElementById("learningoutcome_options");
            let loTxt = select.options[select.selectedIndex].text;
            //console.log(loTxt);

            if (chapterTxt === "Select a Chapter" || sectionTxt === "Select a Section" || loTxt === "Select a Learning Outcome") {
                return false;
            } else {
                return true;
            }
        }

        const handleSubmit = () => {
            if (!validateInputs()) {
                alert("Make sure you select a Chapter, Section, and Learning Outcome.");
                return;
            }

            let ch_digit = readChapterDigit();
            let sec_digit = readSectionDigit();
            let lo_digit = readLoDigit();

            console.log(`Unlocking learning outcome ${ch_digit}.${sec_digit}.${lo_digit}`);
            let req = new XMLHttpRequest();
            req.open("POST", "./pgsql/unlock_lo.php", true);
            req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    console.log(req.response);
                    alert(`Learning Outcome ${ch_digit}.${sec_digit}.${lo_digit}. has been unlocked for all your students!`);
                }
            }
            req.send("ch_digit=" + ch_digit + "&sec_digit=" + sec_digit + "&lo_digit=" + lo_digit);
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