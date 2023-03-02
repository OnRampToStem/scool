<?php 
// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor"){
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$query; $res;
$complete = "temp";
$search_tags;
$number;
$learningoutcome_selected;
$dynamic_ids = []; // list of all dynamic question ids extracted from db

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // accept POST input
    $search_tags = $_POST["search_tags"]; // holds the lo selected (1.2.3)
    $number = $_POST["number"]; // holds max number of the lo selected
    $learningoutcome_selected = $_POST["learningoutcome_selected"];

    // connect to the db
    require_once "../register_login/config.php";

    // get rows at random with selected lo
    $query = "SELECT problem_number FROM dynamic_questions WHERE lo_tag = '{$search_tags}'
              order by random() limit '{$number}';";
    $res = pg_query($con, $query) or die("Cannot execute query: {$query}\n" . pg_last_error($con) . "\n");

    if (pg_num_rows($res) === 0) $complete = "false";
    else {
        // push data into array
        while ($row = pg_fetch_row($res)) {
            // add 0s to the front of the problem number if the length of the problem number is not 8
            if (strlen($row[0]) !== 8) {
                switch (strlen($row[0])) {
                    case 1:
                        $row[0] = "0000000" . $row[0];
                        break;
                    case 2:
                        $row[0] = "000000" . $row[0];
                        break;
                    case 3:
                        $row[0] = "00000" . $row[0];
                        break;
                    case 4:
                        $row[0] = "0000" . $row[0];
                        break;
                    case 5:
                        $row[0] = "000" . $row[0];
                        break;
                    case 6:
                        $row[0] = "00" . $row[0];
                        break;
                    case 7:
                        $row[0] = "0" . $row[0];
                        break;
                }
            }
            array_push($dynamic_ids, $row[0]);
        }
        $complete = "true";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Dynamic Questions</title>
        <link rel="stylesheet" href="../assets/css/instructor/dynamic.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
    </head>
    <body onload="initialize();">
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
                            <a id="OR2STEM-HEADER-A" href="./instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <main>
                <div id="group1">
                    <h1>Dynamic Questions</h1>

                    <div id="loading-div">
                        LOADING...
                    </div>

                    <h3 id="group1h3" style="display:none;">Please select a Chapter, a Section, and a Learning Outcome.</h3>

                    <div id="group1_1" style="display:none;">
                        <div id="group1_1_1">
                            <h3>Chapter</h3>
                            <select id="chapter_options" onchange="getSectionOptions();"></select>
                        </div>
                        <div id="group1_1_2">
                            <h3>Section</h3>
                            <select id="section_options" onchange="getLoOptions();">
                                <option selected="selected" disabled>Select a Section</option>
                            </select>                                
                        </div>
                        <div id="group1_1_3">
                            <h3>Learning Outcome</h3>
                            <select id="learningoutcome_options" onchange="setFormData();">
                                <option selected="selected" disabled>Select a Learning Outcome</option>
                            </select>
                        </div>
                    </div>
                   
                    <div id="form_div">
                        <form id="main_form" action="" method="post">
                            <input id="search_tags" name="search_tags" type="text" style="display:none;">
                            <input id="number" name="number" type="text" style="display:none;">
                            <input id="learningoutcome_selected" name="learningoutcome_selected" type="text" style="display:none;">
                            <input id="go_btn" type="submit" value="Go" style="display:none;">
                        </form>
                    </div>
                </div>

                <hr id="hr" style="border: 1px dashed black; display: none;">

                <div id="selected-lo-header-div" style="display: none;">
                    <p><?= $learningoutcome_selected; ?></p>
                </div>

                <div id="question-display-div" style="display: none;">
                    <div id="prev-btn-div">
                        <button id="prev-btn" onclick="prev();">Previous</button>
                    </div>

                    <div id="content-div">
                        <h1 id="question-count-h1"></h1>
                    </div>

                    <div id="next-btn-div">
                        <button id="next-btn" onclick="next();">Next</button>
                    </div>
                </div>
            </main>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href=""><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="./instr_index1.php">Home</a></li>
                                <li><a href="../navigation/about-us.php">About Us</a></li>
                                <li><a href="../navigation/faq.php">FAQ</a></li>
                                <li><a href="../navigation/contact-us.php">Contact Us</a></li>
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
            ////////////////
            // JS GLOBALS //
            ////////////////
            let dynamic_json; // assoc arr of (lo => num) or ("1.2.3" => 15)
            let dynamic_ids;  // arr holding each id pertaining to a selected lo
            let counter = 0;
            // used to make XMLHttpRequest
            let ch_req;
            let sec_req;
            let lo_req;
            // data
            const ch_digits = [];
            const sec_digits = [];
            const lo_digits = [];





            /////////////////
            // MAIN DRIVER //   
            /////////////////

            let initialize = () => {
                loadJSON();
                getChapterOptions();
                document.getElementById("group1h3").style.display = "";
                document.getElementById("group1_1").style.display = "";
                document.getElementById("go_btn").style.display = "";
                document.getElementById("loading-div").style.display = "none";

                // checking if php process was run
                if ("<?= $complete; ?>" === "true"){
                    dynamic_ids = <?= json_encode($dynamic_ids); ?>;
                    buildiFrame();
                    document.getElementById("question-display-div").style.display = "";
                }
                else if ("<?= $complete; ?>" === "false") {
                    alert("There are no dynamic questions in the learning outcome you have selected.");
                }
            }





            /////////////////////
            // FUNCTIONALITIES //
            /////////////////////

            let setFormData = () => {
                // lo number
                let select = document.getElementById("learningoutcome_options");
                select = select.options[select.selectedIndex].text;
                let pos1 = select.indexOf(".");
                let pos2 = select.indexOf(".", pos1 + 1);
                let pos3 = select.indexOf(".", pos2 + 1);
                var learningoutcomeNumber = select.slice(0, pos3);
                document.getElementById("search_tags").value = learningoutcomeNumber;

                // learning outcome name
                select = document.getElementById("learningoutcome_options");
                document.getElementById("learningoutcome_selected").value = select.options[select.selectedIndex].text;

                // number of dynamic questions for the selected learning outcome
                document.getElementById("number").value = dynamic_json[learningoutcomeNumber];
            }


            const buildiFrame = () => {
                let iframe = document.createElement('iframe');
                iframe.id = "frame";
                iframe.title = "LibreTexts";
                iframe.src = "https://imathas.libretexts.org/imathas/embedq2.php?id=" + dynamic_ids[0];
                iframe.margin = "0 auto";
                iframe.width = "100%";
                iframe.height = "1800px";
                document.getElementById('content-div').appendChild(iframe);
                document.getElementById("question-count-h1").innerHTML = `Question ${counter + 1} / ${dynamic_ids.length}`;
                document.getElementById("hr").style.display = "";
                document.getElementById("selected-lo-header-div").style.display = "";
            }


            const next = () => {
                // making sure we are in the valid range and that the question has been answered before moving on
                if (counter + 1 < dynamic_ids.length) {
                    // update counter
                    counter++;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + dynamic_ids[counter]);
                    // update question number
                    document.getElementById("question-count-h1").innerHTML = `Question ${counter + 1} / ${dynamic_ids.length}`;
                }
            }

            const prev = () => {
                // making sure we are in the valid range and that the question has been answered before moving on
                if (counter > 0) {
                    // update counter
                    counter--;
                    // update iframe
                    document.getElementById("frame").setAttribute("src", "https://imathas.libretexts.org/imathas/embedq2.php?id=" + dynamic_ids[counter]);
                    // update question number
                    document.getElementById("question-count-h1").innerHTML = `Question ${counter + 1} / ${dynamic_ids.length}`;
                }
            }





            ////////////////////////////////////////////////////
            // CHAPTER, SECTION, & LEARNING OUTCOME SELECTION //
            ////////////////////////////////////////////////////

            // getting all chapters from openStax.json          
            let getChapterOptions = () => {
                ch_req = new XMLHttpRequest();
                ch_req.open('POST', './get/ch_names_3.php', true);
                ch_req.onreadystatechange = getChapterOptionsResponse;
                ch_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_req.send("chs=" + JSON.stringify(ch_digits));
            }
            let getChapterOptionsResponse = () =>{
                if (ch_req.readyState == 4 && ch_req.status == 200) {
                    // receive response
                    //console.log("PHP sent back: " + ch_req.responseText);
                    let ch_obj = JSON.parse(ch_req.responseText);
                    
                    // now display the chapters data
                    let str = '<option selected="selected" disabled>Select a Chapter</option>';
                    for(const [key, value] of Object.entries(ch_obj)) {
                        str += `<option value="${key}">${key}. ${value}</option>`;
                    }
                    document.getElementById("chapter_options").innerHTML = str;
                }
            }   

            // getting all sections from selected chapter from openStax.json    
            let getSectionOptions = () => {
                sec_req = new XMLHttpRequest();
                sec_req.open('POST', './get/sec_names_1.php', true);
                sec_req.onreadystatechange = getSectionOptionsResponse;
                sec_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                let select = document.getElementById("chapter_options");
                let chapter = select.options[select.selectedIndex].value;
                sec_req.send("chapter=" + chapter);
            }
            let getSectionOptionsResponse = () =>{
                if (sec_req.readyState == 4 && sec_req.status == 200) {
                    // receive response
                    //console.log("PHP sent back: " + sec_req.responseText);
                    let sec_obj = JSON.parse(sec_req.responseText);
                    
                    // now display the sections data
                    let str = '<option selected="selected" disabled>Select a Section</option>';
                    for(const [key, value] of Object.entries(sec_obj)){
                        let sec_num = key.slice(key.indexOf('.') + 1, key.length);
                        str += `<option value="${sec_num}">${key}. ${value}</option>`;
                    }
                    document.getElementById("section_options").innerHTML = str;

                    // resetting lo options for better user experience
                    document.getElementById("learningoutcome_options").innerHTML = '<option selected="selected" disabled>Select a Learning Outcome</option>';
                }
            }   

            // getting all los from selected section from openStax.json     
            let getLoOptions = () =>{
                lo_req = new XMLHttpRequest();
                lo_req.open('POST', './get/lo_names_1.php', true);
                lo_req.onreadystatechange = getLoOptionsResponse;
                lo_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                let select = document.getElementById("chapter_options");
                let chapter = select.options[select.selectedIndex].value;
                select = document.getElementById("section_options");
                let section = select.options[select.selectedIndex].value;
                lo_req.send("chapter=" + chapter + "&section=" + section);
            }
            let getLoOptionsResponse = () =>{
                if (lo_req.readyState == 4 && lo_req.status == 200) {
                    // receive response
                    //console.log("PHP sent back: " + lo_req.responseText);
                    let lo_obj = JSON.parse(lo_req.responseText);
                    
                    // now display the lo data
                    let str = '<option selected="selected" disabled>Select a Learning Outcome</option>';
                    for(const [key, value] of Object.entries(lo_obj)){
                        let lo_num = key.slice(key.indexOf('.', key.indexOf('.') + 1) + 1, key.length);
                        str += `<option value="${lo_num}">${key}. ${value}</option>`;
                    }
                    document.getElementById("learningoutcome_options").innerHTML = str;
                }
            }





            ///////////////////////////////
            // BACKGROUND FUNCTIONALITES //
            ///////////////////////////////

            let workJSON = () => {

                for (const prop in dynamic_json) {
                    let idx1 = prop.indexOf(".");
                    let ch_digit = prop.slice(0, idx1);
                    if (!ch_digits.includes(ch_digit) && dynamic_json[prop] !== 0) {
                        ch_digits.push(ch_digit);
                    }
                }

                console.log(ch_digits);

                getChapterOptions();

            }


            let loadJSON = () => {
                let load_json_req = new XMLHttpRequest();
                load_json_req.onreadystatechange = function() {
                    if(load_json_req.readyState == 4 && load_json_req.status == 200){
                        dynamic_json = JSON.parse(load_json_req.responseText);
                        //console.log(dynamic_json);
                        //console.log(dynamic_json["1.2.3"]);
                        workJSON();
                    }
                }
                load_json_req.open("GET", "get/dynamic.json", true);
                load_json_req.send();  
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