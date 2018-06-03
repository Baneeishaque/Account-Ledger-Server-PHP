<?php

include_once 'config.php';

$user_id = filter_input(INPUT_GET, 'user_id');
$parent_account_id = filter_input(INPUT_GET, 'parent_account_id');

$get_accounts_sql = "SELECT `account_id`, `name`, `parent_account_id`, `account_type`, `notes`, `commodity_type`, `commodity_value`, `owner_id` FROM `accounts` WHERE `owner_id`='$user_id' AND `parent_account_id`='$parent_account_id' ORDER BY `account_id`";

$status_result = $con->query($get_accounts_sql);

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
