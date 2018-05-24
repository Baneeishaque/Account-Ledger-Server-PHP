<?php

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$status_sql = "SELECT `account_id`, `full_name`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id` FROM `accounts` WHERE `owner_id`='$user_id' ORDER BY  `accounts`.`parent_account_id` ASC";
$status_result = $con->query($status_sql);

$emptyarray = array();

if (mysqli_num_rows($status_result) != 0) {

    array_push($emptyarray, array("status" => "0"));

    while ($status_row = mysqli_fetch_assoc($status_result)) {
        $emptyarray[] = $status_row;
    }
} else {
    array_push($emptyarray, array("status" => "1"));
}

echo json_encode($emptyarray);
