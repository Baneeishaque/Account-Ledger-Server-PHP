<?php

include_once 'config.php';

//echo filter_input(INPUT_GET,'username').','.filter_input(INPUT_GET,'password');

echo json_encode(mysqli_fetch_assoc($con->query("SELECT COUNT(`username`) AS `user_count`, `id` FROM `users` WHERE `username`='".filter_input(INPUT_GET,'username')."' AND `password`='".filter_input(INPUT_GET,'password')."'")));