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

// Fresno State PostgreSQL Database credentials
define('HOST', 'stem-scale-db.priv.fresnostate.edu');
define('PORT', '5432');
define('DB', 'swa');
define('USER', 'scale_dyna');
define('PASS', 'ZAKh55Mxxafz7jBqwhy_SG23C8_WkXm8_6');

// Attempt to connect to the PostgreSQL database 
$con = pg_connect("host=" . HOST . " port=" . PORT . " dbname=" . DB . " user=" . USER . " password=" . PASS)
       or die ("Could not connect to the database.\n");
?>
    