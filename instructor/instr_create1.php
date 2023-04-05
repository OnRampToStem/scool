<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' then force logout
if($_SESSION["type"] !== "Instructor"){
    header("location: ../register_login/logout.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Create an Assessment</title>
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
                cssLink.setAttribute("href", `../assets/css/instructor/instr_create1-${window.localStorage.getItem("mode")}-mode.css`);
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

            <div>
                <h1><?= $_SESSION["selected_course_name"]; ?></h1>
            </div>

            <main>
                <h1 id="header"><u>Assessment Creation</u></h1>

                <br>

                <form action="instr_create2.php" method="POST">

                    <div id="group1">
                        <div id="group1_1">
                            <div class="group1_1_1">
                                <label for="name"><strong>Name:</strong></label>
                                <input type="text" id="name" name="name" placeholder="Assessment #1" required>
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a name for your assessment.">
                            </div>
                            <br><br>
                            <div class="group1_1_1">
                                <label><strong>Public:</strong></label>
                                <input type="radio" id="public_yes" name="public" value="Yes" checked>
                                <label id="public_yes_label" for="public_yes">Yes</label>
                                <input type="radio" id="public_no" name="public" value="No">
                                <label id="public_no_label" for="public_no">No</label>
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Would you like your assessment to be accessible to other instructors/students?">
                            </div>
                            <br><br>
                            <div class="group1_1_1">
                                <label for="duration"><strong>Duration:</strong></label>
                                <input type="number" id="duration" name="duration" min="1" placeholder="30" required>   
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a number (representing minutes), for a student to take the assessment.">                             
                            </div>
                            <!-- hidden input -->
                            <input type="number" id="num_of_selected_los" name="num_of_selected_los" value="" hidden readonly required>
                        </div>
                        <div id="group1_2">
                            <div class="group1_2_1">
                                <label for="open_date"><strong>Open Date:</strong></label>
                                <input type="date" id="open_date" name="open_date" required>
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a date in which assessment will be open for students to take.">
                            </div>
                            <br><br>
                            <div class="group1_2_1">
                                <label for="open_time"><strong>Open Time:</strong></label>
                                <input type="time" id="open_time" name="open_time" required>
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a time in which assessment will be open for students to take.">
                            </div>
                            <br><br>
                            <div class="group1_2_1">
                                <label for="close_date"><strong>Close Date:</strong></label>
                                <input type="date" id="close_date" name="close_date" required>
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a date in which assessment will be closed for students.">
                            </div>
                            <br><br>
                            <div class="group1_2_1">
                                <label for="close_time"><strong>Close Time:</strong></label>
                                <input type="time" id="close_time" name="close_time" required> 
                                <img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle"
                                    title="Input a time in which assessment will be closed for students."> 
                            </div>
                        </div>
                    </div>

                    <br><br>

                    <div id="group2">
                        <p><strong>Please select at least 1 learning outcome to insert into the assessment:</strong></p>
                        <div id="group2_1">
                            <div id="group2_1_1">
                                <p><strong>Chapter</strong></p>
                                <select id="ch_select" onchange="getSectionOptions();getLoOptions();" required>
                                    <option value="">Select a Chapter</option>
                                </select>
                            </div>
                            <div id="group2_1_2">
                                <p><strong>Section</strong></p>
                                <select id="sec_select" onchange="getLoOptions();" required>
                                    <option value="">Select a Section</option>
                                </select>                                
                            </div>
                            <div id="group2_1_3">
                                <p><strong>Learning Outcome</strong></p>
                                <select id="lo_select" onchange="insertSelection();" required>
                                    <option value="">Select a Learning Outcome</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div id="group3" style="display:none;">
                        <p>
                            <span id="table_lo"></span>
                            &emsp;&emsp;&emsp;&emsp;
                            <span id="table_questions"></span>
                            &emsp;&emsp;&emsp;&emsp;
                            <span id="table_points"></span>
                        </p>
                        <table id="sel_table_res">
                            <thead>
                                <tr>
                                    <th id="th1" scope="col">Chapter Name<br>Section Name<br>Learning Outcome Name</th>
                                    <th id="th2" scope="col">Learning Outcome Number</th>
                                    <th id="th3" scope="col">Questions Per Learning Outcome</th>
                                    <th id="th4" scope="col">Points Per Question</th>
                                    <th id="th5" scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="sel_table_res_body"></tbody>
                        </table>
                    </div>

                    <br>

                    <div id="group4">
                        <input id="submit_button" type="submit" name="submit" value="Create Assessment">
                    </div>
                    
                    <br>

                </form>

            </main>

            <br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="instr_index1.php"><p>On-Ramp to STEM</p></a>
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

        <!-- START OF JAVASCRIPT -->
        <script type="text/javascript">
            /* GLOBALS */
            let row_num = 1; // used to control the attribute ids of the dynamic table inputs (number of rows)
            let table_hidden = true; // used to control the attribute style display for table
            let numLos = 0; // used to control the number of learning outcomes in the table
            let numQuestions = 0; // used to sum the total number of questions in the table
            let numPoints = 0; // used to sum the total number of points in the table
            let dynamic_json; // holds assoc arr of lo => num of lo ("1.2.3" => 15)


            const initialize = async () => {
                await getChapterOptions();
                await loadJSON();
            }


            // function to get the complete selected chapter name (ex: 1. Functions)
            let getFullChapter = () =>{
                let select = document.getElementById("ch_select");
                return select.options[select.selectedIndex].text;
            }
            // function to get the complete selected section name (ex: 1.1. Functions and Function)
            let getFullSection = () =>{
                let select = document.getElementById("sec_select");
                return select.options[select.selectedIndex].text;
            }
            // function to get the complete selected learning outcome name
            // (ex: 1.1.1. Determining Whether a Relation Represents a Function)
            let getFullLearningOutcome = () =>{
                let select = document.getElementById("lo_select");
                return select.options[select.selectedIndex].text;
            }


            /* functions to add or sub values of numLos, numQuestions, and numPoints 
                also update display in client side of these values */
            let addNumLos = () =>{
                // update numLos
                numLos++;
                // update display
                if(numLos === 1){
                    document.getElementById("table_lo").innerHTML = numLos + " Learning Outcome Selected";
                } else {
                    document.getElementById("table_lo").innerHTML = numLos + " Learning Outcomes Selected";
                }
            }
            let subNumLos = () =>{
                // update numLos
                numLos--;
                // update display
                if(numLos === 1){
                    document.getElementById("table_lo").innerHTML = numLos + " Learning Outcome Selected";
                } else {
                    document.getElementById("table_lo").innerHTML = numLos + " Learning Outcomes Selected";
                }
            }
            let addNumQuestions = (value, ele) =>{
                // update numQuestions
                numQuestions += parseInt(value);
                // update display
                if(numQuestions === 1){
                    document.getElementById("table_questions").innerHTML = numQuestions + " Question Total";
                } else {
                    document.getElementById("table_questions").innerHTML = numQuestions + " Questions Total";
                }

                // grab list of <td> elements in row from table
                let tableDataList = ele.children;
                // grab individual <input> element value (Points)
                let input1 = tableDataList[3].children;
                // only update if Points input is filled with a value
                if(input1[0].value !== "") {
                    let calculatedPoints = parseInt(value) * parseInt(input1[0].value);
                    // update numPoints
                    numPoints += calculatedPoints;
                    if(numPoints === 1){
                        document.getElementById("table_points").innerHTML = numPoints + " Point Total";
                    } else {
                        document.getElementById("table_points").innerHTML = numPoints + " Points Total";
                    }
                }
            }
            let subNumQuestions = (value) =>{
                // can't go negative, so don't allow subtraction when numQuestions is 0
                if(numQuestions !== 0){                
                    // update numQuestions
                    numQuestions -= parseInt(value);
                    // update display
                    if(numQuestions === 1){
                        document.getElementById("table_questions").innerHTML = numQuestions + " Question Total";
                    } else {
                        document.getElementById("table_questions").innerHTML = numQuestions + " Questions Total";
                    }
                }
            }            
            let addNumPoints = (value, ele) =>{
                // grab list of <td> elements in row from table
                let tableDataList = ele.children;
                // grab individual <input> element value (Questions)
                let input1 = tableDataList[2].children;

                // only update if Questions input is filled with a value
                if(input1[0].value !== "") {
                    let calculatedPoints = parseInt(value) * parseInt(input1[0].value);
                    // update numPoints
                    numPoints += calculatedPoints;
                    if(calculatedPoints === 1){
                        document.getElementById("table_points").innerHTML = numPoints + " Point Total";
                    } else {
                        document.getElementById("table_points").innerHTML = numPoints + " Points Total";
                    }
                }
            }
            let subNumPoints = (value) =>{
                // can't go negative, so don't allow subtraction when numPoints is 0
                if(numPoints !== 0){
                    // update numPoints
                    numPoints -= parseInt(value);
                    // update display
                    if(numPoints === 1){
                        document.getElementById("table_points").innerHTML = numPoints + " Point Total";
                    } else {
                        document.getElementById("table_points").innerHTML = numPoints + " Points Total";
                    }
                }
            }


            // function to insert a row of data into the table
            let insertSelection = () =>{
                // start the table row
                let str = '<tr>';

                // insert the ch selected
                str += '<td class="td1">' + getFullChapter();

                // insert the sec selected
                str += '<br>' + getFullSection();

                // insert the lo selected
                str += '<br>' + getFullLearningOutcome() + '</td>';
    
                // insert the numerical lo
                str += '<td class="td2">';
                str += '<input type="text" id="lonum_' + row_num + '" name="lonum_' + row_num + '" class="lonum_inputs" value="' + getCompleteTag() + '" readonly required>';
                str += '</td>';

                // insert input element for number of questions
                str += '<td class="td3">';
                str += '<input type="number" id="questions_' + row_num + '" name="questions_' + row_num + '" class="questions_inputs" onchange="addNumQuestions(value, this.parentElement.parentElement);" max="' + dynamic_json[getCompleteTag()]  + '" placeholder="0 - ' + dynamic_json[getCompleteTag()] + '" required>';
                str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of assigned learning outcome questions for this assessment.">';
                // max="' + lo_count + '"
                str += '</td>';

                // insert input element for number of points
                str += '<td class="td4">';
                str += '<input type="number" id="points_' + row_num + '" name="points_' + row_num + '" class="points_inputs" onchange="addNumPoints(value, this.parentElement.parentElement);" placeholder="1" required>';
                str += '<img src="../assets/img/help_icon.png" alt="help icon" width="20" height="20" style="vertical-align:middle" title="Input a number, representing the number of points assigned per question for this selected learning outcome.">';
                str += '</td>';

                // insert delete button
                str += '<td class="td5">';
                str += '<button class="delete_button" onclick="deleteValues(this.parentElement.parentElement);deleteRow(this.parentElement.parentElement);" type="button">x</button>';
                str += '</td>';

                // complete the table row
                str += "</tr>";

                // append table row to the table body
                document.getElementById("sel_table_res_body").insertAdjacentHTML("beforeend", str);

                // unhiding the table once first row of data gets inserted (code is only run once)
                if(table_hidden){
                    document.getElementById("group3").setAttribute('style', 'display:""');
                    table_hidden = false;
                    document.getElementById("table_lo").innerHTML = "1 Learning Outcome Selected";
                    document.getElementById("table_questions").innerHTML = "0 Questions Total";
                    document.getElementById("table_points").innerHTML = "0 Points Total";
                }

                // send updated row_num value to hidden input in form
                document.getElementById("num_of_selected_los").value = row_num;

                // update row num
                row_num++;

                // update numLos
                addNumLos();
            }


            // function to delete input values from the user in the selected delete row
            let deleteValues = (ele) =>{
                // subtract numLos and display result
                subNumLos();

                // grab list of <td> elements in row that will be deleted from the table
                let tableDataList = ele.children;

                // grab individual <input> elements value
                let input1 = tableDataList[2].children;
                let input2 = tableDataList[3].children;

                // subtract selected value from numQuestions and display result
                if(input1[0].value !== ""){      
                    subNumQuestions(parseInt(input1[0].value));
                }
                // subtract selected value from numPoints and display result
                if(input2[0].value !== ""){     
                    if(input1[0].value !== ""){
                        subNumPoints(parseInt(input2[0].value) * parseInt(input1[0].value));
                    } else {
                        subNumPoints(parseInt(input2[0].value));
                    }    
                }
            }
            // function to delete actual row from the table
            let deleteRow = (ele) =>{
                // get the parent before deleting
                let parent = ele.parentElement;
                // delete the table row
                ele.remove();
                // update the total number of rows
                row_num--;
                // send row num to hidden input
                document.getElementById("num_of_selected_los").value = row_num - 1;
                // now update the table's ids
                renameInputIds(parent);
            }
            // function to loop through remaining table rows in table (if possible) and rename 
            // attributes of input 'lonum_', 'questions_', and 'points_'
            let renameInputIds = (ele) =>{
                // counter for modifying id value
                let count = 1;

                // grab every child element of the <tbody> element (list of <tr> elements)
                let tableRowList = ele.children;
                //console.log(tableRowList);

                // loop through each <tr> element
                for(let i = 0; i < tableRowList.length; i++){

                    // grab every child element of the <tr> element (list of <td> elements)
                    let tableDataList = tableRowList[i].children;
                    //console.log(tableDataList);

                    // loop through select <td> elements
                    for(let j = 1; j < 4; j++){

                        // grab every child element of the <td> element (list of <input> elements)
                        let inputList = tableDataList[j].children;
                        //console.log(inputList);

                        // loop through each <input> element (should just be 1)
                        for(let k = 0; k < inputList.length; k++){
                            // grab input instance
                            let input = inputList[k];
                            // grab input id value
                            let id = input.id;
                            //console.log(id);
                            // modifying id value
                            let idx1 = id.indexOf("_");
                            id = id.slice(0, idx1 + 1);
                            id = id + count;
                            //console.log(id);
                            // setting input element attributes
                            input.setAttribute("id", id);
                            input.setAttribute("name", id);
                        }
                    }
                    // update count
                    count++;
                }
            }


            // function to get just the numerical value of the chapter (ex: 1)
            let getChapterNumber = () =>{
                let select = document.getElementById("ch_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get just the numerical value of the section (ex: 2)
            let getSectionNumber = () =>{
                let select = document.getElementById("sec_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get just the numerical value of the learning outcome (ex: 3)
            let getLearningOutcomeNumber = () =>{
                let select = document.getElementById("lo_select");
                return select.options[select.selectedIndex].value;
            }
            // function to get a complete learning outcome (ex: 1.2.3)
            let getCompleteTag = () =>{
                return getChapterNumber() + "." + getSectionNumber() + "." + getLearningOutcomeNumber();
            }


            // obtaining the complete list of chapters (on page load)
            // then adding the data to the ch_select dropdown element
            var ch_options_req;              
            var ch_options_obj;            
            let getChapterOptions = () =>{
                ch_options_req = new XMLHttpRequest();
                ch_options_req.open('POST', 'get/ch_names_1.php', true);
                ch_options_req.onreadystatechange = getChapterOptionsResponse;
                ch_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                ch_options_req.send();
            }
            let getChapterOptionsResponse = () =>{
                if (ch_options_req.readyState == 4 && ch_options_req.status == 200) {
                    //console.log("PHP sent back: " + ch_options_req.responseText);
                    ch_options_obj = JSON.parse(ch_options_req.responseText);
                    // now display the chapter options
                    let str = '<option value="">Select a Chapter</option>';
                    let i = 2;
                    for(const [key, value] of Object.entries(ch_options_obj)){
                        str += '<option value="' + key + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("ch_select").innerHTML = str;
                }
            }   

            // obtaining the complete list of sections (given a selected chapter)
            // then adding the data to the sec_select dropdown element
            var sec_options_req;
            var sec_options_obj;
            let getSectionOptions = () =>{
                // we need to grab the options from the PGSQL DB
                sec_options_req = new XMLHttpRequest();
                sec_options_req.open('POST', 'get/sec_names_1.php', true);
                sec_options_req.onreadystatechange = getSectionOptionsResponse;
                sec_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                sec_options_req.send("chapter=" + getChapterNumber());
            }
            let getSectionOptionsResponse = () =>{
                if (sec_options_req.readyState == 4 && sec_options_req.status == 200) {
                    //console.log("PHP sent back: " + sec_options_req.responseText);
                    sec_options_obj = JSON.parse(sec_options_req.responseText);
                    // now display the section options
                    let str = '<option value="">Select a Section</option>';
                    let i = 2;
                    for(const [key, value] of Object.entries(sec_options_obj)){
                        let index = key.indexOf('.');
                        let sec_num = key.slice(index + 1, key.length);
                        str += '<option value="' + sec_num + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("sec_select").innerHTML = str;
                }
            }   

            // obtaining the complete list of learning outcomes (given a selected chapter and selected section)
            // then adding the data to the lo_select dropdown element
            var lo_options_req;
            var lo_options_obj;
            let getLoOptions = () =>{
                // we need to grab the options from the PGSQL DB
                lo_options_req = new XMLHttpRequest();
                lo_options_req.open('POST', 'get/lo_names_1.php', true);
                lo_options_req.onreadystatechange = getLoOptionsResponse;
                lo_options_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                lo_options_req.send("chapter=" + getChapterNumber() + "&section=" + getSectionNumber());
            }
            let getLoOptionsResponse = () =>{
                if (lo_options_req.readyState == 4 && lo_options_req.status == 200) {
                    //console.log("PHP sent back: " + lo_options_req.responseText);
                    lo_options_obj = JSON.parse(lo_options_req.responseText);
                    // now display the lo options
                    let str = '<option value="">Select a Learning Outcome</option>';
                    i = 2;
                    for(const [key, value] of Object.entries(lo_options_obj)){
                        let pos1 = key.indexOf('.');
                        let pos2 = key.indexOf('.', pos1 + 1);
                        let lo_num = key.slice(pos2 + 1, key.length);
                        str += '<option value="' + lo_num + '">' + key + ". " + value + '</option>';
                        i++;
                    }
                    document.getElementById("lo_select").innerHTML = str;
                }
            }   


            // loading dynamic info to variable on page load
            var load_json_req;
            let loadJSON = () => {
                load_json_req = new XMLHttpRequest();
                load_json_req.onreadystatechange = function() {
                    if(load_json_req.readyState == 4 && load_json_req.status == 200){
                        dynamic_json = JSON.parse(load_json_req.responseText);
                        //console.log(dynamic_json);
                        //console.log(dynamic_json["1.2.3"]);
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
                if(!event.target.matches('.dropbtn')) {
                    let dropdowns = document.getElementsByClassName("dropdown-content");
                    for (let i = 0; i < dropdowns.length; i++) {
                        let openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        </script>
    </body>
</html>