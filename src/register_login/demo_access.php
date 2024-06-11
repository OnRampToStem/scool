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

require_once "../bootstrap.php";

$log = getLogger();

$subject = uniqid("guest-", true);
$email = "{$subject}@scool.fresnostate.edu";

$log->info("building demo token for {$email}");

$payload = [
    "sub" => $subject,
    "email" => $email,
    "name" => "Guest User",
    "roles" => ["Learner"],
    "context" => [
        "id" => DEMO_COURSE_ID,
        "title" => DEMO_COURSE_TITLE,
    ],
    "unique_name" => $email,
    "picture" => "https://canvas.instructure.com/images/messages/avatar-50.png",
    "iss" => "https://scool.fresnostate.edu",
    "aud" => "https://scool.fresnostate.edu",
    "iat" => time(),
    "nbf" => time() + 8,
    "exp" => time() + 30,
];

$token = Firebase\JWT\JWT::encode($payload, SECRET_KEY, "HS256");
?>

<html lang="en">
<head>
    <title>Demo Access</title>
    <style>
        .container {
            margin: 0 auto;
            text-align: center;
            border: solid 6px navy;
            width: 35%;
            height: 350px;
            padding-top: 20px;
            margin-top: 50px;
        }
        #image_header {
            width: 237.6px;
            height: 120px;
            padding: 15px;
        }
        .footer {
            text-align: center;
        }
        h1 {
            color: navy;
        }
        .loading {
            font-size: large;
        }
        #counter {
            font-weight: bolder;
            font-size: xx-large;
        }
    </style>
    <script>
        const intervalHandle = setInterval(function() {
            let v = document.getElementById("counter").innerText;
            let count = v - 1;
            document.getElementById("counter").innerText = String(count);
            if (count === 0) {
                clearInterval(intervalHandle);
                document.getElementById("main-form").submit();
            }
        }
        , 1000);
    </script>
</head>
<body>
<div class="container">
    <img id="image_header" src="/assets/img/or2stem.jpg" alt="Fresno State OR2STEM Logo" />
    <h1>Demo Account Access</h1>
    <p class="loading">
        Please wait while we prepare the demo account...
    </p>
    <p>
        <span id="counter">9</span>
    </p>
    <form id="main-form" method="post" action="/misc/payload.php">
        <input type="hidden" name="token" value="<?=$token?>"/>
    </form>
</div>
<div class="footer">
    <?php include "../snippets/footer.html" ?>
</div>
</body>
</html>
