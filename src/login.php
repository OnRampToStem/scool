<?php

/*
 * Student Centered Open Online Learning (SCOOL) LTI Integration
 * Copyright (c) 2021-2024  Fresno State University, SCOOL Project Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

require_once "bootstrap.php";

header("cache-control: no-store");

$log = getLogger(__FILE__);

// global variables and init with empty values
$email = $password = "";
$login_err = false;

// processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $login_err = true;
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["pwd"]))) {
        $login_err = true;
    } else {
        $password = trim($_POST["pwd"]);
    }

    /* MAKE SURE ONLY ALLOWED USERS CAN ACCESS */
    if (
        $_POST["email"] !== "student-marisa.cheung@calearninglab.org" && $_POST["email"] !== "observer-marisa.cheung@calearninglab.org" &&
        $_POST["email"] !== "student-nfo@calearninglab.org" && $_POST["email"] !== "observer-nfo@calearninglab.org" &&
        $_POST["email"] !== "temp-instructor@gmail.com" && $_POST["email"] !== "temp-student@gmail.com" &&
        $_POST["email"] !== DEMO_INSTRUCTOR_EMAIL
    ) {
        $log->warning("invalid email address", ["email" => $_POST["email"]]);
        $login_err = true;
    }

    /* VALIDATE CREDENTIALS */
    // if no input errors then continue
    if (!$login_err) {
        $db_con = getDBConnection();
        // retrieve email and hashed password from the database, where email exists
        $query = "SELECT name, email, type, pic, course_name, course_id FROM users WHERE email = '" . pg_escape_string($db_con, $email) . "'";
        $result = pg_query($db_con, $query) or die("Cannot execute query: $query \n");

        // email exists in the database, continue to password verification
        if (pg_num_rows($result) >= 1) {

            // using pg_fetch_row, we now have access to database table data retrieved
            $row = pg_fetch_row($result);

            if ($password === DEMO_PASSWORD) {

                // create timestamp to be updated for last_signed_in attribute
                $date = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
                $timestamp = $date->format('Y-m-d H:i:s');

                // query
                $query = "UPDATE users SET last_signed_in = '" . $timestamp . "' WHERE email = '" . $row[1] . "'";
                pg_query($db_con, $query) or die("Cannot execute query: $query \n");

                session_start();

                $_SESSION["loggedIn"] = true;
                $_SESSION["name"] = $row[0];
                $_SESSION["email"] = $row[1];
                $_SESSION["type"] = $row[2];
                $_SESSION["pic"] = $row[3];
                $_SESSION["course_name"] = $row[4];
                $_SESSION["course_id"] = $row[5];
                $_SESSION["logout_url"] = "/login.php";

                $log->info("successful login", ["email" => $row[1], "type" => $row[2]]);

                if ($row[2] === "Learner") {
                    header("location: student/student_index.php");
                } else if ($row[2] === "Mentor") {
                    header("location: instructor/instr_index1.php");
                } else if ($row[2] === "Instructor") {
                    header("location: instructor/instr_index1.php");
                } else {
                    $log->error("unknown user type", ["type" => $row[2]]);
                }
            } else {
                $log->warning("invalid password", ["email" => $_POST["email"]]);
                $login_err = true;
            }
        } else {
            $log->warning("user with email address not found", ["email" => $_POST["email"]]);
            $login_err = true;
        }
        pg_close($db_con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>OR2STEM External Login Page</title>
    <meta charset="UTF-8">
    <style>
        .container {
            margin: 0 auto;
            text-align: center;
            border: solid 6px navy;
            width: 35%;
            height: 450px;
            padding-top: 20px;
            margin-top: 50px;
        }

        #image_header {
            width: 237.6px;
            height: 120px;
            padding: 15px;
        }

        .form-group {
            margin: 25px;
        }

        .input-error {
            color: red;
            font-size: 16px;
            font-weight: 600;
        }

        #email_label,
        #pwd_label {
            color: navy;
            font-size: 16px;
            font-weight: 600;
            text-align: left;
        }

        #email,
        #pwd {
            background-color: #F3FFFF;
            border: 1px solid navy;
            width: 250px;
            height: 30px;
            font-size: 14px;
        }

        input[type="submit"] {
            transition-duration: 0.5s;
            width: 200px;
            height: 30px;
            color: white;
            background-color: navy;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: red;
        }

        .footer {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <img id="image_header" src="assets/img/or2stem.jpg" alt="Fresno State OR2STEM Logo" />

        <?php
        if ($login_err) {
            echo '<div class="input-error">Invalid username or password</div>';
        }
        ?>

        <form action="" method="post">

            <div class="form-group">
                <label id="email_label" for="email">Email</label>
                <br>
                <input type="email" id="email" name="email" value="<?= $email; ?>" autofocus required>
            </div>

            <div class="form-group">
                <label id="pwd_label" for="pwd">Password</label>
                <br>
                <input type="password" id="pwd" name="pwd" required>
            </div>

            <input type="submit" name="submit" value="Login">

        </form>
        <p>
            <a href="/register_login/demo_access.php">Try it!</a>
        </p>
    </div>
    <div class="footer">
        <?php include "./snippets/footer.html" ?>
    </div>
</body>

</html>
