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

// start the session (loggedIn, name, email, type, pic, course_name, course_id)
session_start();

// if user is not logged in then redirect them back to Fresno State Canvas
if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
    header("location: https://fresnostate.instructure.com");
    exit;
}

// if user account type is not 'Learner' then force logout
if ($_SESSION["type"] !== "Learner") {
    header("location: /register_login/logout.php");
    exit;
}

// function to sanitize input data
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to send HTML email
function sendHtmlEmail($to, $subject, $message, $headers)
{
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    return mail($to, $subject, $message, $headers);
}

// globals
$errors = [];

// processing client form data when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // receive, sanitize, and validate data
    $question_url = sanitizeInput($_POST["question_url"]);
    $student_name = sanitizeInput($_POST["student_name"]);
    $student_email = sanitizeInput($_POST["student_email"]);
    $instructor_email = sanitizeInput($_POST["instructor_email"]);
    $message = sanitizeInput($_POST["message"]);

    // validate email addresses
    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Invalid student email address.");
    }

    if (!filter_var($instructor_email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Invalid instructor email address.");
    }

    if (empty($errors)) {
        // manipulate data
        $to = $instructor_email;
        $subject = "$student_name - Issue with Assessment Question";
        $final_message = "$question_url \n $message";
        $headers = "From: $student_email";

        // send the email
        $mailSent = mail($to, $subject, $final_message, $headers);

        // Check if the email was sent successfully
        if ($mailSent) {
            echo "Email sent successfully.";
        } else {
            echo "Failed to send email.";
        }
    }
}

?>
