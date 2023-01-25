<?php
// start the session (loggedIn, name, email, type, pic, course_name, course_id, selected_course_name, selected_course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if(!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true){
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Instructor' or 'Mentor' then force logout
if ($_SESSION["type"] !== "Instructor" && $_SESSION["type"] !== "Mentor") {
    header("location: ../register_login/logout.php");
    exit;
}

// globals
$amt_students; // value used to accept unique student_email post input
$student_email; // holds student's email
$student_name; // holds student's full name
$student_complete; // holds number of student's complete los
$student_incomplete; // holds number of student's incomplete los

// processing client form data when it is submitted
if($_SERVER["REQUEST_METHOD"] === "POST"){

    // receive post inputs
    $amt_students = $_POST["amt_students"];
    for($i = 1; $i <= $amt_students; $i++){
        if(isset($_POST["student_email_$i"])){
            $student_email = $_POST["student_email_$i"];
            $student_name = $_POST["student_name_$i"];
            $student_complete = $_POST["student_complete_$i"];
            $student_incomplete = $_POST["student_incomplete_$i"];
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Assess Student</title>
        <link rel="stylesheet" href="../assets/css/instructor/instr_assess2.css" />
        <link rel="stylesheet" href="../assets/css/global/or2stem.css" />
        <link rel="stylesheet" href="../assets/css/global/header.css" />
        <link rel="stylesheet" href="../assets/css/global/global.css" />
        <link rel="stylesheet" href="../assets/css/global/footer.css" />
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    </head>
    <body onload='getChapterData("<?= $student_email; ?>");drawChart();'>
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
                            <a id="OR2STEM-HEADER-A" href="instr_index1.php">On-Ramp to STEM</a>
                        </h1>
                    </div>

                    <div class="inner-banner">
                        <div class="banner-img"></div>
                    </div>
                </nav>
            </header>

            <br>

            <main id="main">
                <h1>Student Evaluation</h1>
                <table id="student_table">
                    <thead>
                        <tr>
                            <th class="intro_th" scope="col">Email</th>
                            <th class="intro_th" scope="col">Name</th>
                            <th class="intro_th" scope="col">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="intro_td"><?= $student_email; ?></td>
                            <td class="intro_td"><?= $student_name; ?></td>
                            <td class="intro_td"><div id="myChart"></div></td>
                        </tr>
                    </tbody>
                </table>
                <br><br>
            </main>

            <br>

            <footer>
                <div class="container">
                    <div class="footer-top flex">
                        <div class="logo">
                            <a href="" class="router-link-active"><p>On-Ramp to STEM</p></a>
                        </div>
                        <div class="navigation">
                            <h4>Navigation</h4>
                            <ul>
                                <li><a href="instr_index1.php" class="router-link-active">Home</a></li>
                                <li><a href="" class="">About Us</a></li>
                                <li><a href="" class="">FAQ</a></li>
                                <li><a href="" class="">Contact Us</a></li>
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
                        <p>© 2021-2022 OR2STEM Team</p>
                    </div>
                </div>
            </footer>
        </div>

        <script type="text/javascript">
            /* GLOBALS */
            let ch_count = 1; // global count of chapters displayed on table on load
            let chapter_clicked = []; // boolean pointers for each chapter button on table
            let section_clicked = []; // boolean pointers for each section button on table


            // fxn to pad a number for display purposes (time spent)
            function str_pad_left(string,pad,length) {
                return (new Array(length+1).join(pad)+string).slice(-length);
            }


            /* GET AND DISPLAY CHAPTER PROGRESS DATA */
            let req1;
            let ch_obj;
            let getChapterData = (email) => {
                // start XMLHttpRequest
                req1 = new XMLHttpRequest();
                req1.onreadystatechange = function() {
                    if(req1.readyState == 4 && req1.status == 200){
                        ch_obj = JSON.parse(req1.responseText);
                        showChapterProgress();
                    }
                }
                req1.open('POST', 'get/ch_prog.php', true);
                req1.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                req1.send(`user=${email}`);
            }
            let showChapterProgress = () => {
                // create a table with all the chapter data to display on the client-side
                let str = '<table id="ch_table">';
                str += '<tr><th>Ch</th><th>Chapter Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th><th>Details</th></tr>';
                for(const key in ch_obj){
                    const value = ch_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td>' + key + '</td>';
                    str += '<td>' + value["Name"] + '</td>';
                    str += '<td>' + value["TotalQuestions"] + '</td>';
                    str += '<td title="' + value["NumberCorrect"] + ' / ' + value["NumberComplete"] + '"><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td>';
                    str += '<td title="' + value["NumberComplete"] + ' / ' + value["TotalQuestions"] + '"><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td>';
                    str += '<td><p>' + finalTime + '</p></td>';
                    str += `<td><button id="ch_btn_${ch_count}" class="open_btn_1" onclick="showSectionTable(this.parentElement.parentElement);">Open</button></td></tr>`;
                    str += `<tr id="sec_tr${ch_count}" style="display:none"><td id="sec_td${ch_count}" colspan="7"></td></tr>`;
                    ch_count++;
                }
                str += '</table>';
                document.getElementById("main").insertAdjacentHTML("beforeend", str);  
                
                // initialize chapter_clicked with false
                for(let i = 1; i < ch_count; i++){
                    chapter_clicked[i] = false;
                }

                // initialize section_clicked with empty arrays for each chapter
                for(let i = 1; i < ch_count; i++){
                    section_clicked[i] = [];
                }
            }
            


            let showSectionTable = (ele) => {
                // grab list of <td> elements in row from table
                let tdList = ele.children;
                // grab unique ch identifier value
                const idx = tdList[0].innerHTML;
                // load and display data
                getSectionData(idx);
            }
            /* GET AND DISPLAY SECTION PROGRESS DATA */
            let req2;
            let sec_obj;
            let getSectionData = (idx) =>{
                if(chapter_clicked[idx]){
                    document.getElementById(`ch_btn_${idx}`).innerHTML = "Open";
                    document.getElementById(`sec_tr${idx}`).style.display = "none";
                    chapter_clicked[idx] = false;
                } 
                else{
                    document.getElementById(`ch_btn_${idx}`).innerHTML = "Close";
                    document.getElementById(`sec_tr${idx}`).style.display = "";
                    chapter_clicked[idx] = true;
                    // start XMLHttpRequest
                    req2 = new XMLHttpRequest();
                    req2.onreadystatechange = function() {
                        if(req2.readyState == 4 && req2.status == 200){
                            //console.log("PHP sent back: " + req2.responseText);
                            sec_obj = JSON.parse(req2.responseText);
                            showSectionProgress(idx);
                        }
                    }
                    req2.open('POST', 'get/sec_prog.php', true);
                    req2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    req2.send(`user=<?= $student_email; ?>&chapter=${idx}`);
                }
            }
            let showSectionProgress = (idx) =>{
                let section_count = 1;
                // create a table with all the section data to display on the client-side
                let str = '<table id="sec_table">';
                str += '<tr><th>Sec</th><th>Section Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th><th>Details</th></tr>';
                for(const key in sec_obj){
                    const value = sec_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td>' + key + '</td>';
                    str += '<td>' + value["Name"] + '</td>';
                    str += '<td>' + value["TotalQuestions"] + '</td>';
                    str += '<td title="' + value["NumberCorrect"] + ' / ' + value["NumberComplete"] + '"><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td>';
                    str += '<td title="' + value["NumberComplete"] + ' / ' + value["TotalQuestions"] + '"><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td>';
                    str += '<td><p>' + finalTime + '</p></td>';
                    str += `<td><button id="sec_btn_${idx}_${section_count}" class="open_btn_2" onclick="showLoTable(this.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement.previousSibling, this.parentElement.parentElement)">Open</button></td></tr>`;
                    str += `<tr id="lo_tr_${idx}_${section_count}" style="display:none"><td id="lo_td_${idx}_${section_count}" colspan="7"></td></tr>`;
                    section_count++;                
                }
                str += '</table>';
                document.getElementById(`sec_td${idx}`).innerHTML = str; 
                
                // initialize section_clicked with false
                for(let i = 1; i < section_count; i++){
                    section_clicked[idx][i] = false;
                }

            }


            let showLoTable = (ele1, ele2) => {
                // grab list of <td> elements in row from table
                let tdList1 = ele1.children;
                let tdList2 = ele2.children;
                // grab unique identifier value
                const ch_idx = tdList1[0].innerHTML;
                let sec_idx1 = tdList2[0].innerHTML; // holds entire section num (1.2)
                let idx = sec_idx1.indexOf(".");
                const sec_idx2 = sec_idx1.slice(idx + 1, sec_idx1.length); // holds just the section digit (2)
                // load and display lo data
                getLoOptions(ch_idx, sec_idx1, sec_idx2);

            }
            /* GET AND DISPLAY LEARNING OUTCOME OPTIONS UNIQUE TO STUDENT SELECTED */
            let req3;           
            let lo_obj;           
            let getLoOptions = (ch_idx, sec_idx1, sec_idx2) =>{
                if(section_clicked[ch_idx][sec_idx2]){
                    document.getElementById(`sec_btn_${ch_idx}_${sec_idx2}`).innerHTML = "Open";
                    document.getElementById(`lo_tr_${ch_idx}_${sec_idx2}`).style.display = "none";
                    section_clicked[ch_idx][sec_idx2] = false;
                }
                else {
                    document.getElementById(`sec_btn_${ch_idx}_${sec_idx2}`).innerHTML = "Close";
                    document.getElementById(`lo_tr_${ch_idx}_${sec_idx2}`).style.display = "";
                    section_clicked[ch_idx][sec_idx2] = true;
                    // we need to grab the options from the PGSQL DB
                    req3 = new XMLHttpRequest();
                    req3.onreadystatechange = function() {
                        if(req3.readyState == 4 && req3.status == 200){
                            //console.log("PHP sent back: " + req2.responseText);
                            lo_obj = JSON.parse(req3.responseText);
                            showLoProgress(ch_idx, sec_idx2);
                        }
                    }
                    req3.open('POST', 'get/lo_prog.php', true);
                    req3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    req3.send(`user=<?= $student_email; ?>&chapter=${ch_idx}&section=${sec_idx1}`);
                }
            }
            let showLoProgress = (ch_idx, sec_idx2) =>{
                // create a table with all the lo data to display on the client-side
                let str = '<table id="lo_table">';
                str += '<tr><th>Lo</th><th>Learning Outcome Name</th><th>Number of Questions</th><th>Percent Correct</th><th>Percent Complete</th><th>Time Spent</th></tr>';
                for(const key in lo_obj){
                    const value = lo_obj[key];
                    // value[4] contains total seconds, convert to hours:minutes:seconds then display all data in table
                    let hours = Math.floor(value["TimeSpent"] / 3600);
                    let minutes = Math.floor(value["TimeSpent"] / 60);
                    let seconds = value["TimeSpent"] - minutes * 60;
                    let finalTime = str_pad_left(hours, '0', 2) + ':' + str_pad_left(minutes, '0' ,2) + ':' + str_pad_left(seconds, '0', 2);
                    // make percentage to display
                    let firstPercent;
                    let secondPercent;
                    if(value["NumberCorrect"] == 0){
                        firstPercent = 0;
                    } else{
                        firstPercent = Math.round((value["NumberCorrect"]/value["NumberComplete"]) * 100);
                    }
                    if(value["NumberComplete"] == 0){
                    secondPercent = 0;
                    } else{
                        secondPercent = Math.round((value["NumberComplete"]/value["TotalQuestions"]) * 100);
                    }
                    str += '<tr><td>' + key + '</td>';
                    str += '<td>' + value["Name"] + '</td>';
                    str += '<td>' + value["TotalQuestions"] + '</td>';
                    str += '<td title="' + value["NumberCorrect"] + ' / ' + value["NumberComplete"] + '"><progress value="' + value["NumberCorrect"] + '" max="' + value["NumberComplete"] + '"></progress><div>' + firstPercent + '%</div></td>';
                    str += '<td title="' + value["NumberComplete"] + ' / ' + value["TotalQuestions"] + '"><progress value="' + value["NumberComplete"] + '" max="' + value["TotalQuestions"] + '"></progress><div>' + secondPercent + '%</div></td>';
                    str += '<td>' + finalTime + '</td></tr>';
                }
                str += '</table>';
                document.getElementById(`lo_td_${ch_idx}_${sec_idx2}`).innerHTML = str;   
            }
            

            // used to draw single student pie chart
            let drawChart = () => {
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    var data = google.visualization.arrayToDataTable([
                        ['Status', 'Learning Outcomes'],
                        ['Complete', <?= $student_complete; ?>],
                        ['Incomplete', <?= $student_incomplete; ?>],
                    ]);

                    var options = {
                        colors: ['green', 'red'],
                        legend: 'none'
                    };

                    var chart = new google.visualization.PieChart(document.getElementById("myChart"));

                    chart.draw(data, options);
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